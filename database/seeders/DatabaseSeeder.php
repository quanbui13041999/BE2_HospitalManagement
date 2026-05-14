<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        DB::table('roles')->insertOrIgnore([
            ['role_id' => 1, 'role_name' => 'Admin'],
            ['role_id' => 2, 'role_name' => 'Doctor'],
            ['role_id' => 3, 'role_name' => 'Patient'],
        ]);

        // 2. Users (10 Admins, 10 Doctors, 20 Patients)
        $users = [];
        for ($i = 1; $i <= 10; $i++) {
            $users[] = [
                'user_id' => $i,
                'full_name' => "Quản trị viên $i",
                'email' => "admin$i@hospital.com",
                'password' => Hash::make('password'),
                'role_id' => 1,
                'status' => 1,
            ];
        }
        for ($i = 11; $i <= 20; $i++) {
            $users[] = [
                'user_id' => $i,
                'full_name' => "Bác sĩ " . ($i - 10),
                'email' => "doctor" . ($i - 10) . "@hospital.com",
                'password' => Hash::make('password'),
                'role_id' => 2,
                'status' => 1,
            ];
        }
        for ($i = 21; $i <= 40; $i++) {
            $users[] = [
                'user_id' => $i,
                'full_name' => "Bệnh nhân " . ($i - 20),
                'email' => "patient" . ($i - 20) . "@gmail.com",
                'password' => Hash::make('password'),
                'role_id' => 3,
                'status' => 1,
            ];
        }
        DB::table('users')->insertOrIgnore($users);

        // 3. Departments
        $depts = [];
        $deptNames = ['Nội tổng quát', 'Nhi khoa', 'Tim mạch', 'Tai Mũi Họng', 'Da liễu', 'Nhãn khoa', 'Răng Hàm Mặt', 'Sản khoa', 'Chỉnh hình', 'Thần kinh'];
        foreach ($deptNames as $idx => $name) {
            $depts[] = ['department_id' => $idx + 1, 'department_name' => $name, 'description' => "Chuyên khoa $name", 'status' => 1];
        }
        DB::table('departments')->insertOrIgnore($depts);

        // 4. Rooms
        $rooms = [];
        for ($i = 1; $i <= 10; $i++) {
            $rooms[] = [
                'room_id' => $i,
                'room_code' => "ROOM-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'room_name' => "Phòng khám $i",
                'department_id' => ($i % 10) + 1,
                'room_type' => 'Phòng khám',
                'status' => 'Sẵn sàng',
            ];
        }
        DB::table('rooms')->insertOrIgnore($rooms);

        // 5. Doctors Profile
        $doctors = [];
        for ($i = 1; $i <= 10; $i++) {
            $doctors[] = [
                'doctor_id' => $i,
                'user_id' => $i + 10,
                'full_name' => "Bác sĩ Chuyên khoa " . ($i),
                'department_id' => ($i % 10) + 1,
                'experience' => 5 + $i,
                'price' => 100000 + ($i * 20000),
                'bio' => "Bác sĩ giàu kinh nghiệm trong lĩnh vực chuyên môn của mình.",
                'status' => 1,
            ];
        }
        DB::table('doctors')->insertOrIgnore($doctors);

        // 6. Services
        $services = [];
        for ($i = 1; $i <= 10; $i++) {
            $services[] = [
                'service_id' => $i,
                'service_code' => "SV-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'service_name' => "Dịch vụ y tế $i",
                'base_price' => 100000 + ($i * 10000),
                'status' => 1,
            ];
        }
        DB::table('services')->insertOrIgnore($services);

        // 7. Vaccines
        $vaccines = [];
        $vNames = ['AstraZeneca', 'Pfizer', 'Moderna', 'Sinopharm', 'Vero Cell', 'Abdala', 'Sputnik V', 'Janssen', 'Covaxin', 'Novavax'];
        foreach ($vNames as $idx => $name) {
            $vaccines[] = ['vaccine_id' => $idx + 1, 'vaccine_name' => $name, 'manufacturer' => 'Hãng sản xuất ' . ($idx + 1), 'doses_required' => 2, 'status' => 1];
        }
        DB::table('vaccines')->insertOrIgnore($vaccines);

        // 8. Insurance Cards
        $insurances = [];
        for ($i = 1; $i <= 10; $i++) {
            $insurances[] = [
                'insurance_id' => $i,
                'user_id' => 20 + $i,
                'card_number' => "BHYT-" . (1000000000 + $i),
                'issued_date' => '2024-01-01',
                'expiry_date' => '2026-12-31',
                'discount_pct' => 80,
                'status' => 'Còn hạn',
            ];
        }
        DB::table('insurancecards')->insertOrIgnore($insurances);

        // 9. Appointments
        $appointments = [];
        for ($i = 1; $i <= 10; $i++) {
            $appointments[] = [
                'appointment_id' => $i,
                'user_id' => 20 + $i,
                'service_id' => ($i % 10) + 1,
                'appointment_time' => now()->addDays($i)->format('Y-m-d 08:30:00'),
                'status' => 'Đã xác nhận',
                'created_at' => now(),
            ];
        }
        DB::table('appointments')->insertOrIgnore($appointments);

        // 10. Payments
        $payments = [];
        for ($i = 1; $i <= 10; $i++) {
            $payments[] = [
                'payment_id' => $i,
                'appointment_id' => $i,
                'subtotal' => 150000 + ($i * 20000),
                'total_amount' => 150000 + ($i * 20000),
                'status' => 'Đã thanh toán',
                'payment_method' => 'Tiền mặt',
                'payment_date' => now(),
                'created_at' => now(),
            ];
        }
        DB::table('payments')->insertOrIgnore($payments);

        // 11. News
        $news = [];
        for ($i = 1; $i <= 10; $i++) {
            $news[] = [
                'news_id' => $i,
                'title' => "Tin tức y khoa số $i",
                'content' => "Đây là nội dung chi tiết của bản tin y khoa số $i, cung cấp các thông tin quan trọng về sức khỏe và dịch vụ bệnh viện.",
                'category' => 'Y tế',
                'is_published' => 1,
                'published_at' => now(),
                'created_at' => now(),
            ];
        }
        DB::table('hospitalnews')->insertOrIgnore($news);

        // 12. Medicines
        $meds = [];
        for ($i = 1; $i <= 10; $i++) {
            $meds[] = [
                'medicine_id' => $i,
                'medicine_name' => "Thuốc biệt dược $i",
                'unit' => 'Hộp',
                'unit_price' => 20000 + ($i * 5000),
                'status' => 1,
            ];
        }
        DB::table('medicines')->insertOrIgnore($meds);
    }
}
