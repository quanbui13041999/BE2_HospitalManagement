<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SqlDumpDataSeeder extends Seeder
{
    /**
     * Baseline data inserted directly by the seeder.
     *
     * @var array<int, array{table: string, sql: string}>
     */
    private const STATEMENTS = [
        [
            'table' => 'activitylogs',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `activitylogs` (`log_id`, `user_id`, `actor_name`, `actor_email`, `role_name`, `action`, `subject_type`, `subject_id`, `description`, `metadata`, `ip_address`, `user_agent`, `status`, `created_at`) VALUES
(1, 1, NULL, NULL, NULL, 'Đặt lịch hẹn #1', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-04-23 04:53:29'),
(2, 1, NULL, NULL, NULL, 'Đặt lịch hẹn #2', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-04-23 07:09:56'),
(3, 1, NULL, NULL, NULL, 'Hủy lịch hẹn #2', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-04-23 07:10:54'),
(4, 1, NULL, NULL, NULL, 'Đặt lịch hẹn #3', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-04-23 07:12:49'),
(5, 1, NULL, NULL, NULL, 'Đặt lịch hẹn #4', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-04-23 07:16:20'),
(6, 1, NULL, NULL, NULL, 'Đặt lịch hẹn #5', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-04-23 07:19:22'),
(7, 1, NULL, NULL, NULL, 'Đặt lịch hẹn #6', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-04-23 10:00:41'),
(8, 1, NULL, NULL, NULL, 'Đặt lịch hẹn #7', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-04-23 10:08:06'),
(9, 1, NULL, NULL, NULL, 'Đặt lịch hẹn #8', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-04-23 10:27:33'),
(10, 2, NULL, NULL, NULL, 'Xác nhận đặt lịch hẹn #10', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-04-23 11:15:21'),
(11, 2, NULL, NULL, NULL, 'Xác nhận đặt lịch hẹn #12', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-04-23 11:21:25'),
(12, 1, NULL, NULL, NULL, 'Đặt lịch hẹn #16', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-04-23 13:23:49'),
(13, 1, NULL, NULL, NULL, 'Đặt lịch hẹn #17', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-04-23 13:43:24'),
(14, 3, NULL, NULL, NULL, 'Đặt lịch hẹn #18', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-04-23 15:08:02'),
(15, 1, NULL, NULL, NULL, 'Đặt lịch hẹn #19', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-04-23 15:17:18'),
(16, 1, NULL, NULL, NULL, 'Dời lịch hẹn #19 sang schedule #243', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-04-23 15:17:31'),
(17, 2, NULL, NULL, NULL, 'Đặt lịch hẹn #20', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-04-23 16:17:11'),
(18, 1, NULL, NULL, NULL, 'Đặt lịch hẹn #21', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-04-23 16:18:17'),
(19, 4, NULL, NULL, NULL, 'Đặt lịch hẹn #22', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-05 12:29:13'),
(20, 2, NULL, NULL, NULL, 'Đặt lịch hẹn #23', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-05 12:53:24'),
(21, 2, NULL, NULL, NULL, 'Đặt lịch hẹn #24', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-06 03:51:39'),
(22, 4, NULL, NULL, NULL, 'Đặt lịch hẹn #25', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-06 05:52:01'),
(23, 2, NULL, NULL, NULL, 'Đặt lịch hẹn #26', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-06 05:52:16'),
(24, 4, NULL, NULL, NULL, 'Đặt lịch hẹn #27', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-06 14:17:21'),
(25, 4, NULL, NULL, NULL, 'Đặt lịch hẹn #28', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-06 14:59:48'),
(26, 4, NULL, NULL, NULL, 'Tạo đánh giá bác sĩ #11', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-07 05:20:20'),
(27, 4, NULL, NULL, NULL, 'Đặt lịch hẹn #29', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-07 05:46:13'),
(28, 4, NULL, NULL, NULL, 'Tạo đánh giá bác sĩ #5', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-07 05:46:42'),
(29, 4, NULL, NULL, NULL, 'Cập nhật đánh giá #4', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-07 06:10:09'),
(30, 4, NULL, NULL, NULL, 'Xóa đánh giá #4 (bác sĩ #11)', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-07 06:10:18'),
(31, 4, NULL, NULL, NULL, 'Đặt lịch hẹn #30', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-07 16:39:03'),
(32, 4, NULL, NULL, NULL, 'Cập nhật đánh giá #5', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-07 16:40:23'),
(33, 4, NULL, NULL, NULL, 'Tạo đánh giá bác sĩ #7', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-07 16:41:18'),
(34, 4, NULL, NULL, NULL, 'Cập nhật đánh giá #6', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-07 16:41:43'),
(35, 4, NULL, NULL, NULL, 'Cập nhật đánh giá #5', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-07 16:48:23'),
(36, 4, NULL, NULL, NULL, 'Cập nhật đánh giá #6', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-07 16:52:20'),
(37, 21, NULL, NULL, NULL, 'Đặt lịch hẹn #31', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-07 16:55:17'),
(38, 21, NULL, NULL, NULL, 'Tạo đánh giá bác sĩ #10', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-07 16:56:06'),
(39, 21, NULL, NULL, NULL, 'Đặt lịch hẹn #32', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-07 17:00:23'),
(40, 21, NULL, NULL, NULL, 'Dời lịch hẹn #32 sang schedule #382', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-07 17:01:02'),
(41, 21, NULL, NULL, NULL, 'Tạo đánh giá bác sĩ #6', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-08 00:01:56'),
(42, 4, NULL, NULL, NULL, 'Tạo đánh giá bác sĩ #11', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-08 00:10:47'),
(43, 21, NULL, NULL, NULL, 'Cập nhật đánh giá #8', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-08 00:26:28'),
(44, 22, NULL, NULL, NULL, 'Đặt lịch hẹn #33', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-08 01:56:50'),
(45, 22, NULL, NULL, NULL, 'Tạo đánh giá bác sĩ #7', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-08 08:57:45'),
(46, 22, NULL, NULL, NULL, 'Cập nhật đánh giá #10', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-08 10:11:47'),
(47, 22, NULL, NULL, NULL, 'Cập nhật đánh giá #10', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-08 10:11:52'),
(48, 23, NULL, NULL, NULL, 'Đặt lịch hẹn #34', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-13 16:36:57'),
(49, 23, NULL, NULL, NULL, 'Dời lịch hẹn #34 sang schedule #382', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-13 16:37:35'),
(50, 23, NULL, NULL, NULL, 'Hủy lịch hẹn #34', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-13 16:42:54'),
(51, 23, NULL, NULL, NULL, 'Đặt lịch hẹn #35', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-13 16:53:38'),
(52, 23, 'Anh Tú Huỳnh', 'anh2482006@gmail.com', 'Admin', 'Đăng xuất', 'user', 23, 'Anh Tú Huỳnh đã đăng xuất khỏi hệ thống.', '[]', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success', '2026-05-28 15:49:55'),
(53, 24, 'Tú Huỳnh', 'tuh225095@gmail.com', 'Bệnh nhân', 'Đăng nhập', 'user', 24, 'Tú Huỳnh đã đăng nhập hệ thống.', '[]', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success', '2026-05-28 15:50:01'),
(54, 24, 'Tú Huỳnh', 'tuh225095@gmail.com', 'Bệnh nhân', 'Đăng xuất', 'user', 24, 'Tú Huỳnh đã đăng xuất khỏi hệ thống.', '[]', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success', '2026-05-28 15:50:03'),
(55, 23, 'Anh Tú Huỳnh', 'anh2482006@gmail.com', 'Admin', 'Đăng nhập', 'user', 23, 'Anh Tú Huỳnh đã đăng nhập hệ thống.', '[]', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success', '2026-05-28 15:50:08'),
(56, 30, 'Search Test Admin', 'search_admin_6a18649b5cb00@example.test', 'Admin', 'Admin xóa người dùng', 'user', 31, 'Admin Search Test Admin đã xóa người dùng Search Test Patient Lan.', '{\"deleted_user\": {\"email\": \"patient_lan_6a18649b6085e@example.test\", \"status\": true, \"role_id\": 3, \"user_id\": 31, \"full_name\": \"Search Test Patient Lan\"}}', '127.0.0.1', 'Symfony', 'success', '2026-05-28 15:51:55'),
(57, 30, 'Search Test Admin', 'search_admin_6a18649b5cb00@example.test', 'Admin', 'Admin xóa người dùng', 'user', 30, 'Admin Search Test Admin đã xóa người dùng Search Test Admin.', '{\"deleted_user\": {\"email\": \"search_admin_6a18649b5cb00@example.test\", \"status\": true, \"role_id\": 1, \"user_id\": 30, \"full_name\": \"Search Test Admin\"}}', '127.0.0.1', 'Symfony', 'success', '2026-05-28 15:51:55'),
(58, 32, 'AI Test Admin', 'ai_admin_6a18649bd1dad@example.test', 'Admin', 'Admin xóa người dùng', 'user', 32, 'Admin AI Test Admin đã xóa người dùng AI Test Admin.', '{\"deleted_user\": {\"email\": \"ai_admin_6a18649bd1dad@example.test\", \"status\": true, \"role_id\": 1, \"user_id\": 32, \"full_name\": \"AI Test Admin\"}}', '127.0.0.1', 'Symfony', 'success', '2026-05-28 15:51:55'),
(59, 24, NULL, NULL, NULL, 'Đặt lịch hẹn #36', NULL, NULL, NULL, NULL, '127.0.0.1', NULL, 'success', '2026-05-28 23:18:56')
SQL,
        ],
        [
            'table' => 'appointments',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `appointments` (`appointment_id`, `user_id`, `schedule_id`, `service_id`, `appointment_time`, `appointment_timeEnd`, `queue_number`, `status`, `is_priority`, `priority_type`, `note`, `cancel_reason`, `slot_hold_expire`, `rescheduled_from`, `created_at`, `mail_reminded_1day`, `mail_reminded_1hour`) VALUES
(27, 4, 296, NULL, '2026-05-06 09:30:00', '2026-05-06 10:00:00', 1, 'Chờ xác nhận', 0, NULL, NULL, NULL, NULL, NULL, '2026-05-06 14:17:21', 0, 0),
(28, 4, 445, NULL, '2026-05-14 14:00:00', '2026-05-14 14:30:00', 1, 'Đã Khám', 0, NULL, NULL, NULL, NULL, NULL, '2026-05-06 14:59:48', 0, 0),
(29, 4, 357, NULL, '2026-05-07 14:30:00', '2026-05-07 15:00:00', 1, 'Hoàn Thành', 0, NULL, NULL, NULL, NULL, NULL, '2026-05-07 05:46:13', 0, 0),
(30, 4, 390, NULL, '2026-05-07 09:00:00', '2026-05-07 09:30:00', 1, 'Hoàn Thành', 0, NULL, NULL, NULL, NULL, NULL, '2026-05-07 16:39:03', 0, 0),
(31, 21, 427, NULL, '2026-05-07 09:00:00', '2026-05-07 09:30:00', 1, 'Hoàn Thành', 0, NULL, NULL, NULL, NULL, NULL, '2026-05-07 16:55:17', 0, 0),
(32, 21, 382, NULL, '2026-05-15 14:00:00', '2026-05-07 10:30:00', 1, 'Hoàn Thành', 0, NULL, NULL, 'Dời sang lịch mới', NULL, 376, '2026-05-07 17:00:23', 0, 0),
(33, 22, 391, NULL, '2026-05-08 08:30:00', '2026-05-08 09:00:00', 1, 'Hoàn Thành', 0, NULL, NULL, NULL, NULL, NULL, '2026-05-08 01:56:50', 0, 0),
(34, 23, 382, NULL, '2026-05-15 14:00:00', '2026-05-13 10:00:00', 2, 'Đã hủy', 0, NULL, NULL, 'Bệnh nhân tự hủy', NULL, 380, '2026-05-13 16:36:57', 0, 0),
(35, 23, 394, 2, '2026-05-13 13:30:00', '2026-05-13 14:00:00', 1, 'Đã thanh toán', 0, NULL, NULL, NULL, NULL, NULL, '2026-05-13 16:53:38', 0, 0),
(36, 24, 338, NULL, '2026-05-28 08:30:00', '2026-05-28 09:00:00', 1, 'Đã thanh toán', 0, NULL, NULL, NULL, NULL, NULL, '2026-05-28 23:18:56', 0, 0)
SQL,
        ],
        [
            'table' => 'chatmessages',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `chatmessages` (`message_id`, `room_id`, `sender_id`, `message_text`, `is_read`, `is_ai`, `sent_at`) VALUES
(1, 1, 23, 'hi', 1, 0, '2026-05-13 18:56:55')
SQL,
        ],
        [
            'table' => 'chatrooms',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `chatrooms` (`room_id`, `user_id`, `doctor_id`, `status`, `created_at`, `closed_at`) VALUES
(1, 23, 0, 'Mở', '2026-05-13 18:56:49', NULL)
SQL,
        ],
        [
            'table' => 'departments',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `departments` (`department_id`, `department_name`, `description`, `status`) VALUES
(1, 'tim mach', 'aaa', 1),
(2, 'phụ sản', 'khoa phụ sản', 1),
(3, 'Nội tổng quát', 'Khám và điều trị các bệnh nội khoa thông thường', 1),
(4, 'Ngoại tổng quát', 'Phẫu thuật và điều trị ngoại khoa', 1),
(5, 'Nhi khoa', 'Chăm sóc sức khỏe trẻ em từ sơ sinh đến 15 tuổi', 1),
(6, 'Da liễu', 'Điều trị các bệnh về da, tóc, móng', 1),
(7, 'Mắt', 'Khám và điều trị bệnh lý về mắt', 1),
(8, 'Tai Mũi Họng', 'Chuyên khoa tai mũi họng', 1),
(9, 'Thần kinh', 'Điều trị các bệnh lý hệ thần kinh trung ương và ngoại biên', 1),
(10, 'Cơ xương khớp', 'Điều trị bệnh xương khớp, cột sống', 1),
(11, 'Tiêu hóa', 'Khám và điều trị bệnh lý đường tiêu hóa', 1),
(12, 'Nội tiết', 'Điều trị đái tháo đường, tuyến giáp, rối loạn nội tiết', 1)
SQL,
        ],
        [
            'table' => 'diagnoses',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `diagnoses` (`diagnosis_id`, `record_id`, `diagnosis_name`, `icd_code`, `diagnosis_type`, `note`, `created_at`, `updated_at`) VALUES
(1, 1, 'Viêm khớp', '44', 'secondary', NULL, '2026-05-14 03:18:03', '2026-05-14 03:18:03')
SQL,
        ],
        [
            'table' => 'disease_nutrition_rules',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `disease_nutrition_rules` (`rule_id`, `disease_name`, `icd_code`, `food_id`, `recommendation_type`, `reason`, `created_at`, `updated_at`) VALUES
(1, 'Tiểu đường', 'E11', 2, 'should_eat', 'Ức gà giàu protein và ít chất béo.', '2026-05-28 21:23:01', '2026-05-28 21:23:01'),
(2, 'Tiểu đường', 'E11', 1, 'should_avoid', 'Cơm trắng có chỉ số đường huyết cao.', '2026-05-28 21:23:01', '2026-05-28 21:23:01'),
(3, 'Cao huyết áp', 'I10', 3, 'should_eat', 'Cá hồi chứa omega-3 tốt cho tim mạch.', '2026-05-28 21:23:01', '2026-05-28 21:23:01'),
(4, 'Tim mạch', 'I25', 4, 'should_avoid', 'Ăn quá nhiều lòng đỏ trứng có thể tăng cholesterol.', '2026-05-28 21:23:01', '2026-05-28 21:23:01')
SQL,
        ],
        [
            'table' => 'doctors',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `doctors` (`doctor_id`, `user_id`, `full_name`, `department_id`, `experience`, `price`, `avatar_url`, `bio`, `status`) VALUES
(1, 1, 'aaanguyeenx van a', 1, 20, 20000.00, NULL, 'bác sĩ tim mạch', 1),
(2, 0, 'nguyễn thị c', 2, 15, 99999999.99, NULL, 'bác sĩ phụ sản', 1),
(3, 11, 'Nguyễn Thị Thu', 3, 18, 250000.00, NULL, 'Chuyên gia nội khoa với 18 năm kinh nghiệm điều trị các bệnh mạn tính', 1),
(4, 12, 'Trần Văn Minh', 4, 22, 350000.00, NULL, 'Phẫu thuật viên ngoại khoa, chuyên về phẫu thuật nội soi tiêu hóa', 1),
(5, 13, 'Lê Thị Phương', 5, 15, 200000.00, NULL, 'Bác sĩ nhi khoa, chuyên điều trị bệnh hô hấp và tiêu hóa ở trẻ', 1),
(6, 14, 'Phạm Đức Long', 6, 12, 300000.00, NULL, 'Chuyên gia da liễu thẩm mỹ, điều trị mụn trứng cá và rối loạn sắc tố', 1),
(7, 15, 'Hoàng Văn Tùng', 7, 20, 280000.00, NULL, 'Bác sĩ nhãn khoa, chuyên điều trị đục thủy tinh thể và glaucoma', 1),
(8, 16, 'Vũ Thị Ngọc', 8, 14, 220000.00, NULL, 'Chuyên khoa tai mũi họng, chuyên phẫu thuật amidan và VA', 1),
(9, 17, 'Đinh Quốc Huy', 9, 25, 400000.00, NULL, 'Giáo sư thần kinh học, nguyên Trưởng khoa Thần kinh BV Chợ Rẫy', 1),
(10, 18, 'Ngô Thị Bích', 10, 16, 260000.00, NULL, 'Chuyên gia cơ xương khớp, điều trị viêm khớp và thoát vị đĩa đệm', 1),
(11, 19, 'Lý Văn Thành', 11, 19, 320000.00, '2305_hinh-nen-4k-one-piece23.jpg', 'Bác sĩ tiêu hóa, chuyên nội soi dạ dày đại tràng', 1),
(12, 20, 'Đặng Thị Hằng', 12, 17, 290000.00, NULL, 'Chuyên gia nội tiết, điều trị đái tháo đường và bệnh tuyến giáp', 1)
SQL,
        ],
        [
            'table' => 'doctorschedules',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `doctorschedules` (`schedule_id`, `doctor_id`, `room_id`, `work_date`, `start_time`, `end_time`, `slot_duration`, `max_slot`, `status`, `note`) VALUES
(411, 8, 8, '2026-05-18', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(410, 8, 8, '2026-05-15', '13:00:00', '17:00:00', 30, 8, 'Hoạt động', NULL),
(409, 8, 8, '2026-05-14', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(408, 8, 8, '2026-05-13', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(407, 8, 8, '2026-05-12', '13:00:00', '17:00:00', 30, 8, 'Hoạt động', NULL),
(406, 8, 8, '2026-05-11', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(405, 8, 8, '2026-05-08', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(404, 8, 8, '2026-05-07', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(403, 7, 7, '2026-06-02', '08:00:00', '11:00:00', 30, 6, 'Hoạt động', NULL),
(402, 7, 7, '2026-06-01', '08:00:00', '11:00:00', 30, 6, 'Hoạt động', NULL),
(401, 7, 7, '2026-05-26', '08:00:00', '11:00:00', 30, 6, 'Hoạt động', NULL),
(400, 7, 7, '2026-05-25', '08:00:00', '11:00:00', 30, 6, 'Hoạt động', NULL),
(399, 7, 7, '2026-05-20', '08:00:00', '11:00:00', 30, 6, 'Hoạt động', NULL),
(398, 7, 7, '2026-05-19', '13:00:00', '16:00:00', 30, 6, 'Hoạt động', NULL),
(397, 7, 7, '2026-05-18', '08:00:00', '11:00:00', 30, 6, 'Hoạt động', NULL),
(396, 7, 7, '2026-05-15', '08:00:00', '11:00:00', 30, 6, 'Hoạt động', NULL),
(395, 7, 7, '2026-05-14', '08:00:00', '11:00:00', 30, 6, 'Hoạt động', NULL),
(394, 7, 7, '2026-05-13', '13:00:00', '16:00:00', 30, 6, 'Hoạt động', NULL),
(393, 7, 7, '2026-05-12', '08:00:00', '11:00:00', 30, 6, 'Hoạt động', NULL),
(392, 7, 7, '2026-05-11', '08:00:00', '11:00:00', 30, 6, 'Hoạt động', NULL),
(391, 7, 7, '2026-05-08', '08:00:00', '11:00:00', 30, 6, 'Hoạt động', NULL),
(390, 7, 7, '2026-05-07', '08:00:00', '11:00:00', 30, 6, 'Hoạt động', NULL),
(389, 6, 6, '2026-06-03', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(388, 6, 6, '2026-06-02', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(387, 6, 6, '2026-05-27', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(386, 6, 6, '2026-05-26', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(385, 6, 6, '2026-05-21', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(384, 6, 6, '2026-05-20', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(383, 6, 6, '2026-05-19', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(382, 6, 6, '2026-05-15', '14:00:00', '17:00:00', 30, 6, 'Hoạt động', NULL),
(381, 6, 6, '2026-05-14', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(380, 6, 6, '2026-05-13', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(379, 6, 6, '2026-05-12', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(378, 6, 6, '2026-05-09', '09:00:00', '12:00:00', 30, 4, 'Hoạt động', NULL),
(377, 6, 6, '2026-05-08', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(376, 6, 6, '2026-05-07', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(375, 5, 5, '2026-06-02', '08:00:00', '12:00:00', 30, 10, 'Hoạt động', NULL),
(374, 5, 5, '2026-06-01', '08:00:00', '12:00:00', 30, 10, 'Hoạt động', NULL),
(373, 5, 5, '2026-05-28', '08:00:00', '12:00:00', 30, 10, 'Hoạt động', NULL),
(372, 5, 5, '2026-05-27', '13:00:00', '17:00:00', 30, 10, 'Hoạt động', NULL),
(371, 5, 5, '2026-05-26', '08:00:00', '12:00:00', 30, 10, 'Hoạt động', NULL),
(370, 5, 5, '2026-05-25', '08:00:00', '12:00:00', 30, 10, 'Hoạt động', NULL),
(369, 5, 5, '2026-05-22', '08:00:00', '12:00:00', 30, 10, 'Hoạt động', NULL),
(368, 5, 5, '2026-05-21', '13:00:00', '17:00:00', 30, 10, 'Hoạt động', NULL),
(367, 5, 5, '2026-05-20', '08:00:00', '12:00:00', 30, 10, 'Hoạt động', NULL),
(366, 5, 5, '2026-05-19', '08:00:00', '12:00:00', 30, 10, 'Hoạt động', NULL),
(365, 5, 5, '2026-05-18', '08:00:00', '12:00:00', 30, 10, 'Hoạt động', NULL),
(364, 5, 5, '2026-05-15', '08:00:00', '12:00:00', 30, 10, 'Hoạt động', NULL),
(363, 5, 5, '2026-05-14', '13:00:00', '17:00:00', 30, 10, 'Hoạt động', NULL),
(362, 5, 5, '2026-05-13', '08:00:00', '12:00:00', 30, 10, 'Hoạt động', NULL),
(361, 5, 5, '2026-05-12', '08:00:00', '12:00:00', 30, 10, 'Hoạt động', NULL),
(360, 5, 5, '2026-05-11', '08:00:00', '12:00:00', 30, 10, 'Hoạt động', NULL),
(359, 5, 5, '2026-05-09', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(358, 5, 5, '2026-05-08', '08:00:00', '12:00:00', 30, 10, 'Hoạt động', NULL),
(357, 5, 5, '2026-05-07', '13:00:00', '17:00:00', 30, 10, 'Hoạt động', NULL),
(356, 5, 5, '2026-05-07', '08:00:00', '12:00:00', 30, 10, 'Hoạt động', NULL),
(355, 4, 4, '2026-06-02', '08:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(354, 4, 4, '2026-06-01', '08:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(353, 4, 4, '2026-05-26', '08:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(352, 4, 4, '2026-05-25', '08:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(351, 4, 4, '2026-05-20', '13:00:00', '17:00:00', 30, 6, 'Hoạt động', NULL),
(350, 4, 4, '2026-05-19', '08:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(349, 4, 4, '2026-05-18', '08:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(348, 4, 4, '2026-05-14', '08:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(347, 4, 4, '2026-05-13', '13:00:00', '17:00:00', 30, 6, 'Hoạt động', NULL),
(346, 4, 4, '2026-05-12', '08:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(345, 4, 4, '2026-05-11', '08:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(344, 4, 4, '2026-05-08', '08:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(343, 4, 4, '2026-05-07', '13:00:00', '16:00:00', 30, 6, 'Hoạt động', NULL),
(342, 4, 4, '2026-05-07', '08:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(341, 3, 3, '2026-06-03', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(340, 3, 3, '2026-06-02', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(339, 3, 3, '2026-05-29', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(338, 3, 3, '2026-05-28', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(337, 3, 3, '2026-05-27', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(336, 3, 3, '2026-05-26', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(335, 3, 3, '2026-05-22', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(334, 3, 3, '2026-05-21', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(333, 3, 3, '2026-05-20', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(332, 3, 3, '2026-05-19', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(331, 3, 3, '2026-05-15', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(330, 3, 3, '2026-05-14', '13:00:00', '17:00:00', 30, 8, 'Hoạt động', NULL),
(329, 3, 3, '2026-05-14', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(328, 3, 3, '2026-05-13', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(327, 3, 3, '2026-05-12', '13:00:00', '17:00:00', 30, 8, 'Hoạt động', NULL),
(326, 3, 3, '2026-05-12', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(325, 3, 3, '2026-05-09', '08:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(324, 3, 3, '2026-05-08', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(323, 3, 3, '2026-05-07', '13:00:00', '17:00:00', 30, 8, 'Hoạt động', NULL),
(322, 3, 3, '2026-05-07', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(412, 8, 8, '2026-05-19', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(413, 8, 8, '2026-05-20', '13:00:00', '17:00:00', 30, 8, 'Hoạt động', NULL),
(414, 8, 8, '2026-05-25', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(415, 8, 8, '2026-05-26', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(416, 8, 8, '2026-06-01', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(417, 9, 9, '2026-05-07', '08:00:00', '11:00:00', 30, 5, 'Hoạt động', NULL),
(418, 9, 9, '2026-05-08', '08:00:00', '11:00:00', 30, 5, 'Hoạt động', NULL),
(419, 9, 9, '2026-05-12', '08:00:00', '11:00:00', 30, 5, 'Hoạt động', NULL),
(420, 9, 9, '2026-05-13', '08:00:00', '11:00:00', 30, 5, 'Hoạt động', NULL),
(421, 9, 9, '2026-05-14', '13:00:00', '16:00:00', 30, 5, 'Hoạt động', NULL),
(422, 9, 9, '2026-05-19', '08:00:00', '11:00:00', 30, 5, 'Hoạt động', NULL),
(423, 9, 9, '2026-05-20', '08:00:00', '11:00:00', 30, 5, 'Hoạt động', NULL),
(424, 9, 9, '2026-05-26', '08:00:00', '11:00:00', 30, 5, 'Hoạt động', NULL),
(425, 9, 9, '2026-05-27', '08:00:00', '11:00:00', 30, 5, 'Hoạt động', NULL),
(426, 9, 9, '2026-06-02', '08:00:00', '11:00:00', 30, 5, 'Hoạt động', NULL),
(427, 10, 10, '2026-05-07', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(428, 10, 10, '2026-05-08', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(429, 10, 10, '2026-05-11', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(430, 10, 10, '2026-05-12', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(431, 10, 10, '2026-05-13', '13:00:00', '17:00:00', 30, 8, 'Hoạt động', NULL),
(432, 10, 10, '2026-05-14', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(433, 10, 10, '2026-05-15', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(434, 10, 10, '2026-05-18', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(435, 10, 10, '2026-05-19', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(436, 10, 10, '2026-05-20', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(437, 10, 10, '2026-05-25', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(438, 10, 10, '2026-05-26', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(439, 10, 10, '2026-06-01', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(440, 11, 11, '2026-05-07', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(441, 11, 11, '2026-05-08', '13:00:00', '17:00:00', 30, 8, 'Hoạt động', NULL),
(442, 11, 11, '2026-05-11', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(443, 11, 11, '2026-05-12', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(444, 11, 11, '2026-05-13', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(445, 11, 11, '2026-05-14', '13:00:00', '17:00:00', 30, 8, 'Hoạt động', NULL),
(446, 11, 11, '2026-05-15', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(447, 11, 11, '2026-05-18', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(448, 11, 11, '2026-05-19', '13:00:00', '17:00:00', 30, 8, 'Hoạt động', NULL),
(449, 11, 11, '2026-05-20', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(450, 11, 11, '2026-05-25', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(451, 11, 11, '2026-06-01', '08:00:00', '12:00:00', 30, 8, 'Hoạt động', NULL),
(452, 12, 12, '2026-05-07', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(453, 12, 12, '2026-05-08', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(454, 12, 12, '2026-05-11', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(455, 12, 12, '2026-05-12', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(456, 12, 12, '2026-05-13', '14:00:00', '17:00:00', 30, 6, 'Hoạt động', NULL),
(457, 12, 12, '2026-05-14', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(458, 12, 12, '2026-05-15', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(459, 12, 12, '2026-05-18', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(460, 12, 12, '2026-05-19', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(461, 12, 12, '2026-05-20', '14:00:00', '17:00:00', 30, 6, 'Hoạt động', NULL),
(462, 12, 12, '2026-05-25', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(463, 12, 12, '2026-05-26', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL),
(464, 12, 12, '2026-06-01', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL)
SQL,
        ],
        [
            'table' => 'emergency_contacts',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `emergency_contacts` (`id`, `user_id`, `priority`, `name`, `relationship`, `phone`, `email`, `lab_notifications`, `recovery_updates`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 9, 1, 'Nguyễn thị', 'Vợ/Chồng', '0976662334', 'haha@gmail.com', 1, 1, '2026-05-02 06:37:36', '2026-05-02 06:37:36', NULL),
(2, 9, 2, 'Nguyễn Trần', 'Con', '0976662335', 'hihi@gmail.com', 1, 0, '2026-05-02 06:37:36', '2026-05-02 06:37:36', NULL),
(3, 9, 3, 'Nguyễn Thành Tài', 'Người giám hộ', '0976662337', 'hehe@gmail.com', 1, 0, '2026-05-02 06:37:36', '2026-05-02 06:41:10', NULL)
SQL,
        ],
        [
            'table' => 'foods',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `foods` (`food_id`, `food_name`, `calories_per_100g`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Cơm trắng', 130, 'Món ăn phổ biến được nấu từ gạo trắng.', 1, '2026-05-28 21:23:01', '2026-05-28 21:23:01'),
(2, 'Ức gà', 165, 'Thịt gà ít mỡ, giàu protein.', 1, '2026-05-28 21:23:01', '2026-05-28 21:23:01'),
(3, 'Cá hồi', 208, 'Loại cá giàu omega-3 và dinh dưỡng.', 1, '2026-05-28 21:23:01', '2026-05-28 21:23:01'),
(4, 'Trứng gà', 155, 'Nguồn protein và chất béo tốt cho cơ thể.', 1, '2026-05-28 21:23:01', '2026-05-28 21:23:01')
SQL,
        ],
        [
            'table' => 'health_backgrounds',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `health_backgrounds` (`id`, `user_id`, `blood_group`, `yeuto_rh`, `height`, `weight`, `bmi`, `food_allergies`, `drug_allergies`, `chronic_diseases`, `other_chronic_diseases`, `created_at`, `updated_at`) VALUES
(1, 9, 'A+', 'negative', 173, 80, 26.73, 'Bánh Kem , Lá Khổ Qua', 'Aspirin,vitaminC', '[\"TĂNG HUYẾT ÁP\", \"TUỘT HUYẾT ÁP\"]', 'aaa', '2026-05-04 20:28:57', '2026-05-04 20:28:57')
SQL,
        ],
        [
            'table' => 'health_trackings',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `health_trackings` (`id`, `patient_id`, `systolic`, `diastolic`, `heart_rate`, `spo2`, `weight`, `blood_sugar`, `symptoms`, `risk_level`, `risk_warnings`, `version`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 25, 110, 130, 110, 90, 58.00, 80, 'Mệt mỏi', 'danger', '[{\"icon\": \"bi-heart-pulse-fill\", \"field\": \"diastolic\", \"level\": \"danger\", \"message\": \"Huyết áp tâm trương rất cao (130 mmHg) - Nguy hiểm!\"}, {\"icon\": \"bi-lungs\", \"field\": \"spo2\", \"level\": \"warning\", \"message\": \"Nồng độ oxy thấp (90%) - Cần theo dõi.\"}]', 3, '2026-05-27 08:58:30', '2026-05-27 09:00:46', NULL)
SQL,
        ],
        [
            'table' => 'hospitalnews',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `hospitalnews` (`news_id`, `title`, `content`, `category`, `thumbnail`, `author_id`, `is_published`, `email_sent`, `published_at`, `created_at`) VALUES
(1, 'Khai trương phòng khám Da liễu – Thẩm mỹ hiện đại', 'Bệnh viện chính thức khai trương phòng khám Da liễu – Thẩm mỹ với trang thiết bị hiện đại nhất, bao gồm hệ thống laser thế hệ mới và đội ngũ bác sĩ chuyên khoa giàu kinh nghiệm.', 'Thông báo', NULL, 1, 1, 1, '2026-04-01 08:00:00', '2026-04-01 07:00:00'),
(2, 'Lịch nghỉ lễ 30/4 – 1/5 năm 2026', 'Thông báo lịch nghỉ lễ: Bệnh viện sẽ hoạt động 24/7 cho dịch vụ cấp cứu. Phòng khám ngoại trú nghỉ từ 30/4 đến 1/5. Từ ngày 2/5 hoạt động bình thường.', 'Thông báo', NULL, 1, 1, 1, '2026-04-20 09:00:00', '2026-04-20 08:00:00'),
(3, '5 dấu hiệu cảnh báo bệnh tim mạch bạn không nên bỏ qua', 'Bệnh tim mạch là nguyên nhân gây tử vong hàng đầu tại Việt Nam. Bài viết này giúp bạn nhận biết sớm 5 dấu hiệu cảnh báo để kịp thời thăm khám.', 'Sức khỏe', NULL, 1, 1, 1, '2026-04-15 10:00:00', '2026-04-15 09:00:00'),
(4, 'Tầm soát ung thư đại tràng miễn phí tháng 5/2026', 'Nhân dịp kỷ niệm ngày thành lập bệnh viện, chúng tôi tổ chức chương trình tầm soát ung thư đại tràng miễn phí cho 200 bệnh nhân đầu tiên đăng ký trong tháng 5.', 'Chương trình', NULL, 1, 1, 1, '2026-05-01 08:00:00', '2026-04-28 07:00:00'),
(5, 'Hướng dẫn đặt lịch khám trực tuyến qua ứng dụng', 'Bệnh viện triển khai hệ thống đặt lịch khám trực tuyến giúp bệnh nhân tiết kiệm thời gian chờ đợi. Bài viết hướng dẫn chi tiết cách sử dụng.', 'Hướng dẫn', NULL, 1, 1, 1, '2026-05-03 09:00:00', '2026-05-02 08:00:00'),
(6, 'Dinh dưỡng hợp lý cho người bệnh đái tháo đường', '<p><span style=\"font-family: \'comic sans ms\', sans-serif;\">Chế độ ăn đ&oacute;ng vai tr&ograve; quan trọng trong kiểm so&aacute;t đường huyết. B&agrave;i viết cung cấp hướng dẫn dinh dưỡng khoa học từ c&aacute;c chuy&ecirc;n gia Nội tiết của bệnh viện..😬</span></p>', 'Sức khỏe', 'news/kfCskEA8YScepTQULQd0IllqWSO6FvIh4jfIpAC4.jpg', 1, 1, 1, '2026-05-05 10:00:00', '2026-05-04 09:00:00'),
(7, 'Thông báo lịch nghỉ lễ 30/4 - 1/5', 'Bệnh viện sẽ hoạt động xuyên suốt trong dịp lễ 30/4 - 1/5 với đội ngũ trực đầy đủ nhằm phục vụ nhu cầu khám chữa bệnh của người dân.', 'Thông báo', 'uploads/news/news1.jpg', 8, 1, 0, '2026-05-14 00:48:55', '2026-05-14 00:48:55'),
(8, 'Chương trình khám sức khỏe miễn phí cho người cao tuổi', 'Từ ngày 15/05 đến 30/05, bệnh viện tổ chức khám tổng quát miễn phí cho người trên 60 tuổi. Vui lòng mang CCCD khi đăng ký.', 'Sự kiện', 'uploads/news/news2.jpg', 8, 1, 0, '2026-05-14 00:48:55', '2026-05-14 00:48:55'),
(9, 'Khuyến cáo phòng chống sốt xuất huyết mùa mưa', 'Người dân cần chủ động vệ sinh môi trường sống, loại bỏ nơi sinh sản của muỗi và đến bệnh viện ngay khi có dấu hiệu sốt kéo dài.', 'Sức khỏe', 'uploads/news/news3.jpg', 8, 1, 0, '2026-05-14 00:48:55', '2026-05-14 00:48:55'),
(10, 'Khai trương khoa Tim mạch công nghệ cao', 'Bệnh viện chính thức đưa vào hoạt động khoa Tim mạch công nghệ cao với nhiều thiết bị hiện đại giúp nâng cao chất lượng khám chữa bệnh.', 'Tin tức', 'uploads/news/news4.jpg', 8, 1, 0, '2026-05-14 00:48:55', '2026-05-14 00:48:55'),
(11, 'Thông báo thay đổi giờ khám cuối tuần', 'Bắt đầu từ tháng tới, bệnh viện mở rộng khung giờ khám vào thứ Bảy và Chủ nhật từ 7h00 đến 18h00.', 'Thông báo', 'uploads/news/news5.jpg', 8, 1, 0, '2026-05-14 00:48:55', '2026-05-14 00:48:55'),
(12, 'Tư vấn chăm sóc sức khỏe trẻ em mùa hè', 'Các bác sĩ nhi khoa khuyến cáo phụ huynh cần bổ sung đủ nước, dinh dưỡng và tiêm vaccine đầy đủ cho trẻ trong mùa hè.', 'Sức khỏe', 'uploads/news/news6.jpg', 8, 1, 0, '2026-05-14 00:48:55', '2026-05-14 00:48:55'),
(13, 'Chương trình ưu đãi khám tổng quát tháng này', 'Giảm 20% chi phí khám sức khỏe tổng quát cho khách hàng đăng ký online từ ngày 01 đến 31 hàng tháng.', 'Khuyến mãi', 'uploads/news/news7.jpg', 8, 1, 0, '2026-05-14 00:48:55', '2026-05-14 00:48:55'),
(14, 'Hướng dẫn đăng ký khám bệnh trực tuyến', 'Người dân có thể đặt lịch khám nhanh chóng thông qua website hoặc ứng dụng di động của bệnh viện để tiết kiệm thời gian chờ đợi.', 'Hướng dẫn', 'uploads/news/news8.jpg', 8, 1, 0, '2026-05-14 00:48:55', '2026-05-14 00:48:55'),
(15, 'Bệnh viện tiếp nhận máy chụp MRI thế hệ mới', 'Thiết bị MRI mới giúp chẩn đoán hình ảnh chính xác hơn, giảm thời gian chụp và nâng cao trải nghiệm bệnh nhân.', 'Tin tức', 'uploads/news/news9.jpg', 8, 1, 0, '2026-05-14 00:48:55', '2026-05-14 00:48:55'),
(16, 'Khuyến nghị tiêm vaccine cúm định kỳ', 'Bác sĩ khuyến nghị người cao tuổi, trẻ em và người có bệnh nền nên tiêm vaccine cúm mỗi năm để tăng cường miễn dịch.', 'Sức khỏe', 'uploads/news/news10.jpg', 8, 1, 0, '2026-05-14 00:48:55', '2026-05-14 00:48:55'),
(17, 'bệnh ế', '<p>&eacute;eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeavavvvaaaaaaaaaaaaa</p>', 'Thông báo', 'http://127.0.0.1:8000/uploads/news/news_6a1872ef6eb7d2.95883191.jpg', 23, 1, 0, '2026-05-28 23:53:03', '2026-05-28 23:53:03')
SQL,
        ],
        [
            'table' => 'insurancecards',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `insurancecards` (`insurance_id`, `user_id`, `card_number`, `provider`, `issued_date`, `expiry_date`, `discount_pct`, `status`, `created_at`, `updated_at`) VALUES
(1, 5, 'BH4030123456789', 'BHXH Việt Nam', '2025-01-01', '2026-12-31', 80.00, 'Còn hạn', '2026-05-06 21:19:14', NULL),
(2, 6, 'BH4030987654321', 'BHXH Việt Nam', '2025-01-01', '2026-12-31', 80.00, 'Còn hạn', '2026-05-06 21:19:14', NULL),
(3, 7, 'BH8020112233445', 'Bảo Việt', '2024-07-01', '2026-06-30', 50.00, 'Còn hạn', '2026-05-06 21:19:14', NULL),
(4, 8, 'BH4030556677889', 'BHXH Việt Nam', '2025-01-01', '2026-12-31', 80.00, 'Còn hạn', '2026-05-06 21:19:14', NULL),
(5, 9, 'BH8020998877665', 'Prudential', '2025-03-01', '2027-02-28', 60.00, 'Còn hạn', '2026-05-06 21:19:14', NULL),
(6, 10, 'BH4030001122334', 'BHXH Việt Nam', '2025-01-01', '2025-12-31', 80.00, 'Hết hạn', '2026-05-06 21:19:14', NULL)
SQL,
        ],
        [
            'table' => 'jobs',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(1, 'default', '{\"uuid\":\"54c50593-0a95-4cd8-9c2b-e14f9c38fead\",\"displayName\":\"App\\\\Events\\\\QueueUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:23:\\\"App\\\\Events\\\\QueueUpdated\\\":1:{s:10:\\\"scheduleId\\\";i:338;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1779985137,\"delay\":null}', 0, NULL, 1779985137, 1779985137),
(2, 'default', '{\"uuid\":\"66fd3e67-7c5d-4ed9-b397-fde2ae028e92\",\"displayName\":\"App\\\\Events\\\\QueueUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:23:\\\"App\\\\Events\\\\QueueUpdated\\\":1:{s:10:\\\"scheduleId\\\";i:338;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1779985137,\"delay\":null}', 0, NULL, 1779985137, 1779985137)
SQL,
        ],
        [
            'table' => 'medicalrecords',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `medicalrecords` (`record_id`, `appointment_id`, `user_id`, `doctor_id`, `diagnosis`, `prescription`, `follow_up_date`, `file_path`, `notes`, `created_at`) VALUES
(1, 29, 4, 5, 'Viêm họng cấp, amidan sưng độ 1. Không có biến chứng.', 'Amoxicillin 500mg x 3 viên/ngày x 7 ngày; Paracetamol 500mg khi sốt; Vitamin C 1000mg x 1 viên/ngày x 14 ngày', '2026-05-21', NULL, 'Bệnh nhân cần uống nhiều nước, nghỉ ngơi, tái khám nếu sốt cao không hạ sau 48h.', '2026-05-07 15:10:00'),
(2, 30, 4, 7, 'Viêm kết mạc dị ứng hai mắt. Thị lực bình thường.', 'Cetirizine 10mg x 1 viên/ngày x 7 ngày; Nhỏ mắt Cromoglicate 2% x 3 lần/ngày x 10 ngày', '2026-05-21', NULL, 'Tránh tiếp xúc với khói bụi, không dụi mắt. Đeo kính bảo hộ khi ra đường.', '2026-05-07 09:25:00'),
(3, 31, 21, 7, 'Cận thị độ cao 4.5 diop mắt phải, 4.0 diop mắt trái. Không có tổn thương võng mạc.', 'Đề xuất đo kính lại, xem xét phẫu thuật LASIK. Nhỏ mắt nhân tạo Hypromellose x 4 lần/ngày.', '2026-06-07', NULL, 'Bệnh nhân được tư vấn phẫu thuật khúc xạ. Hẹn tái khám để đánh giá giác mạc.', '2026-05-07 09:30:00'),
(4, 32, 21, 6, 'Mụn trứng cá độ 2 vùng mặt và cổ. Có sẹo thâm nhẹ.', 'Clindamycin gel 1% bôi buổi tối x 30 ngày; Benzoyl peroxide 5% bôi sáng x 30 ngày; Vitamin C 1000mg x 1 viên/ngày', '2026-06-15', NULL, 'Không nặn mụn, rửa mặt 2 lần/ngày, dùng kem chống nắng SPF 50+.', '2026-05-15 14:20:00'),
(5, 33, 22, 7, 'Khô mắt độ nhẹ, mỏi mắt do sử dụng màn hình máy tính nhiều. Áp lực mắt bình thường 14mmHg.', 'Nước mắt nhân tạo Systane Ultra x 4 lần/ngày; Omega-3 bổ sung x 2 viên/ngày x 30 ngày', '2026-06-08', NULL, 'Áp dụng quy tắc 20-20-20: cứ 20 phút nhìn xa 20 giây. Giảm thời gian dùng thiết bị điện tử.', '2026-05-08 09:05:00'),
(6, 35, 23, 7, 'Loạn thị nhẹ mắt phải 0.75 diop. Kết mạc bình thường. Không viêm nhiễm.', 'Kính điều chỉnh loạn thị, nhỏ mắt dưỡng Artelac x 3 lần/ngày x 14 ngày', '2026-07-13', NULL, 'Tái khám sau 2 tháng để kiểm tra tiến triển loạn thị.', '2026-05-13 13:45:00')
SQL,
        ],
        [
            'table' => 'medical_orders',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `medical_orders` (`order_id`, `record_id`, `order_type`, `order_name`, `description`, `result_status`, `result_note`, `created_at`, `updated_at`) VALUES
(1, 2, 'lab', 'Xét nghiệm HbA1c', 'Đánh giá kiểm soát đường huyết dài hạn', 'Có kết quả', 'HbA1c: 7.2% – kiểm soát chưa tốt, cần điều chỉnh thuốc', '2026-01-20 10:30:00', '2026-01-20 10:30:00'),
(2, 2, 'lab', 'Xét nghiệm đường huyết đói', 'Đo glucose máu sau nhịn ăn 8 giờ', 'Có kết quả', 'Glucose: 8.5 mmol/L – cao hơn bình thường (>7.0)', '2026-01-20 10:30:00', '2026-01-20 10:30:00'),
(3, 3, 'lab', 'Test HP (CLO test)', 'Xét nghiệm vi khuẩn H. pylori', 'Có kết quả', 'CLO test dương tính – cần phác đồ diệt HP', '2026-02-05 11:45:00', '2026-02-05 11:45:00'),
(4, 3, 'imaging', 'Siêu âm ổ bụng tổng quát', 'Đánh giá gan mật lách tụy', 'Có kết quả', 'Dạ dày có nhiều dịch, niêm mạc dày nhẹ. Gan mật bình thường.', '2026-02-05 11:45:00', '2026-02-05 11:45:00'),
(5, 4, 'lab', 'Đo chức năng hô hấp (Spirometry)', 'Đánh giá FEV1/FVC', 'Có kết quả', 'FEV1/FVC = 68% – tắc nghẽn nhẹ. Khuyến nghị dùng thuốc giãn phế quản khi cần', '2026-02-18 09:30:00', '2026-02-18 09:30:00'),
(6, 5, 'lab', 'Xét nghiệm acid uric máu', 'Đánh giá mức độ tăng acid uric', 'Có kết quả', 'Acid uric: 520 µmol/L – tăng (bình thường <420)', '2026-03-01 15:00:00', '2026-03-01 15:00:00'),
(7, 5, 'imaging', 'X-quang khớp bàn chân', 'Kiểm tra tổn thương khớp do Gout', 'Có kết quả', 'Hình ảnh đục vôi nhẹ quanh khớp ngón cái bàn chân phải.', '2026-03-01 15:00:00', '2026-03-01 15:00:00'),
(8, 7, 'imaging', 'MRI cột sống thắt lưng', 'Đánh giá thoát vị L4-L5', 'Có kết quả', 'Thoát vị đĩa đệm L4-L5 phải, chèn ép rễ thần kinh S1 nhẹ.', '2026-04-01 14:45:00', '2026-04-01 14:45:00'),
(9, 9, 'imaging', 'Siêu âm ổ bụng phải', 'Loại trừ viêm ruột thừa, sỏi mật', 'Có kết quả', 'Túi mật không có sỏi. Không có dấu hiệu viêm ruột thừa. Có nhẹ dịch vùng hố chậu phải.', '2026-04-23 14:15:00', '2026-04-23 14:15:00'),
(10, 11, 'lab', 'Xét nghiệm công thức máu', 'Đánh giá bạch cầu, phân biệt vi khuẩn/virus', 'Có kết quả', 'Bạch cầu: 11.5 G/L – tăng nhẹ, tỷ lệ Neutrophil 72% – gợi ý nhiễm khuẩn', '2026-05-07 15:10:00', '2026-05-07 15:10:00'),
(11, 13, 'lab', 'Xét nghiệm tổng phân tích máu', 'Đánh giá thiếu máu, điện giải', 'Có kết quả', 'Hb: 11.8 g/dL – thiếu máu nhẹ. Kali 3.4 mEq/L – giới hạn thấp. Đề xuất bổ sung sắt.', '2026-05-13 10:50:00', '2026-05-13 10:50:00')
SQL,
        ],
        [
            'table' => 'medical_records',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `medical_records` (`record_id`, `record_code`, `patient_id`, `patient_name`, `patient_code`, `doctor_id`, `doctor_name`, `appointment_id`, `exam_date`, `exam_time`, `visit_type`, `chief_complaint`, `status`, `status_note`, `created_at`, `updated_at`) VALUES
(1, 'CK-2026-0001', 22, 'Anh Tú Huỳnh', 'AN2026056285', 11, 'Nguyễn Thị Thu', 32, '2026-05-15', '11:30:00', 'Kham moi', 'gfgfgf', 'completed', NULL, '2026-05-14 03:18:03', '2026-05-14 03:18:03'),
(2, 'CK-2026-0002', 5, 'Trần Thị Mai', 'TR2026010001', 12, 'Đặng Thị Hằng', NULL, '2026-01-20', '09:00:00', 'Kham moi', 'Mệt mỏi kéo dài, khát nước nhiều, tiểu nhiều lần trong ngày.', 'completed', NULL, '2026-01-20 09:00:00', '2026-01-20 10:30:00'),
(3, 'CK-2026-0003', 6, 'Nguyễn Văn Bình', 'NG2026012001', 3, 'Nguyễn Thị Thu', NULL, '2026-02-05', '10:30:00', 'Tai kham', 'Đau thượng vị sau ăn, ợ chua, buồn nôn tái phát.', 'completed', NULL, '2026-02-05 10:30:00', '2026-02-05 11:45:00'),
(4, 'CK-2026-0004', 7, 'Lê Thị Hoa', 'LE2026015001', 5, 'Lê Thị Phương', NULL, '2026-02-18', '08:30:00', 'Kham moi', 'Ho khan, khó thở nhẹ khi gắng sức, tiền sử hen phế quản.', 'completed', NULL, '2026-02-18 08:30:00', '2026-02-18 09:30:00'),
(5, 'CK-2026-0005', 8, 'Phạm Minh Tuấn', 'PH2026018001', 10, 'Ngô Thị Bích', NULL, '2026-03-01', '14:00:00', 'Tai kham', 'Đau khớp ngón tay và ngón chân, sưng đỏ, uric acid cao.', 'completed', NULL, '2026-03-01 14:00:00', '2026-03-01 15:00:00'),
(6, 'CK-2026-0006', 9, 'Hoàng Thị Lan', 'HO2026020001', 11, 'Lý Văn Thành', NULL, '2026-03-15', '09:00:00', 'Kham moi', 'Ợ nóng sau bữa ăn, đau rát vùng ngực dưới, triệu chứng tái phát.', 'completed', NULL, '2026-03-15 09:00:00', '2026-03-15 10:15:00'),
(7, 'CK-2026-0007', 10, 'Đỗ Văn Hùng', 'DO2026022001', 10, 'Ngô Thị Bích', NULL, '2026-04-01', '13:30:00', 'Tai kham', 'Đau lưng dưới lan xuống chân phải, tê bì ngón chân, tiền sử thoát vị L4-L5.', 'completed', NULL, '2026-04-01 13:30:00', '2026-04-01 14:45:00'),
(8, 'CK-2026-0008', 1, 'aaa', 'AA2026042001', 3, 'Nguyễn Thị Thu', NULL, '2026-04-23', '08:00:00', 'Kham moi', 'Khám tổng quát định kỳ, không có triệu chứng đặc biệt.', 'completed', NULL, '2026-04-23 08:00:00', '2026-04-23 08:45:00'),
(9, 'CK-2026-0009', 2, 'bbb', 'BB2026042301', 4, 'Trần Văn Minh', NULL, '2026-04-23', '13:30:00', 'Kham moi', 'Đau bụng phải âm ỉ, đặc biệt sau khi ăn nhiều dầu mỡ.', 'completed', NULL, '2026-04-23 13:30:00', '2026-04-23 14:15:00'),
(10, 'CK-2026-0010', 3, 'aaa', 'AA2026042302', 8, 'Vũ Thị Ngọc', NULL, '2026-04-23', '09:00:00', 'Kham moi', 'Nghẹt mũi kéo dài 2 tuần, chảy nước mũi trong, hắt hơi nhiều.', 'completed', NULL, '2026-04-23 09:00:00', '2026-04-23 09:40:00'),
(11, 'CK-2026-0011', 4, 'bbb', 'BB2026050701', 5, 'Lê Thị Phương', 29, '2026-05-07', '14:30:00', 'Kham moi', 'Đau họng 3 ngày, sốt nhẹ 37.8°C, khó nuốt.', 'completed', NULL, '2026-05-07 14:30:00', '2026-05-07 15:10:00'),
(12, 'CK-2026-0012', 21, 'ádsad', 'AD2026050701', 7, 'Hoàng Văn Tùng', 31, '2026-05-07', '09:00:00', 'Kham moi', 'Mờ mắt khi nhìn xa, nhức đầu sau khi đọc sách.', 'completed', NULL, '2026-05-07 09:00:00', '2026-05-07 09:35:00'),
(13, 'CK-2026-0013', 24, 'Tú Huỳnh', 'TU2026051301', 3, 'Nguyễn Thị Thu', NULL, '2026-05-13', '10:00:00', 'Kham moi', 'Mệt mỏi, hoa mắt chóng mặt khi đứng dậy đột ngột.', 'completed', NULL, '2026-05-13 10:00:00', '2026-05-13 10:50:00')
SQL,
        ],
        [
            'table' => 'medicines',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `medicines` (`medicine_id`, `medicine_code`, `medicine_name`, `unit`, `unit_price`, `stock_quantity`, `min_stock`, `expiry_date`, `status`) VALUES
(1, 'MED001', 'Paracetamol 500mg', 'Viên', 500.00, 5000, 100, '2027-12-31', 1),
(2, 'MED002', 'Amoxicillin 500mg', 'Viên', 2500.00, 3000, 50, '2027-06-30', 1),
(3, 'MED003', 'Omeprazole 20mg', 'Viên', 3000.00, 2000, 50, '2027-08-31', 1),
(4, 'MED004', 'Metformin 500mg', 'Viên', 1500.00, 4000, 100, '2027-10-31', 1),
(5, 'MED005', 'Amlodipine 5mg', 'Viên', 2000.00, 3500, 50, '2027-09-30', 1),
(6, 'MED006', 'Atorvastatin 20mg', 'Viên', 5000.00, 2500, 50, '2027-11-30', 1),
(7, 'MED007', 'Vitamin C 1000mg', 'Viên', 800.00, 8000, 200, '2028-01-31', 1),
(8, 'MED008', 'Dextromethorphan syrup', 'Chai', 45000.00, 500, 20, '2027-05-31', 1),
(9, 'MED009', 'Ibuprofen 400mg', 'Viên', 1200.00, 4000, 100, '2027-07-31', 1),
(10, 'MED010', 'Cetirizine 10mg', 'Viên', 1800.00, 3000, 50, '2027-12-31', 1),
(11, 'MED011', 'Insulin Glargine 100U/ml', 'Lọ', 350000.00, 200, 10, '2027-03-31', 1),
(12, 'MED012', 'Losartan 50mg', 'Viên', 3500.00, 2800, 50, '2027-09-30', 1),
(13, 'MED013', 'Azithromycin 500mg', 'Viên', 8000.00, 1500, 30, '2027-06-30', 1),
(14, 'MED014', 'Prednisolone 5mg', 'Viên', 1000.00, 3000, 50, '2027-08-31', 1),
(15, 'MED015', 'Clindamycin gel 1%', 'Tuýp', 85000.00, 300, 20, '2027-04-30', 1)
SQL,
        ],
        [
            'table' => 'membershipcards',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `membershipcards` (`card_id`, `user_id`, `card_number`, `tier`, `points`, `total_spent`, `discount_pct`, `issue_date`, `expiry_date`, `status`) VALUES
(1, 5, 'MEM20260001', 'Bạc', 1500, 1500000.00, 5.00, '2026-01-10', '2027-01-10', 1),
(2, 6, 'MEM20260002', 'Vàng', 8500, 8500000.00, 10.00, '2026-01-12', '2027-01-12', 1),
(3, 7, 'MEM20260003', 'Thường', 350, 350000.00, 0.00, '2026-01-15', '2027-01-15', 1),
(4, 8, 'MEM20260004', 'Bạc', 2200, 2200000.00, 5.00, '2026-01-18', '2027-01-18', 1),
(5, 9, 'MEM20260005', 'Kim Cương', 25000, 25000000.00, 20.00, '2026-01-20', '2027-01-20', 1),
(6, 22, 'Chưa có thẻ', 'Thường', 0, 0.00, 0.00, '2026-05-08', NULL, 1),
(7, 23, 'MB-20260520-000023', 'Đồng', 0, 0.00, 0.00, '2026-05-20', '2027-05-20', 1)
SQL,
        ],
        [
            'table' => 'notifications',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `notifications` (`notification_id`, `user_id`, `notif_type`, `title`, `content`, `message`, `type`, `target_type`, `target_user_id`, `target_role`, `related_type`, `related_id`, `sender_id`, `action_url`, `ref_id`, `ref_type`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 07:34 ngày 23/04/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 1, 'appointment', 0, '2026-04-23 04:53:29', NULL),
(2, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 13:30 ngày 23/04/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 2, 'appointment', 0, '2026-04-23 07:09:56', NULL),
(3, 1, 'Lịch hẹn', 'Hủy lịch hẹn thành công', 'Lịch hẹn #2 đã được hủy.', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 2, 'appointment', 0, '2026-04-23 07:10:54', NULL),
(4, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:00 ngày 23/04/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 3, 'appointment', 0, '2026-04-23 07:12:49', NULL),
(5, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 08:00 ngày 23/04/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 4, 'appointment', 0, '2026-04-23 07:16:20', NULL),
(6, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 08:00 ngày 24/04/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 5, 'appointment', 0, '2026-04-23 07:19:22', NULL),
(7, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 13:30 ngày 23/04/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 6, 'appointment', 0, '2026-04-23 10:00:41', NULL),
(8, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 10:30 ngày 25/04/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 7, 'appointment', 0, '2026-04-23 10:08:06', NULL),
(9, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 08:00 ngày 23/04/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 8, 'appointment', 0, '2026-04-23 10:27:33', NULL),
(10, 2, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 13:30 ngày 23/04/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 10, 'appointment', 0, '2026-04-23 11:15:21', NULL),
(11, 2, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 15:00 ngày 23/04/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 12, 'appointment', 0, '2026-04-23 11:21:25', NULL),
(12, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 13:30 ngày 24/04/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 16, 'appointment', 0, '2026-04-23 13:23:49', NULL),
(13, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 08:00 ngày 24/04/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 17, 'appointment', 0, '2026-04-23 13:43:24', NULL),
(14, 3, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:00 ngày 23/04/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 18, 'appointment', 0, '2026-04-23 15:08:02', NULL),
(15, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:00 ngày 25/04/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 19, 'appointment', 0, '2026-04-23 15:17:18', NULL),
(16, 1, 'Lịch hẹn', 'Dời lịch hẹn thành công', 'Lịch hẹn #19 đã được dời sang 08:00 30/04/2026', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 19, 'appointment', 0, '2026-04-23 15:17:31', NULL),
(17, 2, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 14:00 ngày 24/04/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 20, 'appointment', 0, '2026-04-23 16:17:11', NULL),
(18, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 13:30 ngày 24/04/2026. Số thứ tự: #2', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 21, 'appointment', 0, '2026-04-23 16:18:17', NULL),
(19, 4, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:00 ngày 05/05/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 22, 'appointment', 0, '2026-05-05 12:29:13', NULL),
(20, 2, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 10:00 ngày 05/05/2026. Số thứ tự: #2', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 23, 'appointment', 0, '2026-05-05 12:53:24', NULL),
(21, 2, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:30 ngày 06/05/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 24, 'appointment', 0, '2026-05-06 03:51:39', NULL),
(22, 4, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:00 ngày 06/05/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 25, 'appointment', 0, '2026-05-06 05:52:01', NULL),
(23, 2, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:00 ngày 06/05/2026. Số thứ tự: #2', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 26, 'appointment', 0, '2026-05-06 05:52:16', NULL),
(24, 4, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:30 ngày 06/05/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 27, 'appointment', 0, '2026-05-06 14:17:21', NULL),
(25, 4, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 14:00 ngày 14/05/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 28, 'appointment', 0, '2026-05-06 14:59:48', NULL),
(26, 4, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 14:30 ngày 07/05/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 29, 'appointment', 0, '2026-05-07 05:46:13', NULL),
(27, 4, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:00 ngày 07/05/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 30, 'appointment', 0, '2026-05-07 16:39:03', NULL),
(28, 21, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:00 ngày 07/05/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 31, 'appointment', 0, '2026-05-07 16:55:17', NULL),
(29, 21, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 10:00 ngày 07/05/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 32, 'appointment', 0, '2026-05-07 17:00:23', NULL),
(30, 21, 'Lịch hẹn', 'Dời lịch hẹn thành công', 'Lịch hẹn #32 đã được dời sang 14:00 15/05/2026', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 32, 'appointment', 0, '2026-05-07 17:01:02', NULL),
(31, 22, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 08:30 ngày 08/05/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 33, 'appointment', 0, '2026-05-08 01:56:50', NULL),
(32, 23, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:30 ngày 13/05/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 34, 'appointment', 1, '2026-05-13 16:36:57', '2026-05-28 23:46:19'),
(33, 23, 'Lịch hẹn', 'Dời lịch hẹn thành công', 'Lịch hẹn #34 đã được dời sang 14:00 15/05/2026', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 34, 'appointment', 1, '2026-05-13 16:37:35', '2026-05-28 23:46:19'),
(34, 23, 'Lịch hẹn', 'Hủy lịch hẹn thành công', 'Lịch hẹn #34 đã được hủy.', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 34, 'appointment', 1, '2026-05-13 16:42:54', '2026-05-28 23:46:19'),
(35, 23, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 13:30 ngày 13/05/2026. Số thứ tự: #1', NULL, NULL, 'user', NULL, NULL, NULL, NULL, NULL, NULL, 35, 'appointment', 1, '2026-05-13 16:53:38', '2026-05-28 23:46:07'),
(36, 24, 'appointment_created', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 08:30 ngày 28/05/2026. Số thứ tự: #1', 'Lịch khám lúc 08:30 ngày 28/05/2026. Số thứ tự: #1', 'appointment_created', 'user', 24, NULL, 'appointment', 36, NULL, NULL, 36, 'appointment', 1, '2026-05-28 23:18:56', '2026-05-28 23:19:36'),
(37, 24, 'payment_created', 'Đã tạo yêu cầu thanh toán', 'Hóa đơn cho lịch khám #36 có số tiền 250.000đ đang chờ thanh toán.', 'Hóa đơn cho lịch khám #36 có số tiền 250.000đ đang chờ thanh toán.', 'payment_created', 'user', 24, NULL, 'payment', 2, NULL, NULL, 2, 'payment', 1, '2026-05-28 23:19:13', '2026-05-28 23:19:31'),
(38, 24, 'payment_paid', 'Thanh toán thành công', 'Giao dịch #2 đã được xác nhận thanh toán.', 'Giao dịch #2 đã được xác nhận thanh toán.', 'payment_paid', 'user', 24, NULL, 'payment', 2, NULL, NULL, 2, 'payment', 1, '2026-05-28 23:19:16', '2026-05-28 23:19:27'),
(39, NULL, 'hospital_news', 'Bản tin mới của bệnh viện', 'bệnh ế', 'bệnh ế', 'hospital_news', 'all', NULL, NULL, 'news', 17, 23, 'http://127.0.0.1:8000/news/17', 17, 'news', 0, '2026-05-28 23:53:03', '2026-05-28 23:53:03')
SQL,
        ],
        [
            'table' => 'notification_user',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `notification_user` (`id`, `notification_id`, `user_id`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 36, 24, '2026-05-28 23:42:25', '2026-05-28 16:18:56', '2026-05-28 16:42:25'),
(2, 37, 24, '2026-05-28 23:42:31', '2026-05-28 16:19:13', '2026-05-28 16:42:31'),
(3, 38, 24, '2026-05-28 23:45:39', '2026-05-28 16:19:16', '2026-05-28 16:45:39'),
(4, 35, 23, '2026-05-28 23:51:52', '2026-05-28 16:46:07', '2026-05-28 16:51:52'),
(5, 32, 23, '2026-05-28 23:46:52', '2026-05-28 16:46:19', '2026-05-28 16:46:52'),
(6, 33, 23, '2026-05-28 23:51:58', '2026-05-28 16:46:19', '2026-05-28 16:51:58'),
(7, 34, 23, '2026-05-28 23:46:19', '2026-05-28 16:46:19', '2026-05-28 16:46:19'),
(8, 39, 23, '2026-05-28 23:53:27', '2026-05-28 16:53:12', '2026-05-28 16:53:27'),
(9, 39, 24, '2026-05-28 23:54:06', '2026-05-28 16:54:06', '2026-05-28 16:54:06')
SQL,
        ],
        [
            'table' => 'nutrition_articles',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `nutrition_articles` (`article_id`, `doctor_id`, `title`, `slug`, `content`, `target_disease`, `status`, `created_at`, `updated_at`) VALUES
(1, 11, 'Chế độ ăn cho người tiểu đường', 'che-do-an-cho-nguoi-tieu-duong', 'Người bệnh tiểu đường nên hạn chế đường, tăng cường rau xanh, ngũ cốc nguyên cám và thực phẩm giàu chất xơ.', 'Tiểu đường', 1, '2026-05-28 21:23:01', '2026-05-28 21:23:01'),
(2, 11, 'Dinh dưỡng cho người cao huyết áp', 'dinh-duong-cho-nguoi-cao-huyet-ap', 'Người cao huyết áp nên giảm muối, hạn chế thực phẩm nhiều dầu mỡ và ăn nhiều trái cây.', 'Cao huyết áp', 1, '2026-05-28 21:23:01', '2026-05-28 21:23:01'),
(3, 11, 'Thực phẩm tốt cho tim mạch', 'thuc-pham-tot-cho-tim-mach', 'Các loại cá béo, rau xanh và hạt dinh dưỡng rất tốt cho sức khỏe tim mạch.', 'Tim mạch', 1, '2026-05-28 21:23:01', '2026-05-28 21:23:01'),
(4, NULL, '5 nguyên tắc ăn uống lành mạnh', '5-nguyen-tac-an-uong-lanh-manh', 'Ăn đủ chất, uống đủ nước, hạn chế thức ăn nhanh và duy trì vận động hàng ngày.', NULL, 1, '2026-05-28 21:23:01', '2026-05-28 21:23:01')
SQL,
        ],
        [
            'table' => 'patientallergies',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `patientallergies` (`allergy_id`, `user_id`, `allergen`, `reaction`, `severity`, `noted_date`, `notes`) VALUES
(1, 5, 'Penicillin', 'Phát ban, ngứa toàn thân', 'Nặng', '2020-03-10', 'Dị ứng kháng sinh nhóm beta-lactam'),
(2, 6, 'Tôm', 'Nổi mề đay, khó thở', 'Nặng', '2018-07-15', 'Dị ứng hải sản'),
(3, 7, 'Aspirin', 'Đau dạ dày, xuất huyết nhẹ', 'Vừa', '2022-11-20', 'Không dùng NSAID'),
(4, 8, 'Sulfonamide', 'Sốc phản vệ nhẹ', 'Nặng', '2019-05-05', 'Cần báo bác sĩ trước khi dùng thuốc'),
(5, 9, 'Phấn hoa', 'Chảy nước mắt, hắt hơi', 'Nhẹ', '2021-04-01', 'Dị ứng theo mùa'),
(6, 10, 'Mủ latex', 'Ngứa, sưng đỏ da', 'Vừa', '2017-08-22', 'Thông báo cho phòng phẫu thuật'),
(7, 1, 'Không có dị ứng đã biết', 'Không có', 'Không xác định', '2026-04-23', 'Khám tổng quát, không ghi nhận dị ứng'),
(8, 2, 'Cá biển', 'Nổi mề đay, ngứa', 'Vừa', '2026-04-23', 'Dị ứng hải sản, tránh các loại cá biển'),
(9, 3, 'Không có dị ứng đã biết', 'Không có', 'Không xác định', '2026-04-23', NULL),
(10, 4, 'Penicillin', 'Phát ban da', 'Nhẹ', '2026-05-07', 'Dị ứng nhẹ với kháng sinh nhóm beta-lactam'),
(11, 21, 'Bụi nhà', 'Hắt hơi, ngứa mắt', 'Nhẹ', '2026-05-07', 'Dị ứng bụi mãn tính, đặc biệt buổi sáng'),
(12, 22, 'Không có dị ứng đã biết', 'Không có', 'Không xác định', '2026-05-08', NULL),
(13, 23, 'Không có dị ứng đã biết', 'Không có', 'Không xác định', '2026-05-13', NULL),
(14, 24, 'Sulfonamide', 'Ngứa da, phát ban', 'Vừa', '2026-05-13', 'Cần báo bác sĩ khi được kê kháng sinh')
SQL,
        ],
        [
            'table' => 'patientmedicalhistory',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `patientmedicalhistory` (`history_id`, `user_id`, `condition`, `diagnosed_at`, `treated_at`, `is_chronic`, `notes`) VALUES
(1, 5, 'Tăng huyết áp', '2020-01-15', 'Bệnh viện Chợ Rẫy', 1, 'Đang dùng Amlodipine 5mg/ngày'),
(2, 5, 'Đái tháo đường type 2', '2021-06-10', 'Bệnh viện Đa khoa Trung tâm', 1, 'HbA1c 7.2%, kiểm soát bằng Metformin'),
(3, 6, 'Viêm dạ dày mạn', '2019-03-20', 'Phòng khám tư', 1, 'H. pylori dương tính, đã điều trị'),
(4, 7, 'Hen phế quản', '2015-09-05', 'Bệnh viện Nhi Đồng', 1, 'Dùng salbutamol khi cần'),
(5, 8, 'Gout', '2022-04-18', 'Bệnh viện Thống Nhất', 1, 'Uric acid cao, chế độ ăn kiêng'),
(6, 9, 'Trào ngược dạ dày thực quản', '2023-01-10', 'Phòng khám nội khoa', 0, 'Điều trị khỏi sau 3 tháng'),
(7, 10, 'Cột sống thắt lưng L4-L5', '2018-11-30', 'Bệnh viện Chấn Thương', 1, 'Thoát vị đĩa đệm, đang vật lý trị liệu'),
(8, 1, 'Không có tiền sử bệnh đặc biệt', '2026-04-23', NULL, 0, 'Sức khỏe tổng thể tốt'),
(9, 2, 'Đau túi mật chức năng', '2025-03-10', 'Phòng khám đa khoa tư', 0, 'Đã điều trị, không tái phát'),
(10, 3, 'Viêm mũi dị ứng mãn tính', '2020-06-01', 'Bệnh viện Tai Mũi Họng TP.HCM', 1, 'Điều trị theo mùa, dùng Cetirizine khi cần'),
(11, 4, 'Viêm họng tái phát', '2024-01-15', 'Phòng khám nhi khoa', 0, 'Tái phát khoảng 2-3 lần/năm'),
(12, 21, 'Cận thị tiến triển', '2022-09-01', 'Trung tâm mắt Sài Gòn', 1, 'Độ cận tăng hàng năm, đang theo dõi'),
(13, 22, 'Khô mắt do công nghệ', '2025-11-20', 'Phòng khám nhãn khoa', 0, 'Liên quan đến thói quen dùng màn hình nhiều giờ'),
(14, 23, 'Loạn thị nhẹ', '2026-05-13', 'Bệnh viện Đa khoa Trung tâm', 0, 'Phát hiện lần đầu, chưa điều trị'),
(15, 24, 'Thiếu máu nhẹ (thiếu sắt)', '2026-05-13', 'Bệnh viện Đa khoa Trung tâm', 0, 'Hb 11.8 g/dL, đang bổ sung sắt')
SQL,
        ],
        [
            'table' => 'paymentitems',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `paymentitems` (`item_id`, `payment_id`, `item_type`, `item_name`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 1, 'Khám bệnh', 'Phí khám - BS. Hoàng Văn Tùng', 1, 280000.00, 280000.00),
(2, 1, 'Dịch vụ', 'Điện tâm đồ (ECG)', 1, 200000.00, 200000.00),
(3, 2, 'Khám bệnh', 'Phí khám - BS. Nguyễn Thị Thu', 1, 250000.00, 250000.00)
SQL,
        ],
        [
            'table' => 'payments',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `payments` (`payment_id`, `appointment_id`, `subtotal`, `insurance_id`, `discount_amount`, `membership_id`, `total_amount`, `status`, `method`, `payment_method`, `payment_date`, `transaction_ref`, `notes`, `created_at`, `updated_at`) VALUES
(1, 35, 480000.00, NULL, 0.00, NULL, 480000.00, 'Thành công', 'QR', NULL, '2026-05-20 13:39:46', 'PAY-26ZHARW6KR', NULL, NULL, NULL),
(2, 36, 250000.00, NULL, 0.00, NULL, 250000.00, 'Thành công', 'QR', NULL, '2026-05-28 23:19:16', 'PAY-Z8PRH1TOWH', NULL, NULL, NULL)
SQL,
        ],
        [
            'table' => 'prescriptions',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `prescriptions` (`prescription_id`, `record_id`, `drug_name`, `dosage`, `instructions`, `duration_days`, `quantity`, `unit`, `created_at`, `updated_at`) VALUES
(1, 2, 'Metformin 500mg', '500mg', 'Uống 1 viên sau bữa ăn sáng và tối', 30, 60, 'viên', '2026-01-20 10:30:00', '2026-01-20 10:30:00'),
(2, 2, 'Amlodipine 5mg', '5mg', 'Uống 1 viên vào buổi sáng', 30, 30, 'viên', '2026-01-20 10:30:00', '2026-01-20 10:30:00'),
(3, 2, 'Vitamin C 1000mg', '1000mg', 'Uống 1 viên sau bữa ăn', 30, 30, 'viên', '2026-01-20 10:30:00', '2026-01-20 10:30:00'),
(4, 3, 'Omeprazole 20mg', '20mg', 'Uống 1 viên trước bữa ăn sáng 30 phút', 28, 28, 'viên', '2026-02-05 11:45:00', '2026-02-05 11:45:00'),
(5, 3, 'Azithromycin 500mg', '500mg', 'Uống 1 viên/ngày x 5 ngày', 5, 5, 'viên', '2026-02-05 11:45:00', '2026-02-05 11:45:00'),
(6, 4, 'Prednisolone 5mg', '5mg', 'Uống 2 viên buổi sáng x 7 ngày, giảm liều dần', 14, 20, 'viên', '2026-02-18 09:30:00', '2026-02-18 09:30:00'),
(7, 4, 'Cetirizine 10mg', '10mg', 'Uống 1 viên vào buổi tối', 7, 7, 'viên', '2026-02-18 09:30:00', '2026-02-18 09:30:00'),
(8, 5, 'Ibuprofen 400mg', '400mg', 'Uống 1 viên sau ăn khi đau, không quá 3 viên/ngày', 14, 30, 'viên', '2026-03-01 15:00:00', '2026-03-01 15:00:00'),
(9, 5, 'Paracetamol 500mg', '500mg', 'Uống 1 viên khi đau, cách nhau ít nhất 6 giờ', 14, 20, 'viên', '2026-03-01 15:00:00', '2026-03-01 15:00:00'),
(10, 6, 'Omeprazole 20mg', '20mg', 'Uống 1 viên trước bữa ăn sáng 30 phút', 14, 14, 'viên', '2026-03-15 10:15:00', '2026-03-15 10:15:00'),
(11, 7, 'Ibuprofen 400mg', '400mg', 'Uống 1 viên sau ăn, ngày 2 lần', 14, 28, 'viên', '2026-04-01 14:45:00', '2026-04-01 14:45:00'),
(12, 7, 'Prednisolone 5mg', '5mg', 'Uống 1 viên buổi sáng x 5 ngày', 5, 5, 'viên', '2026-04-01 14:45:00', '2026-04-01 14:45:00'),
(13, 8, 'Vitamin C 1000mg', '1000mg', 'Uống 1 viên/ngày sau bữa ăn', 30, 30, 'viên', '2026-04-23 08:45:00', '2026-04-23 08:45:00'),
(14, 9, 'Omeprazole 20mg', '20mg', 'Uống 1 viên trước bữa sáng', 14, 14, 'viên', '2026-04-23 14:15:00', '2026-04-23 14:15:00'),
(15, 9, 'Paracetamol 500mg', '500mg', 'Uống khi đau, tối đa 3 viên/ngày', 7, 15, 'viên', '2026-04-23 14:15:00', '2026-04-23 14:15:00'),
(16, 10, 'Cetirizine 10mg', '10mg', 'Uống 1 viên/ngày vào buổi tối', 14, 14, 'viên', '2026-04-23 09:40:00', '2026-04-23 09:40:00'),
(17, 11, 'Amoxicillin 500mg', '500mg', 'Uống 1 viên x 3 lần/ngày x 7 ngày', 7, 21, 'viên', '2026-05-07 15:10:00', '2026-05-07 15:10:00'),
(18, 11, 'Paracetamol 500mg', '500mg', 'Uống khi sốt trên 38.5°C', 5, 10, 'viên', '2026-05-07 15:10:00', '2026-05-07 15:10:00'),
(19, 11, 'Vitamin C 1000mg', '1000mg', 'Uống 1 viên/ngày sau ăn', 14, 14, 'viên', '2026-05-07 15:10:00', '2026-05-07 15:10:00'),
(20, 12, 'Nước mắt nhân tạo Hypromellose 0.3%', '1-2 giọt/lần', 'Nhỏ 3-4 lần/ngày vào mỗi mắt', 30, 2, 'lọ', '2026-05-07 09:35:00', '2026-05-07 09:35:00'),
(21, 13, 'Paracetamol 500mg', '500mg', 'Uống khi đau đầu chóng mặt', 7, 10, 'viên', '2026-05-13 10:50:00', '2026-05-13 10:50:00'),
(22, 13, 'Vitamin C 1000mg', '1000mg', 'Uống 1 viên/ngày sau ăn x 30 ngày', 30, 30, 'viên', '2026-05-13 10:50:00', '2026-05-13 10:50:00')
SQL,
        ],
        [
            'table' => 'queue_tickets',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `queue_tickets` (`ticket_id`, `appointment_id`, `schedule_id`, `user_id`, `patient_name`, `patient_phone`, `patient_email`, `queue_date`, `queue_number`, `priority`, `status`, `priority_sort`, `checkin_time`, `called_at`, `started_at`, `completed_at`, `est_wait_minutes`, `notes`, `served_by`, `created_at`, `updated_at`) VALUES
(1, 36, 338, 24, 'Tú Huỳnh', '1234567890', 'tuh225095@gmail.com', '2026-05-28', 1, 'normal', 'waiting', 4, '2026-05-28 23:18:56', NULL, NULL, NULL, 15, NULL, NULL, '2026-05-28 16:18:56', '2026-05-28 16:18:57')
SQL,
        ],
        [
            'table' => 'record_allergies',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `record_allergies` (`id`, `record_id`, `allergen`, `severity`, `reaction`, `created_at`, `updated_at`) VALUES
(1, 2, 'Penicillin', 'Nặng', 'Phát ban toàn thân, ngứa', '2026-01-20 09:00:00', '2026-01-20 09:00:00'),
(2, 3, 'Tôm cua', 'Nặng', 'Nổi mề đay, khó thở', '2026-02-05 10:30:00', '2026-02-05 10:30:00'),
(3, 4, 'Aspirin', 'Vừa', 'Đau dạ dày, xuất huyết nhẹ', '2026-02-18 08:30:00', '2026-02-18 08:30:00'),
(4, 5, 'Sulfonamide', 'Nặng', 'Sốc phản vệ nhẹ', '2026-03-01 14:00:00', '2026-03-01 14:00:00'),
(5, 11, 'Penicillin', 'Nhẹ', 'Phát ban da nhẹ', '2026-05-07 14:30:00', '2026-05-07 14:30:00')
SQL,
        ],
        [
            'table' => 'reviews',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `reviews` (`review_id`, `appointment_id`, `user_id`, `doctor_id`, `rating`, `comment`, `doctor_reply`, `created_at`, `doctor_reply_updated_at`, `updated_at`) VALUES
(1, 1, 1, 1, 5, 'oke', 'oke', '2026-04-23 12:19:37', NULL, NULL),
(2, 25, 4, 2, 5, 'Bác sĩ rất tận tâm, giải thích rõ ràng từng bước điều trị.', NULL, '2026-05-06 14:00:00', NULL, NULL),
(3, 26, 2, 2, 4, 'Khám nhanh, tuy nhiên phải chờ hơi lâu.', NULL, '2026-05-06 15:00:00', NULL, NULL),
(6, 30, 4, 7, 4, 'bình luận mới 13123123123', 'oke', '2026-05-07 16:41:18', NULL, NULL),
(5, 29, 4, 5, 5, 'ádasdluậoadoaksjldjasd', 'ok', '2026-05-07 05:46:42', NULL, NULL),
(7, 31, 21, 10, 5, 'sfsdf', NULL, '2026-05-07 16:56:06', '2026-05-07 00:37:31', NULL),
(8, 32, 21, 6, 5, 'ânjnjana 111111111', NULL, '2026-05-07 17:01:56', NULL, '2026-05-07 17:26:28'),
(9, 28, 4, 11, 5, 'asdasd', NULL, '2026-05-07 17:10:47', NULL, NULL),
(10, 33, 22, 7, 5, 'dddddwaa', NULL, '2026-05-08 01:57:45', NULL, '2026-05-08 03:11:52')
SQL,
        ],
        [
            'table' => 'roles',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'Admin'),
(2, 'Bác sĩ'),
(3, 'Bệnh nhân'),
(4, 'Lễ tân'),
(5, 'Dược sĩ')
SQL,
        ],
        [
            'table' => 'rooms',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `rooms` (`room_id`, `room_code`, `room_name`, `department_id`, `room_type`, `status`, `notes`) VALUES
(1, 'P101', 'Phòng khám Tim Mạch 1', 1, 'Khám bệnh', 'Hoạt động', NULL),
(2, 'P102', 'Phòng khám Phụ Sản 1', 2, 'Khám bệnh', 'Hoạt động', NULL),
(3, 'P201', 'Phòng khám Nội Tổng Quát', 3, 'Khám bệnh', 'Hoạt động', NULL),
(4, 'P202', 'Phòng khám Ngoại Tổng Quát', 4, 'Khám bệnh', 'Hoạt động', NULL),
(5, 'P301', 'Phòng khám Nhi Khoa', 5, 'Khám bệnh', 'Hoạt động', NULL),
(6, 'P302', 'Phòng khám Da Liễu', 6, 'Khám bệnh', 'Hoạt động', NULL),
(7, 'P401', 'Phòng khám Mắt', 7, 'Khám bệnh', 'Hoạt động', NULL),
(8, 'P402', 'Phòng khám Tai Mũi Họng', 8, 'Khám bệnh', 'Hoạt động', NULL),
(9, 'P501', 'Phòng khám Thần Kinh', 9, 'Khám bệnh', 'Hoạt động', NULL),
(10, 'P502', 'Phòng khám Cơ Xương Khớp', 10, 'Khám bệnh', 'Hoạt động', NULL),
(11, 'P601', 'Phòng khám Tiêu Hóa', 11, 'Khám bệnh', 'Hoạt động', NULL),
(12, 'P602', 'Phòng khám Nội Tiết', 12, 'Khám bệnh', 'Hoạt động', NULL)
SQL,
        ],
        [
            'table' => 'rehab_exercises',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `rehab_exercises` (`id`, `title`, `content`, `category`, `phase`, `thumbnail`, `status`, `view_count`, `duration_minutes`, `created_by`, `created_at`, `updated_at`) VALUES
(101, 'Co gian khop goi', 'Nguoi benh nam thoai mai tren mat phang cung Co chan nhe nha giu lung thang Thuc hien dong tac cham rai va dung lai khi thay cang vua phai Bai tap giup tang do linh hoat cua khop goi va giam cam giac cung khop sau khi nghi lau', 'co-xuong-khop', 'phuc-hoi', NULL, 'published', 18, 12, 23, '2026-06-01 08:00:00', '2026-06-01 08:00:00'),
(102, 'Nang chan thang', 'Nguoi benh nam ngua mot chan co mot chan duoi thang Nang chan thang len cham rai giu trong thoi gian ngan roi ha xuong Tu the nay ho tro tang suc manh co dui va on dinh khop goi trong giai doan phuc hoi', 'co-xuong-khop', 'phuc-hoi', NULL, 'published', 12, 10, 23, '2026-06-01 08:05:00', '2026-06-01 08:05:00'),
(103, 'Xoay co chan nhe', 'Nguoi benh ngoi tren ghe giu lung thang Dat got chan cham san va xoay co chan theo vong tron nho Thuc hien cham rai de giup tang tuan hoan va giam cung co sau chan thuong nhe', 'chan-thuong-the-thao', 'cap-tinh', NULL, 'published', 9, 8, 23, '2026-06-01 08:10:00', '2026-06-01 08:10:00'),
(104, 'Tap giu thang bang', 'Nguoi benh dung gan tay vin de dam bao an toan Dung mot chan trong thoi gian ngan sau do doi ben Bai tap giup cai thien kha nang giu thang bang va phoi hop van dong sau chan thuong the thao', 'chan-thuong-the-thao', 'duy-tri', NULL, 'draft', 3, 15, 23, '2026-06-01 08:15:00', '2026-06-01 08:15:00'),
(105, 'Tap tho bang bung', 'Nguoi benh nam hoac ngoi thoai mai Dat tay len bung Hit vao cham bang mui de bung phong len sau do tho ra cham bang mieng Bai tap ho tro kiem soat nhip tho va tang hieu qua thong khi', 'ho-hap-tim-mach', 'phuc-hoi', NULL, 'published', 20, 10, 23, '2026-06-01 08:20:00', '2026-06-01 08:20:00'),
(106, 'Di bo cham tai cho', 'Nguoi benh dung thang nguoi gan diem tua Buoc tai cho voi nhip cham va deu Ket hop hit tho nhe nha Bai tap giup tang suc ben tim mach va duy tri kha nang van dong hang ngay', 'ho-hap-tim-mach', 'duy-tri', NULL, 'published', 15, 15, 23, '2026-06-01 08:25:00', '2026-06-01 08:25:00'),
(107, 'Tap nam mo ban tay', 'Nguoi benh dat tay tren ban Mo cac ngon tay cham rai roi nam ban tay nhe nha Lap lai theo kha nang Bai tap ho tro phuc hoi van dong ban tay sau dot quy hoac yeu co than kinh', 'than-kinh-dot-quy', 'phuc-hoi', NULL, 'published', 11, 10, 23, '2026-06-01 08:30:00', '2026-06-01 08:30:00'),
(108, 'Tap nang canh tay', 'Nguoi benh ngoi thang lung Nang canh tay len cham rai trong tam van dong thoai mai roi ha xuong Bai tap giup cai thien kiem soat van dong vai va tay trong qua trinh phuc hoi than kinh', 'than-kinh-dot-quy', 'phuc-hoi', NULL, 'draft', 4, 12, 23, '2026-06-01 08:35:00', '2026-06-01 08:35:00')
SQL,
        ],
        [
            'table' => 'serviceprices',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `serviceprices` (`price_id`, `service_id`, `price_type`, `price`, `effective_date`, `end_date`, `created_by`, `created_at`) VALUES
(1, 1, 'Thường', 150000.00, '2025-01-01', NULL, NULL, '2026-05-06 21:19:13'),
(2, 2, 'Thường', 200000.00, '2025-01-01', NULL, NULL, '2026-05-06 21:19:13'),
(3, 3, 'Thường', 450000.00, '2025-01-01', NULL, NULL, '2026-05-06 21:19:13'),
(4, 4, 'Thường', 300000.00, '2025-01-01', NULL, NULL, '2026-05-06 21:19:13'),
(5, 5, 'Thường', 250000.00, '2025-01-01', NULL, NULL, '2026-05-06 21:19:13'),
(6, 6, 'Thường', 180000.00, '2025-01-01', NULL, NULL, '2026-05-06 21:19:13'),
(7, 7, 'Thường', 120000.00, '2025-01-01', NULL, NULL, '2026-05-06 21:19:13'),
(8, 8, 'Thường', 800000.00, '2025-01-01', NULL, NULL, '2026-05-06 21:19:13'),
(9, 9, 'Thường', 200000.00, '2025-01-01', NULL, NULL, '2026-05-06 21:19:13'),
(10, 10, 'Thường', 1200000.00, '2025-01-01', NULL, NULL, '2026-05-06 21:19:13'),
(11, 11, 'Thường', 150000.00, '2025-01-01', NULL, NULL, '2026-05-06 21:19:13'),
(12, 12, 'Thường', 250000.00, '2025-01-01', NULL, NULL, '2026-05-06 21:19:13'),
(13, 13, 'Thường', 3500000.00, '2025-01-01', NULL, NULL, '2026-05-06 21:19:13'),
(14, 14, 'Thường', 200000.00, '2025-01-01', NULL, NULL, '2026-05-06 21:19:13'),
(15, 15, 'Thường', 1500000.00, '2025-01-01', NULL, NULL, '2026-05-06 21:19:13'),
(16, 16, 'Thường', 180000.00, '2025-01-01', NULL, NULL, '2026-05-06 21:19:13'),
(17, 2, 'BHYT', 60000.00, '2025-01-01', NULL, NULL, '2026-05-06 21:19:13'),
(18, 6, 'BHYT', 50000.00, '2025-01-01', NULL, NULL, '2026-05-06 21:19:13'),
(19, 7, 'BHYT', 40000.00, '2025-01-01', NULL, NULL, '2026-05-06 21:19:13')
SQL,
        ],
        [
            'table' => 'services',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `services` (`service_id`, `service_code`, `service_name`, `department_id`, `description`, `duration_minutes`, `base_price`, `status`) VALUES
(1, '1', 'aa', 1, 'aa', 30, 0.00, 1),
(2, 'TM01', 'Điện tâm đồ (ECG)', 1, 'Ghi lại hoạt động điện của tim', 20, 0.00, 1),
(3, 'TM02', 'Siêu âm tim', 1, 'Đánh giá cấu trúc và chức năng tim', 30, 0.00, 1),
(4, 'PS01', 'Siêu âm thai', 2, 'Theo dõi sự phát triển thai nhi', 30, 0.00, 1),
(5, 'PS02', 'Xét nghiệm Pap smear', 2, 'Tầm soát ung thư cổ tử cung', 15, 0.00, 1),
(6, 'NT01', 'Xét nghiệm máu tổng quát', 3, 'CBC, sinh hoá máu cơ bản', 15, 0.00, 1),
(7, 'NT02', 'Chụp X-quang ngực', 3, 'Đánh giá phổi và tim', 15, 0.00, 1),
(8, 'NG01', 'Nội soi dạ dày', 4, 'Chẩn đoán bệnh lý dạ dày', 30, 0.00, 1),
(9, 'NHI01', 'Khám sức khoẻ trẻ em', 5, 'Kiểm tra tổng quát và tiêm chủng', 30, 0.00, 1),
(10, 'DL01', 'Điều trị mụn laser', 6, 'Laser điều trị mụn và sẹo', 45, 0.00, 1),
(11, 'MAT01', 'Đo khúc xạ mắt', 7, 'Kiểm tra thị lực và độ cận/viễn', 20, 0.00, 1),
(12, 'TMH01', 'Nội soi tai mũi họng', 8, 'Chẩn đoán bệnh lý TMH', 20, 0.00, 1),
(13, 'TK01', 'Chụp MRI não', 9, 'Chẩn đoán hình ảnh bệnh lý thần kinh', 45, 0.00, 1),
(14, 'CXK01', 'Chụp X-quang xương khớp', 10, 'Đánh giá tổn thương xương và khớp', 15, 0.00, 1),
(15, 'TH01', 'Nội soi đại tràng', 11, 'Tầm soát ung thư đại trực tràng', 45, 0.00, 1),
(16, 'NOI01', 'Xét nghiệm đường huyết HbA1c', 12, 'Kiểm soát đái tháo đường dài hạn', 15, 0.00, 1)
SQL,
        ],
        [
            'table' => 'tiensu',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `tiensu` (`id`, `user_id`, `blood_group`, `yeuto_rh`, `height`, `weight`, `bmi`, `food_allergies`, `drug_allergies`, `chronic_diseases`, `other_chronic_diseases`, `created_at`, `updated_at`) VALUES
(1, 3, 'O-', 'positive', 176, 43, 13.88, 'aaa', 'aaa', '[\"TĂNG HUYẾT ÁP\"]', NULL, '2026-04-23 06:49:56', '2026-04-23 06:49:56')
SQL,
        ],
        [
            'table' => 'users',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `users` (`user_id`, `full_name`, `email`, `password`, `phone`, `address`, `date_of_birth`, `gender`, `role_id`, `avatar_url`, `status`, `created_at`) VALUES
(1, 'aaa', 'a@gmail.com', '$2y$12$wa5hS.P1b..AgnGHh2RkZOsorAyjQBkG14cX4P.yW3sIoJ6mnZ7VC', '0913841867', '123 Nguyen Trai', '2026-04-09', 'Nam', 3, NULL, 1, '2026-04-23 10:54:20'),
(2, 'bbb', 'a123123333@gmail.com', '$2y$12$bz76leBESjJ7e4IfxXlSzeVtdzqz/ktnVWUTs7IFHTXU7wAGcx8ya', '0948075521', 'ádadsasd', '2026-04-22', 'Nam', 3, NULL, 1, '2026-04-23 17:28:25'),
(3, 'aaa', 'admin@email.com', '$2y$12$9Fght5RVAOkTE0j.xvpGSObxxCS.40Sjg4niryyx.M9S5bznL5IsS', '0913841867', '123 Nguyen Trai', '2026-04-10', 'Khác', 1, NULL, 1, '2026-04-23 20:43:54'),
(4, 'bbb', 'aa@gmail.com', '$2y$12$oeYkxkXeNU8LuPiNK7wMR.TBPQR0my0AoWPrNLwP.PDL9MEVcKTSK', '0948075521', 'ádadsasd', '2026-04-22', 'Nam', 3, NULL, 1, '2026-05-05 19:28:59'),
(5, 'Trần Thị Mai', 'mai.tran@gmail.com', '$2y$12$wa5hS.P1b..AgnGHh2RkZOsorAyjQBkG14cX4P.yW3sIoJ6mnZ7VC', '0901234567', '45 Lê Lợi, Q.1, TP.HCM', '1990-03-15', 'Nữ', 3, NULL, 1, '2026-01-10 08:00:00'),
(6, 'Nguyễn Văn Bình', 'binh.nguyen@gmail.com', '$2y$12$wa5hS.P1b..AgnGHh2RkZOsorAyjQBkG14cX4P.yW3sIoJ6mnZ7VC', '0912345678', '12 Trần Hưng Đạo, Q.5, TP.HCM', '1985-07-22', 'Nam', 3, NULL, 1, '2026-01-12 09:00:00'),
(7, 'Lê Thị Hoa', 'hoa.le@gmail.com', '$2y$12$wa5hS.P1b..AgnGHh2RkZOsorAyjQBkG14cX4P.yW3sIoJ6mnZ7VC', '0923456789', '78 Nguyễn Huệ, Q.1, TP.HCM', '1995-11-08', 'Nữ', 3, NULL, 1, '2026-01-15 10:00:00'),
(8, 'Phạm Minh Tuấn', 'tuan.pham@gmail.com', '$2y$12$wa5hS.P1b..AgnGHh2RkZOsorAyjQBkG14cX4P.yW3sIoJ6mnZ7VC', '0934567890', '99 Đinh Tiên Hoàng, Bình Thạnh', '1988-05-30', 'Nam', 3, NULL, 1, '2026-01-18 11:00:00'),
(9, 'Hoàng Thị Lan', 'lan.hoang@gmail.com', '$2y$12$wa5hS.P1b..AgnGHh2RkZOsorAyjQBkG14cX4P.yW3sIoJ6mnZ7VC', '0945678901', '23 Võ Thị Sáu, Q.3, TP.HCM', '1992-09-14', 'Nữ', 3, NULL, 1, '2026-01-20 12:00:00'),
(10, 'Đỗ Văn Hùng', 'hung.do@gmail.com', '$2y$12$wa5hS.P1b..AgnGHh2RkZOsorAyjQBkG14cX4P.yW3sIoJ6mnZ7VC', '0956789012', '56 Cách Mạng Tháng 8, Q.10', '1980-12-01', 'Nam', 3, NULL, 1, '2026-01-22 13:00:00'),
(11, 'BS. Nguyễn Thị Thu', 'thu.bs@hospital.vn', '$2y$12$PaWG6274qzVi7NurfiF2feEuEyDBD/HEtsAqyQkeIpLx.Gbo2rzOy', '0901111111', 'Bệnh viện Đa khoa Trung tâm', '1975-04-20', 'Nữ', 2, NULL, 1, '2025-06-01 07:00:00'),
(12, 'BS. Trần Văn Minh', 'minh.bs@hospital.vn', '$2y$12$wa5hS.P1b..AgnGHh2RkZOsorAyjQBkG14cX4P.yW3sIoJ6mnZ7VC', '0902222222', 'Bệnh viện Đa khoa Trung tâm', '1972-08-15', 'Nam', 2, NULL, 1, '2025-06-01 07:00:00'),
(13, 'BS. Lê Thị Phương', 'phuong.bs@hospital.vn', '$2y$12$wa5hS.P1b..AgnGHh2RkZOsorAyjQBkG14cX4P.yW3sIoJ6mnZ7VC', '0903333333', 'Bệnh viện Đa khoa Trung tâm', '1978-02-10', 'Nữ', 2, NULL, 1, '2025-06-01 07:00:00'),
(14, 'BS. Phạm Đức Long', 'long.bs@hospital.vn', '$2y$12$wa5hS.P1b..AgnGHh2RkZOsorAyjQBkG14cX4P.yW3sIoJ6mnZ7VC', '0904444444', 'Bệnh viện Đa khoa Trung tâm', '1980-06-25', 'Nam', 2, NULL, 1, '2025-06-01 07:00:00'),
(15, 'BS. Hoàng Văn Tùng', 'tung.bs@hospital.vn', '$2y$12$wa5hS.P1b..AgnGHh2RkZOsorAyjQBkG14cX4P.yW3sIoJ6mnZ7VC', '0905555555', 'Bệnh viện Đa khoa Trung tâm', '1976-11-30', 'Nam', 2, NULL, 1, '2025-06-01 07:00:00'),
(16, 'BS. Vũ Thị Ngọc', 'ngoc.bs@hospital.vn', '$2y$12$wa5hS.P1b..AgnGHh2RkZOsorAyjQBkG14cX4P.yW3sIoJ6mnZ7VC', '0906666666', 'Bệnh viện Đa khoa Trung tâm', '1983-03-18', 'Nữ', 2, NULL, 1, '2025-06-01 07:00:00'),
(17, 'BS. Đinh Quốc Huy', 'huy.bs@hospital.vn', '$2y$12$wa5hS.P1b..AgnGHh2RkZOsorAyjQBkG14cX4P.yW3sIoJ6mnZ7VC', '0907777777', 'Bệnh viện Đa khoa Trung tâm', '1970-09-05', 'Nam', 2, NULL, 1, '2025-06-01 07:00:00'),
(18, 'BS. Ngô Thị Bích', 'bich.bs@hospital.vn', '$2y$12$wa5hS.P1b..AgnGHh2RkZOsorAyjQBkG14cX4P.yW3sIoJ6mnZ7VC', '0908888888', 'Bệnh viện Đa khoa Trung tâm', '1977-07-12', 'Nữ', 2, NULL, 1, '2025-06-01 07:00:00'),
(19, 'BS. Lý Văn Thành', 'thanh.bs@hospital.vn', '$2y$12$wa5hS.P1b..AgnGHh2RkZOsorAyjQBkG14cX4P.yW3sIoJ6mnZ7VC', '0909999999', 'Bệnh viện Đa khoa Trung tâm', '1982-01-28', 'Nam', 2, NULL, 1, '2025-06-01 07:00:00'),
(20, 'BS. Đặng Thị Hằng', 'hang.bs@hospital.vn', '$2y$12$wa5hS.P1b..AgnGHh2RkZOsorAyjQBkG14cX4P.yW3sIoJ6mnZ7VC', '0910000000', 'Bệnh viện Đa khoa Trung tâm', '1979-05-22', 'Nữ', 2, NULL, 1, '2025-06-01 07:00:00'),
(21, 'ádsad', 'aaa@gmail.com', '$2y$12$vlL1wWlfN5/ztxF/HaNjUuss56EPyXdcUPG/STgSRP/4oYw4CB.Si', '0913841867', '123 nguyen trai', '2026-05-06', 'Nam', 3, NULL, 1, '2026-05-07 23:55:06'),
(23, 'Anh Tú Huỳnh', 'anh2482006@gmail.com', '$2y$12$6y7eJvCL/MaK4DRZFTiKzO.7Kj18EXX7dyzLUqJba6wHJQWlBfbUq', '0812816248', 'anh2482006@gmail.com', '2026-05-04', 'Nam', 1, NULL, 1, '2026-05-13 23:36:17'),
(24, 'Tú Huỳnh', 'tuh225095@gmail.com', '$2y$12$GsW2E6.alOYgVYOcioUrxenKya4KyRe1FtP4WnLahSJif/dJ8Y1JC', '1234567890', 'tuh225095@gmail.com', '2026-05-06', 'Nam', 3, NULL, 1, '2026-05-13 23:56:47'),
(22, 'Anh Tú Huỳnh', 'a123@gmail.com', '$2y$12$k8UuiGqtbaAtoyW0UhZQae.yZhnm43aPPvosi0GHy9WUIPDVKE152', '1234567890', 'a123@gmail.com', '2026-05-05', 'Nam', 1, NULL, 1, '2026-05-07 23:53:09')
SQL,
        ],
        [
            'table' => 'vaccines',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `vaccines` (`vaccine_id`, `vaccine_name`, `description`, `manufacturer`, `doses_required`, `status`) VALUES
(1, 'Cúm mùa', 'Phòng ngừa cúm mùa hàng năm', 'Sanofi', 1, 1),
(2, 'Viêm gan B', 'Phòng ngừa viêm gan B', 'GSK', 3, 1),
(3, 'HPV (Gardasil 9)', 'Phòng ngừa ung thư cổ tử cung', 'MSD', 3, 1),
(4, 'Sởi – Quai bị – Rubella', 'Vaccine phòng 3 bệnh cùng lúc', 'Merck', 2, 1),
(5, 'Thủy đậu', 'Phòng ngừa bệnh thủy đậu', 'GlaxoSmithKline', 2, 1),
(6, 'Viêm não Nhật Bản', 'Phòng ngừa viêm não Nhật Bản', 'Vabiotech', 3, 1),
(7, 'COVID-19 (Pfizer)', 'Vaccine phòng COVID-19 mRNA', 'Pfizer-BioNTech', 2, 1),
(8, 'Uốn ván – Bạch hầu', 'Phòng uốn ván và bạch hầu', 'Pasteur', 1, 1)
SQL,
        ],
        [
            'table' => 'vital_signs',
            'sql' => <<<'SQL'
INSERT IGNORE INTO `vital_signs` (`vital_id`, `record_id`, `blood_pressure`, `bp_status`, `heart_rate`, `hr_status`, `temperature`, `temp_status`, `spo2`, `spo2_status`, `weight`, `blood_sugar`, `sugar_status`, `created_at`, `updated_at`) VALUES
(1, 1, '124', 'normal', 75.0, 'normal', 34.0, 'normal', 66.0, 'normal', 66.0, 66.00, 'normal', '2026-05-14 03:18:03', '2026-05-14 03:18:03'),
(2, 2, '130/85', 'high', 78.0, 'normal', 36.7, 'normal', 98.0, 'normal', 58.0, 7.80, 'high', '2026-05-28 14:22:56', '2026-05-28 14:22:56'),
(3, 3, '125/80', 'normal', 80.0, 'normal', 36.5, 'normal', 97.0, 'normal', 72.0, 5.20, 'normal', '2026-05-28 14:22:56', '2026-05-28 14:22:56'),
(4, 4, '110/70', 'normal', 88.0, 'normal', 36.8, 'normal', 96.0, 'normal', 52.0, 5.00, 'normal', '2026-05-28 14:22:56', '2026-05-28 14:22:56'),
(5, 5, '128/82', 'normal', 76.0, 'normal', 36.6, 'normal', 98.0, 'normal', 80.0, 5.50, 'normal', '2026-05-28 14:22:56', '2026-05-28 14:22:56'),
(6, 6, '118/75', 'normal', 72.0, 'normal', 36.5, 'normal', 99.0, 'normal', 55.0, 5.10, 'normal', '2026-05-28 14:22:56', '2026-05-28 14:22:56'),
(7, 7, '135/90', 'high', 82.0, 'normal', 36.9, 'normal', 97.0, 'normal', 78.0, 5.40, 'normal', '2026-05-28 14:22:56', '2026-05-28 14:22:56'),
(8, 8, '120/80', 'normal', 75.0, 'normal', 36.6, 'normal', 98.0, 'normal', 65.0, 5.00, 'normal', '2026-05-28 14:22:56', '2026-05-28 14:22:56'),
(9, 9, '122/78', 'normal', 79.0, 'normal', 36.7, 'normal', 98.0, 'normal', 70.0, 5.20, 'normal', '2026-05-28 14:22:56', '2026-05-28 14:22:56'),
(10, 10, '115/72', 'normal', 74.0, 'normal', 36.5, 'normal', 99.0, 'normal', 60.0, 4.90, 'normal', '2026-05-28 14:22:56', '2026-05-28 14:22:56'),
(11, 11, '110/68', 'normal', 92.0, 'normal', 37.8, 'high', 97.0, 'normal', 62.0, 5.10, 'normal', '2026-05-28 14:22:56', '2026-05-28 14:22:56'),
(12, 12, '118/76', 'normal', 76.0, 'normal', 36.6, 'normal', 98.0, 'normal', 68.0, 5.30, 'normal', '2026-05-28 14:22:56', '2026-05-28 14:22:56'),
(13, 13, '105/65', 'low', 70.0, 'normal', 36.5, 'normal', 99.0, 'normal', 63.0, 4.80, 'normal', '2026-05-28 14:22:56', '2026-05-28 14:22:56')
SQL,
        ],
    ];

    public function run(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('DROP VIEW IF EXISTS v_doctorratings');
        DB::statement('DROP TABLE IF EXISTS v_doctorratings');
        DB::connection()->getPdo()->exec('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach (self::STATEMENTS as $statement) {
                if (! Schema::hasTable($statement['table'])) {
                    continue;
                }

                DB::connection()->getPdo()->exec($statement['sql']);
            }
        } finally {
            DB::connection()->getPdo()->exec('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->createDoctorRatingsView();
    }

    private function createDoctorRatingsView(): void
    {
        if (! Schema::hasTable('doctors') || ! Schema::hasTable('reviews')) {
            return;
        }

        DB::statement('DROP VIEW IF EXISTS v_doctorratings');
        DB::statement("
            CREATE VIEW v_doctorratings AS
            SELECT
                d.doctor_id,
                d.full_name,
                d.department_id,
                d.experience,
                d.price,
                d.avatar_url,
                d.bio,
                d.status,
                COALESCE(AVG(r.rating), 0) AS avg_rating,
                COUNT(r.review_id) AS total_reviews
            FROM doctors d
            LEFT JOIN reviews r ON r.doctor_id = d.doctor_id
            GROUP BY
                d.doctor_id,
                d.full_name,
                d.department_id,
                d.experience,
                d.price,
                d.avatar_url,
                d.bio,
                d.status
        ");
    }
}
