<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Room;
use App\Models\DoctorSchedule;
use App\Models\Department;
use App\Models\Doctor;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class VerifyAllTenFeaturesTest extends TestCase
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

    public function test_all_ten_features_routes_and_views()
    {
        // Create an Admin user
        $admin = User::firstWhere('role_id', 1);
        if (!$admin) {
            $admin = User::create([
                'full_name' => 'Test Admin User',
                'email' => 'test_admin_' . uniqid() . '@example.com',
                'password' => Hash::make('secret123'),
                'role_id' => 1,
                'status' => 1,
            ]);
        }

        // Create a Patient user
        $patient = User::firstWhere('role_id', 3);
        if (!$patient) {
            $patient = User::create([
                'full_name' => 'Test Patient User',
                'email' => 'test_patient_' . uniqid() . '@example.com',
                'password' => Hash::make('secret123'),
                'role_id' => 3,
                'status' => 1,
            ]);
        }

        // 1. Book appointment page (Patient)
        $this->actingAs($patient)
            ->get(route('appointments.create'))
            ->assertOk();

        // 2. View appointments list (Patient)
        $this->actingAs($patient)
            ->get(route('appointments.index'))
            ->assertOk();

        // 3. Queue Display (Public)
        $this->get(route('queue.display.index'))
            ->assertOk();

        // 4. Queue Manage page (Admin)
        $this->actingAs($admin)
            ->get(route('admin.queue.index'))
            ->assertOk();

        // 5. Patient Search page (Admin)
        $this->actingAs($admin)
            ->get(route('admin.patients.search'))
            ->assertOk();

        // 6. CSKH Chat client (Patient)
        $roomResponse = $this->actingAs($patient)
            ->post(route('chat.room'))
            ->assertOk();
        $roomId = $roomResponse->json('room_id');

        if ($roomId) {
            $this->actingAs($patient)
                ->get(route('chat.messages', ['roomId' => $roomId]))
                ->assertOk();
        }

        // 7. CSKH Chatroom admin (Admin)
        $this->actingAs($admin)
            ->get(route('admin.chatroom.index'))
            ->assertOk();

        // 8. Activity Logs admin (Admin)
        $this->actingAs($admin)
            ->get(route('admin.activity-logs.index'))
            ->assertOk();

        // 9. News public list (Public)
        $this->get(route('news.index'))
            ->assertOk();

        // 10. News admin list (Admin)
        $this->actingAs($admin)
            ->get(route('admin.news.index'))
            ->assertOk();

        // 11. Notifications page (Patient)
        $this->actingAs($patient)
            ->get(route('notifications.index'))
            ->assertOk();

        // 12. Rooms & Schedules CRUD (Admin)
        $this->actingAs($admin)
            ->get(route('admin.rooms.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.rooms.schedule.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.rooms.weekly'))
            ->assertOk();

        // 13. Doctor Statistics (Admin)
        $this->actingAs($admin)
            ->get(route('admin.doctor-statistics.index'))
            ->assertOk();

        // 14. Revenue Report (Admin)
        $this->actingAs($admin)
            ->get(route('admin.revenue.index'))
            ->assertOk();

        // 15. User Services Catalog (Public/Patient)
        $this->get(route('user.services.index'))
            ->assertOk();

        // 16. Personal Payment History (Patient)
        $this->actingAs($patient)
            ->get(route('user.payments.history'))
            ->assertOk();

        // 17. Admin Payments Management (Admin)
        $this->actingAs($admin)
            ->get(route('admin.payments.index'))
            ->assertOk();
    }

    public function test_service_booking_payment_options()
    {
        $patient = User::firstWhere('role_id', 3);
        if (!$patient) {
            $patient = User::create([
                'full_name' => 'Test Patient User',
                'email' => 'test_patient_' . uniqid() . '@example.com',
                'password' => Hash::make('secret123'),
                'role_id' => 3,
                'status' => 1,
            ]);
        }

        // Find an active service with active prices
        $service = \App\Models\Service::has('activePrices')->first();
        if (!$service) {
            $this->markTestSkipped('No active service with prices found.');
        }
        $price = $service->activePrices->first();

        // Test 1: Booking with 'payment_option = now' -> should redirect to payments.qr
        $responseNow = $this->actingAs($patient)
            ->post(route('user.services.book', $service->service_id), [
                'price_type' => $price->price_type,
                'work_date' => date('Y-m-d'),
                'appointment_time' => '13:30',
                'note' => 'Test Pay Now Option',
                'payment_option' => 'now'
            ]);
        
        $responseNow->assertRedirect();
        $this->assertStringContainsString('/payments/', $responseNow->headers->get('Location'));
        $this->assertStringContainsString('/qr', $responseNow->headers->get('Location'));

        // Test 2: Booking with 'payment_option = later' -> should redirect to payments.history
        $responseLater = $this->actingAs($patient)
            ->post(route('user.services.book', $service->service_id), [
                'price_type' => $price->price_type,
                'work_date' => date('Y-m-d'),
                'appointment_time' => '14:30',
                'note' => 'Test Pay Later Option',
                'payment_option' => 'later'
            ]);
        
        $responseLater->assertRedirect(route('user.payments.history'));
        $responseLater->assertSessionHas('success');
    }
}
