<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Review;
use App\Models\DoctorSchedule;
use App\Models\Doctor;
use App\Models\User;
use App\Models\Department;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ConcurrencyConflictTest extends TestCase
{
    private $doctor;
    private $appointment;
    private $review;

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

        $department = Department::firstOrCreate(
            ['department_name' => 'Khoa concurrency test'],
            ['description' => 'Du lieu test', 'status' => 1]
        );

        // Create test user and doctor without factories. /* fixed: project khong co factory cho cac model nay */
        $user = $this->createUser(2, 'doctor_concurrency');
        $this->doctor = Doctor::create([
            'user_id' => $user->user_id,
            'full_name' => 'Doctor Concurrency Test',
            'department_id' => $department->department_id,
            'experience' => 5,
            'price' => 100000,
            'status' => 1,
            'version' => 1,
        ]);

        // Create test appointment
        $schedule = DoctorSchedule::create([
            'doctor_id' => $this->doctor->doctor_id,
            'room_id' => 1,
            'work_date' => today()->addDays(3),
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'slot_duration' => 15,
            'max_slot' => 20,
            'status' => 'Hoạt động',
            'version' => 1,
        ]);
        $patient = $this->createUser(3, 'patient_concurrency');
        $this->appointment = Appointment::create([
            'schedule_id' => $schedule->schedule_id,
            'user_id' => $patient->user_id,
            'appointment_time' => today()->addDays(3)->setTime(8, 0),
            'queue_number' => 1,
            'status' => 'Đã xác nhận',
            'created_at' => now(),
            'version' => 1,
        ]);

        // Create test review
        $this->review = Review::create([
            'user_id' => $patient->user_id,
            'doctor_id' => $this->doctor->doctor_id,
            'appointment_id' => $this->appointment->appointment_id,
            'rating' => 5,
            'comment' => 'Review concurrency test',
            'created_at' => now(),
            'version' => 1,
        ]);
    }

    private function createUser(int $roleId, string $prefix): User
    {
        return User::create([
            'full_name' => ucfirst(str_replace('_', ' ', $prefix)),
            'email' => $prefix . '_' . uniqid() . '@example.test',
            'password' => Hash::make('secret123'),
            'role_id' => $roleId,
            'status' => 1,
        ]);
    }

    /**
     * Test: Concurrent appointment completion should fail on second attempt
     */
    public function test_concurrent_appointment_completion_detects_conflict()
    {
        $appointmentId = $this->appointment->appointment_id;

        // Simulate first completion
        $updated1 = Appointment::where('appointment_id', $appointmentId)
            ->where('version', 1)
            ->update([
                'status' => 'Hoàn thành',
                'version' => 2
            ]);

        $this->assertEquals(1, $updated1, 'First update should succeed');

        // Simulate second concurrent completion (with stale version)
        $updated2 = Appointment::where('appointment_id', $appointmentId)
            ->where('version', 1)  // Still thinks version is 1
            ->update([
                'status' => 'Hoàn thành',
                'version' => 2
            ]);

        $this->assertEquals(0, $updated2, 'Second update should fail (version mismatch)');

        // Verify final state is from first update
        $appointment = Appointment::find($appointmentId);
        $this->assertEquals(2, $appointment->version);
        $this->assertEquals('Hoàn thành', $appointment->status);
    }

    /**
     * Test: Concurrent review reply should fail on second attempt
     */
    public function test_concurrent_review_reply_detects_conflict()
    {
        $reviewId = $this->review->review_id;

        // First reply
        $updated1 = Review::where('review_id', $reviewId)
            ->where('version', 1)
            ->update([
                'doctor_reply' => 'First reply',
                'version' => 2
            ]);

        $this->assertEquals(1, $updated1, 'First reply should succeed');

        // Second concurrent reply (stale version)
        $updated2 = Review::where('review_id', $reviewId)
            ->where('version', 1)
            ->update([
                'doctor_reply' => 'Second reply',
                'version' => 2
            ]);

        $this->assertEquals(0, $updated2, 'Second reply should fail');

        // Verify final state
        $review = Review::find($reviewId);
        $this->assertEquals('First reply', $review->doctor_reply);
        $this->assertEquals(2, $review->version);
    }

    /**
     * Test: Version increments correctly on successful updates
     */
    public function test_version_increments_on_successful_update()
    {
        $appointmentId = $this->appointment->appointment_id;
        $versions = [1];

        for ($i = 0; $i < 5; $i++) {
            $currentVersion = end($versions);
            $updated = Appointment::where('appointment_id', $appointmentId)
                ->where('version', $currentVersion)
                ->update([
                    'note' => "Update {$i}",
                    'version' => $currentVersion + 1
                ]);

            $this->assertEquals(1, $updated, "Update {$i} should succeed");
            $versions[] = $currentVersion + 1;
        }

        $appointment = Appointment::find($appointmentId);
        $this->assertEquals(6, $appointment->version, 'Version should be 6 after 5 updates');
    }

    /**
     * Test: Pessimistic locking prevents concurrent writes
     */
    public function test_pessimistic_locking_with_database_transaction()
    {
        $appointmentId = $this->appointment->appointment_id;

        DB::transaction(function () use ($appointmentId) {
            $record = Appointment::lockForUpdate()->find($appointmentId);
            $this->assertNotNull($record);

            $currentVersion = $record->version ?? 1;
            Appointment::where('appointment_id', $appointmentId)
                ->where('version', $currentVersion)
                ->update([
                    'status' => 'Đã xác nhận',
                    'version' => $currentVersion + 1
                ]);
        });

        $appointment = Appointment::find($appointmentId);
        $this->assertEquals(2, $appointment->version);
        $this->assertEquals('Đã xác nhận', $appointment->status);
    }

    /**
     * Test: Schedule blocking with version control
     */
    public function test_schedule_blocking_with_version_control()
    {
        $schedule = DoctorSchedule::first();
        $scheduleId = $schedule->schedule_id;

        // Block the schedule
        $currentVersion = $schedule->version ?? 1;
        $updated = DoctorSchedule::where('schedule_id', $scheduleId)
            ->where('version', $currentVersion)
            ->update([
                'status' => 'blocked',
                'version' => $currentVersion + 1
            ]);

        $this->assertEquals(1, $updated, 'Schedule block should succeed');

        // Try to block again with stale version
        $updated2 = DoctorSchedule::where('schedule_id', $scheduleId)
            ->where('version', $currentVersion)
            ->update([
                'status' => 'blocked',
                'version' => $currentVersion + 1
            ]);

        $this->assertEquals(0, $updated2, 'Second block should fail');
    }
}
