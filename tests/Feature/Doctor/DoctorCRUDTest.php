<?php

namespace Tests\Feature\Doctor;

use App\Models\Doctor;
use App\Models\User;
use App\Models\Department;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DoctorCRUDTest extends TestCase
{
    private $adminUser;
    private $department;

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

        // Create admin user without factories. /* fixed: model factory setup khong kha dung trong project nay */
        $this->adminUser = $this->createUser(1, 'doctor_crud_admin');

        // Create department
        $this->department = Department::firstOrCreate(
            ['department_name' => 'Khoa Doctor CRUD Test'],
            ['description' => 'Du lieu test', 'status' => 1]
        );
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
                'email'         => 'doctor_store_' . uniqid() . '@example.test',
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
        $regularUser = $this->createUser(3, 'doctor_crud_regular');

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
                'email' => 'doctor_validation_' . uniqid() . '@example.test',
                'status'    => 1,
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonStructure([
            'data' => [
                'errors' => ['full_name', 'department_id'],
            ],
        ]);
    }

    /**
     * Test: Get Doctor - Returns Version
     */
    public function test_get_doctor_includes_version()
    {
        $doctorUser = $this->createUser(2, 'doctor_crud_get');
        $doctor = Doctor::create([
            'user_id' => $doctorUser->user_id,
            'full_name' => 'Dr. Version Test',
            'department_id' => $this->department->department_id,
            'experience' => 3,
            'price' => 200000,
            'status' => 1,
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
        $doctorUser = $this->createUser(2, 'doctor_crud_update');
        $doctor = Doctor::create([
            'user_id' => $doctorUser->user_id,
            'department_id' => $this->department->department_id,
            'full_name'     => 'Dr. Old Name',
            'experience' => 3,
            'price' => 200000,
            'status' => 1,
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
        $doctorUser = $this->createUser(2, 'doctor_crud_conflict');
        $doctor = Doctor::create([
            'user_id' => $doctorUser->user_id,
            'full_name' => 'Dr. Conflict',
            'department_id' => $this->department->department_id,
            'experience' => 3,
            'price' => 200000,
            'status' => 1,
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
            'message' => 'Dữ liệu đã được thay đổi trước đó. Vui lòng thử lại.'
        ]);
    }

    /**
     * Test: Delete Doctor - With Version Check
     */
    public function test_delete_doctor_with_version_check()
    {
        $doctorUser = $this->createUser(2, 'doctor_crud_delete');
        $doctor = Doctor::create([
            'user_id' => $doctorUser->user_id,
            'full_name' => 'Dr. Delete',
            'department_id' => $this->department->department_id,
            'experience' => 3,
            'price' => 200000,
            'version'       => 1,
            'status'        => 1
        ]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/doctor/dashboard/doctors/{$doctor->doctor_id}", [
                'version' => 1  // Current version
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('doctors', [
            'doctor_id' => $doctor->doctor_id,
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
}
