<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PatientSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Đảm bảo kết nối đúng DB
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

    public function test_admin_can_access_search_dashboard_and_perform_ajax_search(): void
    {
        // 1. Tạo 1 Admin
        $admin = User::create([
            'full_name' => 'Search Test Admin',
            'email' => 'search_admin_' . uniqid() . '@example.test',
            'password' => Hash::make('secret123'),
            'role_id' => 1, // Admin
            'status' => 1,
        ]);

        // 2. Tạo 1 bệnh nhân mẫu
        $patient = User::create([
            'full_name' => 'Search Test Patient Lan',
            'email' => 'patient_lan_' . uniqid() . '@example.test',
            'password' => Hash::make('secret123'),
            'role_id' => 3, // Bệnh nhân
            'gender' => 'Nữ',
            'date_of_birth' => '1990-05-15',
            'status' => 1,
        ]);

        try {
            // 3. Test truy cập Dashboard
            $this->actingAs($admin)
                ->get(route('admin.patients.search'))
                ->assertOk()
                ->assertViewIs('admin.patients.search')
                ->assertSee('Tìm kiếm Bệnh nhân Nâng cao');

            // 4. Test tìm kiếm thường bằng AJAX
            $response = $this->actingAs($admin)
                ->getJson(route('admin.patients.search.results', [
                    'keyword' => 'Lan',
                    'gender' => 'Nữ'
                ]))
                ->assertOk()
                ->assertJsonStructure(['success', 'html', 'total', 'current_page', 'last_page']);

            $this->assertTrue($response->json('total') >= 1);

            // 5. Test xem chi tiết bệnh nhân qua AJAX
            $this->actingAs($admin)
                ->getJson(route('admin.patients.detail', $patient->user_id))
                ->assertOk()
                ->assertJsonStructure(['success', 'html']);

            $this->actingAs($admin)
                ->getJson(route('admin.patients.search.results', [
                    'age_from' => 80,
                    'age_to' => 20,
                ]))
                ->assertUnprocessable(); // fixed: tuoi tu khong duoc lon hon tuoi den

            $this->actingAs($admin)
                ->getJson(route('admin.patients.search.results', [
                    'appointment_status' => 'sai-trang-thai',
                ]))
                ->assertUnprocessable(); // fixed: chan enum trang thai lich hen khong hop le

        } finally {
            $patient->delete();
            $admin->delete();
        }
    }

    public function test_ai_search_with_gemini_api_integration(): void
    {
        // Thiết lập key giả để test logic
        config(['services.gemini.api_key' => 'TEST_GEMINI_API_KEY']);

        $admin = User::create([
            'full_name' => 'AI Test Admin',
            'email' => 'ai_admin_' . uniqid() . '@example.test',
            'password' => Hash::make('secret123'),
            'role_id' => 1,
            'status' => 1,
        ]);

        // Giả lập Response của Gemini
        $mockJson = json_encode([
            'keyword' => 'Lan',
            'gender' => 'Nữ',
            'age_from' => 30,
            'age_to' => 45,
            'appointment_status' => null,
            'has_insurance' => '1',
            'membership_tier' => 'Vàng',
            'chronic_disease' => 'tiểu đường',
            'allergy' => null,
            'sort_by' => 'created_at',
            'sort_dir' => 'desc',
            'explanation' => 'Đã tìm kiếm bệnh nhân nữ tên Lan trong độ tuổi 30-45 có bảo hiểm vàng bị tiểu đường'
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => $mockJson]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        try {
            $response = $this->actingAs($admin)
                ->postJson(route('admin.patients.ai-search'), [
                    'query' => 'tìm bệnh nhân nữ tên Lan 35 tuổi có bảo hiểm vàng bị tiểu đường'
                ])
                ->assertOk()
                ->assertJson([
                    'success' => true,
                    'filters' => [
                        'keyword' => 'Lan',
                        'gender' => 'Nữ',
                        'membership_tier' => 'Vàng'
                    ],
                    'explanation' => 'Đã tìm kiếm bệnh nhân nữ tên Lan trong độ tuổi 30-45 có bảo hiểm vàng bị tiểu đường'
                ]);

            $this->actingAs($admin)
                ->postJson(route('admin.patients.ai-search'), [
                    'query' => str_repeat('a', 501),
                ])
                ->assertUnprocessable(); // fixed: gioi han do dai cau hoi AI

        } finally {
            $admin->delete();
        }
    }
}
