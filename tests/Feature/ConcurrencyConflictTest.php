<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Review;
use App\Models\DoctorSchedule;
use App\Models\Doctor;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class ConcurrencyConflictTest extends TestCase
{
    private $doctor;
    private $appointment;
    private $review;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user and doctor
        $user = User::factory()->create();
        $this->doctor = Doctor::factory()->create(['user_id' => $user->user_id]);

        // Create test appointment
        $schedule = DoctorSchedule::factory()->create(['doctor_id' => $this->doctor->doctor_id]);
        $patient = User::factory()->create();
        $this->appointment = Appointment::factory()->create([
            'schedule_id' => $schedule->schedule_id,
            'user_id' => $patient->user_id,
            'version' => 1
        ]);

        // Create test review
        $this->review = Review::factory()->create([
            'doctor_id' => $this->doctor->doctor_id,
            'appointment_id' => $this->appointment->appointment_id,
            'version' => 1
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
