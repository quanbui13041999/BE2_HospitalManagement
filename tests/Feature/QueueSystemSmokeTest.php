<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\QueueTicket;
use App\Models\QueueCounter;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class QueueSystemSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Switch to mysql for these tests to leverage realistic DB schema and seed data
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => env('DB_HOST', '127.0.0.1'),
            'database.connections.mysql.port' => env('DB_PORT', '3306'),
            'database.connections.mysql.database' => 'hospitalbookingdb',
            'database.connections.mysql.username' => 'root',
            'database.connections.mysql.password' => '',
        ]);
        DB::purge('mysql');
        DB::reconnect('mysql');
    }

    public function test_full_queue_flow_as_receptionist_and_doctor(): void
    {
        // 1. Find or create an admin (role_id = 1) to act as receptionist/admin
        $admin = User::where('role_id', 1)->first();
        if (!$admin) {
            $admin = User::create([
                'full_name' => 'Test Admin',
                'email' => 'test_admin_queue@example.test',
                'password' => Hash::make('secret123'),
                'role_id' => 1,
                'status' => 1,
            ]);
        }

        // 2. Find a doctor who has a schedule today, or create one
        $schedule = DoctorSchedule::whereDate('work_date', today())
            ->where('status', 'Hoạt động')
            ->first();

        if (!$schedule) {
            // Let's find any doctor and create a schedule for today
            $doctor = Doctor::first();
            $schedule = DoctorSchedule::create([
                'doctor_id' => $doctor->doctor_id,
                'room_id' => 1,
                'work_date' => today(),
                'start_time' => '08:00:00',
                'end_time' => '12:00:00',
                'slot_duration' => 15,
                'max_slot' => 20,
                'status' => 'Hoạt động',
            ]);
        } else {
            $doctor = Doctor::find($schedule->doctor_id);
        }

        // Find the user model associated with this doctor
        $doctorUser = User::find($doctor->user_id);

        // Ensure we clean up any pre-existing tickets for today's schedule to avoid conflicts
        QueueTicket::where('schedule_id', $schedule->schedule_id)->delete();
        QueueCounter::where('schedule_id', $schedule->schedule_id)->delete();

        // 3. Test Receptionist Dashboard - Index
        $response = $this->actingAs($admin)
            ->get(route('queue.manage.index'));
        $response->assertStatus(200);
        $response->assertSee($doctor->full_name);

        // 4. Test Receptionist Schedule Detail View
        $response = $this->actingAs($admin)
            ->get(route('queue.manage.show', $schedule->schedule_id));
        $response->assertStatus(200);

        // 5. Test Search/Checkin Page
        $response = $this->actingAs($admin)
            ->get(route('queue.manage.checkin'));
        $response->assertStatus(200);

        // 6. Test Patient Check-in Store (Normal priority walk-in)
        $this->actingAs($admin)
            ->post(route('queue.manage.checkin.store'), [
                'schedule_id' => $schedule->schedule_id,
                'patient_name' => '',
                'priority' => 'invalid',
                'patient_email' => 'not-an-email',
            ])
            ->assertSessionHasErrors(['patient_name', 'priority', 'patient_email']); // fixed: input check-in sai phai bi validate

        $response = $this->actingAs($admin)
            ->post(route('queue.manage.checkin.store'), [
                'schedule_id' => $schedule->schedule_id,
                'patient_name' => 'Bệnh nhân Test 1',
                'priority' => 'normal',
                'patient_phone' => '0912345678',
                'patient_email' => 'test1@example.test',
                'notes' => 'Kiểm tra sức khoẻ định kỳ'
            ]);
        // Should redirect back to the schedule show page
        $response->assertStatus(302);
        $response->assertRedirect(route('queue.manage.show', $schedule->schedule_id));

        // 7. Check if Ticket was created
        $ticket1 = QueueTicket::where('schedule_id', $schedule->schedule_id)
            ->where('patient_name', 'Bệnh nhân Test 1')
            ->first();
        $this->assertNotNull($ticket1);
        $this->assertEquals(1, $ticket1->queue_number);
        $this->assertEquals('normal', $ticket1->priority);
        $this->assertEquals('waiting', $ticket1->status);

        // 8. Create an emergency ticket directly to test priority queueing
        $ticketEmergency = QueueTicket::create([
            'schedule_id' => $schedule->schedule_id,
            'patient_name' => 'Bệnh nhân Cấp Cứu',
            'priority' => 'emergency',
            'priority_sort' => 1,
            'queue_date' => today(),
            'queue_number' => 2,
            'status' => 'waiting',
            'checkin_time' => now(),
            'est_wait_minutes' => 0,
        ]);

        // 9. Fetch Receptionist API Snapshot
        $response = $this->actingAs($admin)
            ->get(route('queue.manage.api.snapshot', $schedule->schedule_id));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current',
            'waiting',
            'stats' => ['total_waiting', 'total_completed', 'total_today']
        ]);
        // The first waiting ticket should be the Emergency one due to priority sort
        $this->assertEquals('Bệnh nhân Cấp Cứu', $response->json('waiting.0.patient_name'));

        // 10. Doctor Dashboard Index
        $response = $this->actingAs($doctorUser)
            ->get(route('queue.doctor.index'));
        $response->assertStatus(200);

        // 11. Doctor call next patient (should call the Emergency ticket #2)
        $response = $this->actingAs($doctorUser)
            ->post(route('queue.doctor.call.next', $schedule->schedule_id));
        $response->assertStatus(302);

        $ticketEmergency->refresh();
        $this->assertEquals('calling', $ticketEmergency->status);

        // 12. Doctor start exam
        $response = $this->actingAs($doctorUser)
            ->post(route('queue.doctor.start', $ticketEmergency->ticket_id));
        $response->assertStatus(302);

        $ticketEmergency->refresh();
        $this->assertEquals('in_progress', $ticketEmergency->status);

        // 13. Doctor complete exam
        $response = $this->actingAs($doctorUser)
            ->post(route('queue.doctor.complete', $ticketEmergency->ticket_id));
        $response->assertStatus(302);

        $ticketEmergency->refresh();
        $this->assertEquals('completed', $ticketEmergency->status);

        // 14. Doctor call next patient again (should call Normal ticket #1)
        $response = $this->actingAs($doctorUser)
            ->post(route('queue.doctor.call.next', $schedule->schedule_id));
        $response->assertStatus(302);

        $ticket1->refresh();
        $this->assertEquals('calling', $ticket1->status);

        // 15. Receptionist/Admin skips the Normal ticket (no show)
        $response = $this->actingAs($admin)
            ->post(route('queue.manage.ticket.skip', $ticket1->ticket_id), [
                'reason' => 'Không có mặt tại phòng khám'
            ]);
        $response->assertStatus(302);

        $ticket1->refresh();
        $this->assertEquals('skipped', $ticket1->status);
        $this->assertEquals('Không có mặt tại phòng khám', $ticket1->notes);

        // 16. Admin Queue Dashboard (index)
        $response = $this->actingAs($admin)
            ->get(route('admin.queue.index'));
        $response->assertStatus(200);

        // 17. Admin Queue Detail (show)
        $response = $this->actingAs($admin)
            ->get(route('admin.queue.show', $schedule->schedule_id));
        $response->assertStatus(200);

        // 18. Admin Queue Report (report)
        $response = $this->actingAs($admin)
            ->get(route('admin.queue.report'));
        $response->assertStatus(200);

        // 19. Admin Queue API Snapshot
        $response = $this->actingAs($admin)
            ->get(route('admin.queue.api.snapshot', $schedule->schedule_id));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current',
            'waiting',
            'stats'
        ]);

        // 20. Admin Queue API All Snapshots
        $response = $this->actingAs($admin)
            ->get(route('admin.queue.api.all-snapshots'));
        $response->assertStatus(200);

        // Clean up
        QueueTicket::where('schedule_id', $schedule->schedule_id)->delete();
        QueueCounter::where('schedule_id', $schedule->schedule_id)->delete();
        if ($admin->email === 'test_admin_queue@example.test') {
            $admin->delete();
        }
    }
}
