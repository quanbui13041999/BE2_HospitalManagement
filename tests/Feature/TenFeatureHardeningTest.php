<?php

namespace Tests\Feature;

use App\Models\ChatRoom;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Appointment;
use App\Models\QueueTicket;
use App\Models\User;
use App\Services\QueueService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenFeatureHardeningTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_common_invalid_inputs_are_rejected(): void
    {
        $admin = User::create([
            'full_name' => 'Hardening Admin',
            'email' => 'hardening_admin_' . uniqid() . '@example.test',
            'password' => Hash::make('secret123'),
            'role_id' => 1,
            'status' => 1,
        ]);

        try {
            $this->actingAs($admin)
                ->post(route('admin.news.store'), [
                    'title' => '　',
                    'content' => '<p>&nbsp;</p>',
                    'category' => '999999999999',
                ])
                ->assertSessionHasErrors(['title', 'content', 'category']); // fixed: full-width space/select gia tu DevTools bi chan

            $this->actingAs($admin)
                ->get(route('admin.news.index', ['page' => 99999]))
                ->assertSessionHasErrors(['page']); // fixed: URL page qua lon phai bi validate
        } finally {
            $admin->delete();
        }
    }

    public function test_chat_second_delete_or_close_reports_json_error(): void
    {
        $admin = User::where('role_id', 1)->firstOrFail();
        $patient = User::create([
            'full_name' => 'Hardening Patient',
            'email' => 'hardening_patient_' . uniqid() . '@example.test',
            'password' => Hash::make('secret123'),
            'role_id' => 3,
            'status' => 1,
        ]);

        $room = ChatRoom::create([
            'user_id' => $patient->user_id,
            'status' => 'Mở',
            'created_at' => now(),
        ]);

        try {
            $this->actingAs($admin)
                ->postJson(route('admin.chatroom.close', $room->room_id))
                ->assertOk()
                ->assertJson(['success' => true]);

            $this->actingAs($admin)
                ->postJson(route('admin.chatroom.close', $room->room_id))
                ->assertStatus(409)
                ->assertJson(['success' => false]); // fixed: tab thu hai dong phong phai nhan loi ro

            $this->actingAs($patient)
                ->postJson(route('chat.send'), [
                    'room_id' => $room->room_id,
                    'message_text' => '　',
                ])
                ->assertUnprocessable(); // fixed: tin nhan chi co full-width space bi chan
        } finally {
            ChatRoom::where('room_id', $room->room_id)->delete();
            $patient->delete();
        }
    }

    public function test_queue_checkin_rejects_full_width_blank_name(): void
    {
        $admin = User::where('role_id', 1)->firstOrFail();
        $schedule = DoctorSchedule::whereDate('work_date', today())
            ->where('status', 'Hoạt động')
            ->first();

        if (!$schedule) {
            $doctor = Doctor::firstOrFail();
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
        }

        $this->actingAs($admin)
            ->post(route('queue.manage.checkin.store'), [
                'schedule_id' => $schedule->schedule_id,
                'patient_name' => '　',
                'priority' => 'normal',
                'patient_phone' => '０１２３４５６７８９',
            ])
            ->assertSessionHasErrors(['patient_name', 'patient_phone']); // fixed: khoang trang/full-width number bi validate
    }

    public function test_patient_cannot_cancel_appointment_after_exam_has_started(): void
    {
        $patient = User::create([
            'full_name' => 'Patient In Exam',
            'email' => 'patient_in_exam_' . uniqid() . '@example.test',
            'password' => Hash::make('secret123'),
            'role_id' => 3,
            'status' => 1,
        ]);

        $schedule = DoctorSchedule::whereDate('work_date', today())
            ->where('status', 'Hoạt động')
            ->first();
        $createdSchedule = false;

        if (!$schedule) {
            $doctor = Doctor::firstOrFail();
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
            $createdSchedule = true;
        }

        $appointment = Appointment::create([
            'user_id' => $patient->user_id,
            'schedule_id' => $schedule->schedule_id,
            'appointment_time' => today()->setTime(10, 0),
            'queue_number' => 1,
            'status' => 'Đã xác nhận',
            'created_at' => now(),
        ]);

        $ticket = QueueTicket::create([
            'appointment_id' => $appointment->appointment_id,
            'schedule_id' => $schedule->schedule_id,
            'user_id' => $patient->user_id,
            'patient_name' => $patient->full_name,
            'queue_date' => today(),
            'queue_number' => 1,
            'priority' => 'emergency',
            'priority_sort' => 1,
            'status' => 'calling',
            'checkin_time' => now(),
            'est_wait_minutes' => 0,
        ]);

        try {
            app(QueueService::class)->startExam($ticket->ticket_id);

            $appointment->refresh();
            $this->assertSame('Đang khám', $appointment->status); // fixed: bat dau kham phai khoa huy lich

            $this->actingAs($patient)
                ->post(route('appointments.cancel', $appointment->appointment_id), [
                    'version' => now()->format('Y-m-d H:i:s'),
                    'cancel_reason' => 'Muốn hủy khi đang khám',
                ])
                ->assertSessionHasErrors(['msg']);
        } finally {
            QueueTicket::where('ticket_id', $ticket->ticket_id)->delete();
            Appointment::where('appointment_id', $appointment->appointment_id)->delete();
            $patient->delete();

            if ($createdSchedule) {
                DoctorSchedule::where('schedule_id', $schedule->schedule_id)->delete();
            }
        }
    }
}
