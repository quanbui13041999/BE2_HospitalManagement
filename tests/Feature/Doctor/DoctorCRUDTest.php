<?php

namespace Tests\Feature\Doctor;

use App\Models\Doctor;
use App\Models\User;
use App\Models\Department;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class DoctorCRUDTest extends TestCase
{
    private $adminUser;
    private $department;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->adminUser = User::factory()->create(['is_admin' => 1]);

        // Create department
        $this->department = Department::factory()->create();
    }

    /**
     * Test: Store (Create) Doctor - FIX FOR ADD BUTTON
     */
    public function test_store_doctor_success()
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson('/doctor/dashboard/doctors', [
                'full_name'     => 'Dr. Nguyễn Văn A',
                'department_id' => $this->department->department_id,
                'email'         => 'doctor@example.com',
                'password'      => 'password123',
                'experience'    => 10,
                'price'         => 500000,
                'bio'           => 'Experienced doctor',
                'status'        => 1,
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'doctor' => ['doctor_id', 'full_name', 'department_id']
        ]);

        $this->assertDatabaseHas('doctors', [
            'full_name' => 'Dr. Nguyễn Văn A',
            'version'   => 1  // Verify version is initialized
        ]);
    }

    /**
     * Test: Store Doctor - Authorization Check
     */
    public function test_store_doctor_unauthorized()
    {
        $regularUser = User::factory()->create(['is_admin' => 0]);

        $response = $this->actingAs($regularUser)
            ->postJson('/doctor/dashboard/doctors', [
                'full_name'     => 'Dr. Test',
                'department_id' => $this->department->department_id,
                'status'        => 1,
            ]);

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
    }

    /**
     * Test: Store Doctor - Validation
     */
    public function test_store_doctor_validation_fails()
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson('/doctor/dashboard/doctors', [
                'full_name' => '',  // Required field
                'status'    => 1,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['full_name', 'department_id']);
    }

    /**
     * Test: Get Doctor - Returns Version
     */
    public function test_get_doctor_includes_version()
    {
        $doctor = Doctor::factory()->create([
            'department_id' => $this->department->department_id,
            'version'       => 2
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson("/doctor/dashboard/doctors/{$doctor->doctor_id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'doctor'  => [
                'doctor_id' => $doctor->doctor_id,
                'version'   => 2
            ]
        ]);
    }

    /**
     * Test: Update Doctor - With Version Check
     */
    public function test_update_doctor_with_version_check()
    {
        $doctor = Doctor::factory()->create([
            'department_id' => $this->department->department_id,
            'full_name'     => 'Dr. Old Name',
            'version'       => 1
        ]);

        $response = $this->actingAs($this->adminUser)
            ->putJson("/doctor/dashboard/doctors/{$doctor->doctor_id}", [
                'full_name'     => 'Dr. New Name',
                'department_id' => $this->department->department_id,
                'status'        => 1,
                'version'       => 1,  // Current version
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('doctors', [
            'doctor_id' => $doctor->doctor_id,
            'full_name' => 'Dr. New Name',
            'version'   => 2  // Version incremented
        ]);
    }

    /**
     * Test: Update Doctor - Version Conflict Detection
     */
    public function test_update_doctor_version_mismatch()
    {
        $doctor = Doctor::factory()->create([
            'department_id' => $this->department->department_id,
            'version'       => 2
        ]);

        $response = $this->actingAs($this->adminUser)
            ->putJson("/doctor/dashboard/doctors/{$doctor->doctor_id}", [
                'full_name'     => 'Dr. Updated',
                'department_id' => $this->department->department_id,
                'status'        => 1,
                'version'       => 1,  // Wrong version (currently 2)
            ]);

        $response->assertStatus(409);
        $response->assertJson([
            'success' => false,
            'message' => 'Bản ghi đã bị thay đổi bởi người khác'
        ]);
    }

    /**
     * Test: Delete Doctor - With Version Check
     */
    public function test_delete_doctor_with_version_check()
    {
        $doctor = Doctor::factory()->create([
            'department_id' => $this->department->department_id,
            'version'       => 1,
            'status'        => 1
        ]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/doctor/dashboard/doctors/{$doctor->doctor_id}", [
                'version' => 1  // Current version
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('doctors', [
            'doctor_id' => $doctor->doctor_id,
            'status'    => 0  // Soft deleted via status
        ]);
    }
}
