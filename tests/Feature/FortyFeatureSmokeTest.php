<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class FortyFeatureSmokeTest extends TestCase
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

    public function test_all_forty_feature_main_pages_do_not_break(): void
    {
        $admin = $this->userForRole(1, 'forty_admin');
        $patient = $this->userForRole(3, 'forty_patient');
        $doctorUser = $this->doctorUser();
        $appointment = $this->patientAppointment($patient);
        $schedule = $appointment->schedule;

        $pages = [
            ['01_DatLichHen', $patient, 'appointments.create'],
            ['02_HuyLichHen', $patient, 'appointments.index'],
            ['03_DoiLichHen', $patient, 'appointments.edit', ['id' => $appointment->appointment_id]],
            ['04_XemDanhSachLichHen', $patient, 'appointments.index'],
            ['05_HangDoiKhamBenh_TV', null, 'queue.display.index'],
            ['05_HangDoiKhamBenh_LeTan', $admin, 'queue.manage.index'],
            ['05_HangDoiKhamBenh_BacSi', $doctorUser, 'queue.doctor.index'],
            ['06_TimKiemBenhNhan', $admin, 'admin.patients.search'],
            ['07_ChatCskh_User', $patient, 'chat.index'],
            ['07_ChatCskh_Admin', $admin, 'admin.chatroom.index'],
            ['08_NhatKyHoatDong', $admin, 'admin.activity-logs.index'],
            ['09_BanTinBenhVien_Public', null, 'news.index'],
            ['09_BanTinBenhVien_Admin', $admin, 'admin.news.index'],
            ['10_ThongBao', $patient, 'notifications.index'],
            ['11_TienSuDiUng', $patient, 'health.index'],
            ['12_HoSoCaNhan', $patient, 'profile.show'],
            ['12_HoTroLienHe', $patient, 'emergency-contacts.index'],
            ['13_ThanhVienUuDai', $patient, 'membership.show'],
            ['14_NhacNhoDieuTri', $patient, 'treatment.index'],
            ['15_DanhSachPhieuKham', $patient, 'medical-records.index'],
            ['15_QuanLiThietBi', $admin, 'admin.devices.index'],
            ['16_HoSoBenhAn', $patient, 'medical-records.index'],
            ['17_ThuVienPhucHoi_User', $patient, 'rehab.index'],
            ['17_ThuVienPhucHoi_Admin', $admin, 'admin.rehab.index'],
            ['18_CheDoDinhDuong_User', $patient, 'patient.nutrition.index'],
            ['18_CheDoDinhDuong_Admin', $admin, 'admin.nutrition.index'],
            ['19_NhatKySucKhoe', $patient, 'health-tracking.index'],
            ['20_LuuTruTraCuuYKhoa', $patient, 'documents.index'],
            ['21_GoiYBacSi', $patient, 'appointments.create'],
            ['22_TaoGioThongMinh', $patient, 'appointments.create'],
            ['23_UocLuongThoiGian', null, 'queue.display', ['scheduleId' => $schedule->schedule_id]],
            ['24_NhacLichHen', $patient, 'notifications.index'],
            ['25_DanhGiaSauKham', $patient, 'reviews.check'],
            ['26_CaiDatLichLamViec', $doctorUser, 'doctor.schedule'],
            ['27_QuanLyNgayNghi', $doctorUser, 'doctor.schedule'],
            ['28_GiuSlot', $patient, 'appointments.create'],
            ['29_TuDongDoiLich', $patient, 'appointments.doctor-off', ['id' => $appointment->appointment_id]],
            ['30_DashboardBacSi_ThongKe', $doctorUser, 'doctor.dashboard'],
            ['31_QuanLyPhongKham', $admin, 'admin.rooms.index'],
            ['32_QuanLyDanhMucDichVu', $admin, 'admin.services.index'],
            ['33_ThanhToanOnline_User', $patient, 'user.payments.history'],
            ['33_ThanhToanOnline_Admin', $admin, 'admin.payments.index'],
            ['34_QuanLyBHYT', $admin, 'admin.bhyt.index'],
            ['35_QuanLyVienPhi', $admin, 'admin.payments.index'],
            ['36_ThongKeTheoBS', $admin, 'admin.doctor-statistics.index'],
            ['37_QuanLyTiemChung_Vaccine', $admin, 'admin.vaccines.index'],
            ['37_QuanLyTiemChung_Record', $admin, 'admin.vaccination-records.index'],
            ['38_BaoCaoDoanhThu', $admin, 'admin.revenue.index'],
            ['39_BenhNhanUuTien', $admin, 'queue.manage.checkin'],
            ['40_CRUDThanhToan', $admin, 'admin.payments.index'],
        ];

        foreach ($pages as $page) {
            [$feature, $actor, $routeName] = $page;
            $params = $page[3] ?? [];

            if (! Route::has($routeName)) {
                $this->fail("{$feature}: route {$routeName} không tồn tại.");
            }

            $response = $actor
                ? $this->actingAs($actor)->get(route($routeName, $params))
                : $this->get(route($routeName, $params));

            $this->assertLessThan(500, $response->getStatusCode(), "{$feature}: route {$routeName} bị lỗi server."); /* fixed: smoke test chống 500 cho 40 chức năng */
        }
    }

    public function test_forty_feature_api_and_invalid_url_cases_are_handled(): void
    {
        $admin = $this->userForRole(1, 'forty_api_admin');
        $patient = $this->userForRole(3, 'forty_api_patient');
        $doctorUser = $this->doctorUser();
        $appointment = $this->patientAppointment($patient);
        $schedule = $appointment->schedule;

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [[
                        'text' => json_encode([
                            'keyword' => null,
                            'gender' => 'Nam',
                            'sort_by' => 'created_at',
                            'sort_dir' => 'desc',
                        ]),
                    ]]],
                ]],
            ], 200),
        ]);
        config(['services.gemini.api_key' => 'TEST_GEMINI_API_KEY']);

        $this->actingAs($admin)
            ->getJson(route('admin.patients.search.results', ['gender' => 'sai-select']))
            ->assertUnprocessable(); /* fixed: select bị sửa bằng DevTools phải báo lỗi */

        $this->actingAs($admin)
            ->postJson(route('admin.patients.ai-search'), ['query' => 'Tìm bệnh nhân nam'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->actingAs($admin)
            ->get(route('admin.chatroom.list'))
            ->assertRedirect(route('admin.chatroom.index'))
            ->assertSessionHas('warning'); /* fixed: mở API bằng browser không in JSON thô */

        $this->actingAs($patient)
            ->get(route('notifications.dropdown'))
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('warning'); /* fixed: notification dropdown phải quay về màn hình chính */

        $this->actingAs($doctorUser)
            ->getJson('/api/v1/schedules/day-off/' . $doctorUser->doctor->doctor_id)
            ->assertOk()
            ->assertJsonStructure(['success']);

        $this->getJson(route('queue.api.display', ['scheduleId' => $schedule->schedule_id]))
            ->assertOk()
            ->assertJsonStructure(['current', 'waiting', 'stats']);

        $invalidIdResponse = $this->actingAs($patient)->get('/lich-hen/abc/doi');
        $this->assertLessThan(500, $invalidIdResponse->getStatusCode()); /* fixed: URL id chữ không được bung 500 */
    }

    private function userForRole(int $roleId, string $prefix): User
    {
        return User::where('role_id', $roleId)->first()
            ?? User::create([
                'full_name' => ucfirst($prefix),
                'email' => $prefix . '_' . uniqid() . '@example.test',
                'password' => Hash::make('secret123'),
                'role_id' => $roleId,
                'status' => 1,
            ]);
    }

    private function doctorUser(): User
    {
        $doctor = Doctor::with('user')
            ->whereHas('user', fn ($query) => $query->where('role_id', 2))
            ->first();
        if ($doctor?->user) {
            return $doctor->user;
        }

        $department = Department::firstOrCreate(
            ['department_name' => 'Khoa kiểm thử'],
            ['description' => 'Dữ liệu smoke test', 'status' => 1]
        );

        $user = $this->userForRole(2, 'forty_doctor');
        Doctor::firstOrCreate(
            ['user_id' => $user->user_id],
            [
                'full_name' => $user->full_name,
                'department_id' => $department->department_id,
                'experience' => 1,
                'price' => 100000,
                'status' => 1,
            ]
        );

        return $user->fresh('doctor');
    }

    private function patientAppointment(User $patient): Appointment
    {
        $appointment = Appointment::with('schedule')
            ->where('user_id', $patient->user_id)
            ->whereIn('status', ['Chờ xác nhận', 'Đã xác nhận'])
            ->where('appointment_time', '>', now()->addHours(2))
            ->first();

        if ($appointment) {
            return $appointment;
        }

        $doctor = Doctor::firstOrFail();
        $room = Room::firstOrCreate(
            ['room_code' => 'SMOKE-ROOM'],
            ['room_name' => 'Phòng smoke test', 'room_type' => 'Khám bệnh', 'status' => 'Hoạt động']
        );

        $workDate = today()->addDays(7);
        while (DoctorSchedule::where('doctor_id', $doctor->doctor_id)
            ->whereDate('work_date', $workDate)
            ->where('start_time', '07:31:00')
            ->exists()) {
            $workDate = $workDate->copy()->addDay();
        }

        $schedule = DoctorSchedule::create([
            'doctor_id' => $doctor->doctor_id,
            'room_id' => $room->room_id,
            'work_date' => $workDate,
            'start_time' => '07:31:00',
            'end_time' => '08:01:00',
            'slot_duration' => 15,
            'max_slot' => 20,
            'status' => 'Hoạt động',
        ]);

        return Appointment::create([
            'user_id' => $patient->user_id,
            'schedule_id' => $schedule->schedule_id,
            'appointment_time' => $workDate->copy()->setTime(7, 31),
            'queue_number' => 1,
            'status' => 'Đã xác nhận',
            'created_at' => now(),
            'version' => 1,
        ])->load('schedule');
    }
}
