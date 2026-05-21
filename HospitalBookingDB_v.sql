-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3306
-- Thời gian đã tạo: Th5 13, 2026 lúc 07:01 PM
-- Phiên bản máy phục vụ: 8.4.7
-- Phiên bản PHP: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `hospitalbookingdb`
--
CREATE DATABASE IF NOT EXISTS `hospitalbookingdb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hospitalbookingdb`;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `activitylogs`
--

DROP TABLE IF EXISTS `activitylogs`;
CREATE TABLE IF NOT EXISTS `activitylogs` (
  `log_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `activitylogs_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `activitylogs`
--

INSERT INTO `activitylogs` (`log_id`, `user_id`, `action`, `ip_address`, `created_at`) VALUES
(1, 1, 'Đặt lịch hẹn #1', '127.0.0.1', '2026-04-23 04:53:29'),
(2, 1, 'Đặt lịch hẹn #2', '127.0.0.1', '2026-04-23 07:09:56'),
(3, 1, 'Hủy lịch hẹn #2', '127.0.0.1', '2026-04-23 07:10:54'),
(4, 1, 'Đặt lịch hẹn #3', '127.0.0.1', '2026-04-23 07:12:49'),
(5, 1, 'Đặt lịch hẹn #4', '127.0.0.1', '2026-04-23 07:16:20'),
(6, 1, 'Đặt lịch hẹn #5', '127.0.0.1', '2026-04-23 07:19:22'),
(7, 1, 'Đặt lịch hẹn #6', '127.0.0.1', '2026-04-23 10:00:41'),
(8, 1, 'Đặt lịch hẹn #7', '127.0.0.1', '2026-04-23 10:08:06'),
(9, 1, 'Đặt lịch hẹn #8', '127.0.0.1', '2026-04-23 10:27:33'),
(10, 2, 'Xác nhận đặt lịch hẹn #10', '127.0.0.1', '2026-04-23 11:15:21'),
(11, 2, 'Xác nhận đặt lịch hẹn #12', '127.0.0.1', '2026-04-23 11:21:25'),
(12, 1, 'Đặt lịch hẹn #16', '127.0.0.1', '2026-04-23 13:23:49'),
(13, 1, 'Đặt lịch hẹn #17', '127.0.0.1', '2026-04-23 13:43:24'),
(14, 3, 'Đặt lịch hẹn #18', '127.0.0.1', '2026-04-23 15:08:02'),
(15, 1, 'Đặt lịch hẹn #19', '127.0.0.1', '2026-04-23 15:17:18'),
(16, 1, 'Dời lịch hẹn #19 sang schedule #243', '127.0.0.1', '2026-04-23 15:17:31'),
(17, 2, 'Đặt lịch hẹn #20', '127.0.0.1', '2026-04-23 16:17:11'),
(18, 1, 'Đặt lịch hẹn #21', '127.0.0.1', '2026-04-23 16:18:17'),
(19, 4, 'Đặt lịch hẹn #22', '127.0.0.1', '2026-05-05 12:29:13'),
(20, 2, 'Đặt lịch hẹn #23', '127.0.0.1', '2026-05-05 12:53:24'),
(21, 2, 'Đặt lịch hẹn #24', '127.0.0.1', '2026-05-06 03:51:39'),
(22, 4, 'Đặt lịch hẹn #25', '127.0.0.1', '2026-05-06 05:52:01'),
(23, 2, 'Đặt lịch hẹn #26', '127.0.0.1', '2026-05-06 05:52:16'),
(24, 4, 'Đặt lịch hẹn #27', '127.0.0.1', '2026-05-06 14:17:21'),
(25, 4, 'Đặt lịch hẹn #28', '127.0.0.1', '2026-05-06 14:59:48'),
(26, 4, 'Tạo đánh giá bác sĩ #11', '127.0.0.1', '2026-05-07 05:20:20'),
(27, 4, 'Đặt lịch hẹn #29', '127.0.0.1', '2026-05-07 05:46:13'),
(28, 4, 'Tạo đánh giá bác sĩ #5', '127.0.0.1', '2026-05-07 05:46:42'),
(29, 4, 'Cập nhật đánh giá #4', '127.0.0.1', '2026-05-07 06:10:09'),
(30, 4, 'Xóa đánh giá #4 (bác sĩ #11)', '127.0.0.1', '2026-05-07 06:10:18'),
(31, 4, 'Đặt lịch hẹn #30', '127.0.0.1', '2026-05-07 16:39:03'),
(32, 4, 'Cập nhật đánh giá #5', '127.0.0.1', '2026-05-07 16:40:23'),
(33, 4, 'Tạo đánh giá bác sĩ #7', '127.0.0.1', '2026-05-07 16:41:18'),
(34, 4, 'Cập nhật đánh giá #6', '127.0.0.1', '2026-05-07 16:41:43'),
(35, 4, 'Cập nhật đánh giá #5', '127.0.0.1', '2026-05-07 16:48:23'),
(36, 4, 'Cập nhật đánh giá #6', '127.0.0.1', '2026-05-07 16:52:20'),
(37, 21, 'Đặt lịch hẹn #31', '127.0.0.1', '2026-05-07 16:55:17'),
(38, 21, 'Tạo đánh giá bác sĩ #10', '127.0.0.1', '2026-05-07 16:56:06'),
(39, 21, 'Đặt lịch hẹn #32', '127.0.0.1', '2026-05-07 17:00:23'),
(40, 21, 'Dời lịch hẹn #32 sang schedule #382', '127.0.0.1', '2026-05-07 17:01:02'),
(41, 21, 'Tạo đánh giá bác sĩ #6', '127.0.0.1', '2026-05-08 00:01:56'),
(42, 4, 'Tạo đánh giá bác sĩ #11', '127.0.0.1', '2026-05-08 00:10:47'),
(43, 21, 'Cập nhật đánh giá #8', '127.0.0.1', '2026-05-08 00:26:28'),
(44, 22, 'Đặt lịch hẹn #33', '127.0.0.1', '2026-05-08 01:56:50'),
(45, 22, 'Tạo đánh giá bác sĩ #7', '127.0.0.1', '2026-05-08 08:57:45'),
(46, 22, 'Cập nhật đánh giá #10', '127.0.0.1', '2026-05-08 10:11:47'),
(47, 22, 'Cập nhật đánh giá #10', '127.0.0.1', '2026-05-08 10:11:52'),
(48, 23, 'Đặt lịch hẹn #34', '127.0.0.1', '2026-05-13 16:36:57'),
(49, 23, 'Dời lịch hẹn #34 sang schedule #382', '127.0.0.1', '2026-05-13 16:37:35'),
(50, 23, 'Hủy lịch hẹn #34', '127.0.0.1', '2026-05-13 16:42:54'),
(51, 23, 'Đặt lịch hẹn #35', '127.0.0.1', '2026-05-13 16:53:38');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `appointments`
--

DROP TABLE IF EXISTS `appointments`;
CREATE TABLE IF NOT EXISTS `appointments` (
  `appointment_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `schedule_id` int UNSIGNED NOT NULL,
  `service_id` int UNSIGNED DEFAULT NULL,
  `appointment_time` datetime DEFAULT NULL,
  `appointment_timeEnd` datetime DEFAULT NULL,
  `queue_number` int UNSIGNED DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chờ xác nhận',
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancel_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slot_hold_expire` datetime DEFAULT NULL,
  `rescheduled_from` int UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mail_reminded_1day` tinyint(1) NOT NULL DEFAULT '0',
  `mail_reminded_1hour` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`appointment_id`),
  UNIQUE KEY `UQ_Appointments_UserSchedule` (`user_id`,`schedule_id`),
  KEY `appointments_schedule_id_foreign` (`schedule_id`),
  KEY `appointments_service_id_foreign` (`service_id`),
  KEY `appointments_rescheduled_from_foreign` (`rescheduled_from`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `user_id`, `schedule_id`, `service_id`, `appointment_time`, `appointment_timeEnd`, `queue_number`, `status`, `note`, `cancel_reason`, `slot_hold_expire`, `rescheduled_from`, `created_at`, `mail_reminded_1day`, `mail_reminded_1hour`) VALUES
(27, 4, 296, NULL, '2026-05-06 09:30:00', '2026-05-06 10:00:00', 1, 'Chờ xác nhận', NULL, NULL, NULL, NULL, '2026-05-06 14:17:21', 0, 0),
(28, 4, 445, NULL, '2026-05-14 14:00:00', '2026-05-14 14:30:00', 1, 'Đã Khám', NULL, NULL, NULL, NULL, '2026-05-06 14:59:48', 0, 0),
(29, 4, 357, NULL, '2026-05-07 14:30:00', '2026-05-07 15:00:00', 1, 'Hoàn Thành', NULL, NULL, NULL, NULL, '2026-05-07 05:46:13', 0, 0),
(30, 4, 390, NULL, '2026-05-07 09:00:00', '2026-05-07 09:30:00', 1, 'Hoàn Thành', NULL, NULL, NULL, NULL, '2026-05-07 16:39:03', 0, 0),
(31, 21, 427, NULL, '2026-05-07 09:00:00', '2026-05-07 09:30:00', 1, 'Hoàn Thành', NULL, NULL, NULL, NULL, '2026-05-07 16:55:17', 0, 0),
(32, 21, 382, NULL, '2026-05-15 14:00:00', '2026-05-07 10:30:00', 1, 'Hoàn Thành', NULL, 'Dời sang lịch mới', NULL, 376, '2026-05-07 17:00:23', 0, 0),
(33, 22, 391, NULL, '2026-05-08 08:30:00', '2026-05-08 09:00:00', 1, 'Hoàn Thành', NULL, NULL, NULL, NULL, '2026-05-08 01:56:50', 0, 0),
(34, 23, 382, NULL, '2026-05-15 14:00:00', '2026-05-13 10:00:00', 2, 'Đã hủy', NULL, 'Bệnh nhân tự hủy', NULL, 380, '2026-05-13 16:36:57', 0, 0),
(35, 23, 394, 2, '2026-05-13 13:30:00', '2026-05-13 14:00:00', 1, 'Chờ xác nhận', NULL, NULL, NULL, NULL, '2026-05-13 16:53:38', 0, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bhyt_cards`
--

DROP TABLE IF EXISTS `bhyt_cards`;
CREATE TABLE IF NOT EXISTS `bhyt_cards` (
  `bhyt_card_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `patient_id` int UNSIGNED NOT NULL,
  `card_number` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date NOT NULL,
  `coverage_rate` int UNSIGNED NOT NULL DEFAULT '80',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Còn hạn',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`bhyt_card_id`),
  UNIQUE KEY `bhyt_cards_card_number_unique` (`card_number`),
  KEY `bhyt_cards_patient_id_foreign` (`patient_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chatmessages`
--

DROP TABLE IF EXISTS `chatmessages`;
CREATE TABLE IF NOT EXISTS `chatmessages` (
  `message_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_id` int UNSIGNED NOT NULL,
  `sender_id` int UNSIGNED NOT NULL,
  `message_text` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `is_ai` tinyint NOT NULL DEFAULT '0',
  `sent_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `chatmessages_room_id_foreign` (`room_id`),
  KEY `chatmessages_sender_id_foreign` (`sender_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chatmessages`
--

INSERT INTO `chatmessages` (`message_id`, `room_id`, `sender_id`, `message_text`, `is_read`, `is_ai`, `sent_at`) VALUES
(1, 1, 23, 'hi', 0, 0, '2026-05-13 18:56:55');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chatrooms`
--

DROP TABLE IF EXISTS `chatrooms`;
CREATE TABLE IF NOT EXISTS `chatrooms` (
  `room_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `doctor_id` int UNSIGNED DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Mở',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `closed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`room_id`),
  KEY `chatrooms_user_id_foreign` (`user_id`),
  KEY `chatrooms_doctor_id_foreign` (`doctor_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chatrooms`
--

INSERT INTO `chatrooms` (`room_id`, `user_id`, `doctor_id`, `status`, `created_at`, `closed_at`) VALUES
(1, 23, NULL, 'Mở', '2026-05-13 18:56:49', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `checkins`
--

DROP TABLE IF EXISTS `checkins`;
CREATE TABLE IF NOT EXISTS `checkins` (
  `checkin_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `appointment_id` int UNSIGNED NOT NULL,
  `checkin_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `queue_number` int UNSIGNED DEFAULT NULL,
  `est_wait_minutes` int UNSIGNED DEFAULT NULL,
  `called_at` datetime DEFAULT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đang chờ',
  PRIMARY KEY (`checkin_id`),
  UNIQUE KEY `checkins_appointment_id_unique` (`appointment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `department_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`department_id`),
  UNIQUE KEY `departments_department_name_unique` (`department_name`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `description`, `status`) VALUES
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
(12, 'Nội tiết', 'Điều trị đái tháo đường, tuyến giáp, rối loạn nội tiết', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `doctordaysoff`
--

DROP TABLE IF EXISTS `doctordaysoff`;
CREATE TABLE IF NOT EXISTS `doctordaysoff` (
  `day_off_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `doctor_id` int UNSIGNED NOT NULL,
  `off_date` date NOT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`day_off_id`),
  UNIQUE KEY `UQ_DoctorDaysOff` (`doctor_id`,`off_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `doctors`
--

DROP TABLE IF EXISTS `doctors`;
CREATE TABLE IF NOT EXISTS `doctors` (
  `doctor_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_id` int UNSIGNED NOT NULL,
  `experience` int UNSIGNED DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `avatar_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`doctor_id`),
  UNIQUE KEY `doctors_user_id_unique` (`user_id`),
  KEY `doctors_department_id_foreign` (`department_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `doctors`
--

INSERT INTO `doctors` (`doctor_id`, `user_id`, `full_name`, `department_id`, `experience`, `price`, `avatar_url`, `bio`, `status`) VALUES
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
(12, 20, 'Đặng Thị Hằng', 12, 17, 290000.00, NULL, 'Chuyên gia nội tiết, điều trị đái tháo đường và bệnh tuyến giáp', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `doctorschedules`
--

DROP TABLE IF EXISTS `doctorschedules`;
CREATE TABLE IF NOT EXISTS `doctorschedules` (
  `schedule_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `doctor_id` int UNSIGNED NOT NULL,
  `room_id` int UNSIGNED DEFAULT NULL,
  `work_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `slot_duration` int UNSIGNED NOT NULL DEFAULT '30',
  `max_slot` int UNSIGNED DEFAULT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Hoạt động',
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`schedule_id`),
  UNIQUE KEY `UQ_DoctorSchedules` (`doctor_id`,`work_date`,`start_time`),
  KEY `doctorschedules_room_id_foreign` (`room_id`)
) ENGINE=MyISAM AUTO_INCREMENT=465 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `doctorschedules`
--

INSERT INTO `doctorschedules` (`schedule_id`, `doctor_id`, `room_id`, `work_date`, `start_time`, `end_time`, `slot_duration`, `max_slot`, `status`, `note`) VALUES
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
(464, 12, 12, '2026-06-01', '09:00:00', '12:00:00', 30, 6, 'Hoạt động', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `emergency_contacts`
--

DROP TABLE IF EXISTS `emergency_contacts`;
CREATE TABLE IF NOT EXISTS `emergency_contacts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `priority` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `relationship` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lab_notifications` tinyint(1) NOT NULL DEFAULT '0',
  `recovery_updates` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `emergency_contacts_user_id_priority_unique` (`user_id`,`priority`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `emergency_contacts`
--

INSERT INTO `emergency_contacts` (`id`, `user_id`, `priority`, `name`, `relationship`, `phone`, `email`, `lab_notifications`, `recovery_updates`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 9, 1, 'Nguyễn thị', 'Vợ/Chồng', '0976662334', 'haha@gmail.com', 1, 1, '2026-05-02 06:37:36', '2026-05-02 06:37:36', NULL),
(2, 9, 2, 'Nguyễn Trần', 'Con', '0976662335', 'hihi@gmail.com', 1, 0, '2026-05-02 06:37:36', '2026-05-02 06:37:36', NULL),
(3, 9, 3, 'Nguyễn Thành Tài', 'Người giám hộ', '0976662337', 'hehe@gmail.com', 1, 0, '2026-05-02 06:37:36', '2026-05-02 06:41:10', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `health_backgrounds`
--

DROP TABLE IF EXISTS `health_backgrounds`;
CREATE TABLE IF NOT EXISTS `health_backgrounds` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `blood_group` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `yeuto_rh` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `height` double DEFAULT NULL,
  `weight` double DEFAULT NULL,
  `bmi` double DEFAULT NULL,
  `food_allergies` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `drug_allergies` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `chronic_diseases` json DEFAULT NULL,
  `other_chronic_diseases` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `health_backgrounds_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `health_backgrounds`
--

INSERT INTO `health_backgrounds` (`id`, `user_id`, `blood_group`, `yeuto_rh`, `height`, `weight`, `bmi`, `food_allergies`, `drug_allergies`, `chronic_diseases`, `other_chronic_diseases`, `created_at`, `updated_at`) VALUES
(1, 9, 'A+', 'negative', 173, 80, 26.73, 'Bánh Kem , Lá Khổ Qua', 'Aspirin,vitaminC', '[\"TĂNG HUYẾT ÁP\", \"TUỘT HUYẾT ÁP\"]', 'aaa', '2026-05-04 20:28:57', '2026-05-04 20:28:57');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hospitalnews`
--

DROP TABLE IF EXISTS `hospitalnews`;
CREATE TABLE IF NOT EXISTS `hospitalnews` (
  `news_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_id` int UNSIGNED DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `email_sent` tinyint NOT NULL DEFAULT '0',
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`news_id`),
  KEY `hospitalnews_author_id_foreign` (`author_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `hospitalnews`
--

INSERT INTO `hospitalnews` (`news_id`, `title`, `content`, `category`, `thumbnail`, `author_id`, `is_published`, `email_sent`, `published_at`, `created_at`) VALUES
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
(16, 'Khuyến nghị tiêm vaccine cúm định kỳ', 'Bác sĩ khuyến nghị người cao tuổi, trẻ em và người có bệnh nền nên tiêm vaccine cúm mỗi năm để tăng cường miễn dịch.', 'Sức khỏe', 'uploads/news/news10.jpg', 8, 1, 0, '2026-05-14 00:48:55', '2026-05-14 00:48:55');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `insurancecards`
--

DROP TABLE IF EXISTS `insurancecards`;
CREATE TABLE IF NOT EXISTS `insurancecards` (
  `insurance_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `card_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issued_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `discount_pct` decimal(5,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Còn hạn',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`insurance_id`),
  KEY `insurancecards_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `insurancecards`
--

INSERT INTO `insurancecards` (`insurance_id`, `user_id`, `card_number`, `provider`, `issued_date`, `expiry_date`, `discount_pct`, `status`, `created_at`) VALUES
(1, 5, 'BH4030123456789', 'BHXH Việt Nam', '2025-01-01', '2026-12-31', 80.00, 'Còn hạn', '2026-05-06 21:19:14'),
(2, 6, 'BH4030987654321', 'BHXH Việt Nam', '2025-01-01', '2026-12-31', 80.00, 'Còn hạn', '2026-05-06 21:19:14'),
(3, 7, 'BH8020112233445', 'Bảo Việt', '2024-07-01', '2026-06-30', 50.00, 'Còn hạn', '2026-05-06 21:19:14'),
(4, 8, 'BH4030556677889', 'BHXH Việt Nam', '2025-01-01', '2026-12-31', 80.00, 'Còn hạn', '2026-05-06 21:19:14'),
(5, 9, 'BH8020998877665', 'Prudential', '2025-03-01', '2027-02-28', 60.00, 'Còn hạn', '2026-05-06 21:19:14'),
(6, 10, 'BH4030001122334', 'BHXH Việt Nam', '2025-01-01', '2025-12-31', 80.00, 'Hết hạn', '2026-05-06 21:19:14');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `invoices`
--

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE IF NOT EXISTS `invoices` (
  `invoice_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `patient_id` int UNSIGNED NOT NULL,
  `doctor_id` int UNSIGNED DEFAULT NULL,
  `appointment_id` int UNSIGNED DEFAULT NULL,
  `bhyt_card_id` int UNSIGNED DEFAULT NULL,
  `issue_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `due_date` datetime DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `bhyt_applied` tinyint(1) NOT NULL DEFAULT '0',
  `bhyt_coverage` int UNSIGNED NOT NULL DEFAULT '0',
  `bhyt_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chờ thanh toán',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`invoice_id`),
  UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  KEY `invoices_patient_id_foreign` (`patient_id`),
  KEY `invoices_doctor_id_foreign` (`doctor_id`),
  KEY `invoices_appointment_id_foreign` (`appointment_id`),
  KEY `invoices_bhyt_card_id_foreign` (`bhyt_card_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
CREATE TABLE IF NOT EXISTS `invoice_items` (
  `item_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` int UNSIGNED NOT NULL,
  `service_id` int UNSIGNED DEFAULT NULL,
  `service_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int UNSIGNED NOT NULL DEFAULT '1',
  `unit_price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `invoice_items_invoice_id_foreign` (`invoice_id`),
  KEY `invoice_items_service_id_foreign` (`service_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `medicaldocuments`
--

DROP TABLE IF EXISTS `medicaldocuments`;
CREATE TABLE IF NOT EXISTS `medicaldocuments` (
  `doc_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `record_id` int UNSIGNED DEFAULT NULL,
  `doc_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`doc_id`),
  KEY `medicaldocuments_user_id_foreign` (`user_id`),
  KEY `medicaldocuments_record_id_foreign` (`record_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `medicalrecords`
--

DROP TABLE IF EXISTS `medicalrecords`;
CREATE TABLE IF NOT EXISTS `medicalrecords` (
  `record_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `appointment_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `doctor_id` int UNSIGNED DEFAULT NULL,
  `diagnosis` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prescription` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `medicalrecords_appointment_id_unique` (`appointment_id`),
  KEY `medicalrecords_user_id_foreign` (`user_id`),
  KEY `medicalrecords_doctor_id_foreign` (`doctor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `medicines`
--

DROP TABLE IF EXISTS `medicines`;
CREATE TABLE IF NOT EXISTS `medicines` (
  `medicine_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `medicine_code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `medicine_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int NOT NULL DEFAULT '0',
  `min_stock` int NOT NULL DEFAULT '10',
  `expiry_date` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`medicine_id`),
  UNIQUE KEY `medicines_medicine_code_unique` (`medicine_code`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `medicines`
--

INSERT INTO `medicines` (`medicine_id`, `medicine_code`, `medicine_name`, `unit`, `unit_price`, `stock_quantity`, `min_stock`, `expiry_date`, `status`) VALUES
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
(15, 'MED015', 'Clindamycin gel 1%', 'Tuýp', 85000.00, 300, 20, '2027-04-30', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `medicinetransactions`
--

DROP TABLE IF EXISTS `medicinetransactions`;
CREATE TABLE IF NOT EXISTS `medicinetransactions` (
  `transaction_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `medicine_id` int UNSIGNED NOT NULL,
  `trans_type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `reference_id` int UNSIGNED DEFAULT NULL,
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`transaction_id`),
  KEY `medicinetransactions_medicine_id_foreign` (`medicine_id`),
  KEY `medicinetransactions_created_by_foreign` (`created_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `membershipcards`
--

DROP TABLE IF EXISTS `membershipcards`;
CREATE TABLE IF NOT EXISTS `membershipcards` (
  `card_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `card_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tier` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Thường',
  `points` int NOT NULL DEFAULT '0',
  `total_spent` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount_pct` decimal(5,2) NOT NULL DEFAULT '0.00',
  `issue_date` date NOT NULL DEFAULT (curdate()),
  `expiry_date` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`card_id`),
  UNIQUE KEY `membershipcards_user_id_unique` (`user_id`),
  UNIQUE KEY `membershipcards_card_number_unique` (`card_number`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `membershipcards`
--

INSERT INTO `membershipcards` (`card_id`, `user_id`, `card_number`, `tier`, `points`, `total_spent`, `discount_pct`, `issue_date`, `expiry_date`, `status`) VALUES
(1, 5, 'MEM20260001', 'Bạc', 1500, 1500000.00, 5.00, '2026-01-10', '2027-01-10', 1),
(2, 6, 'MEM20260002', 'Vàng', 8500, 8500000.00, 10.00, '2026-01-12', '2027-01-12', 1),
(3, 7, 'MEM20260003', 'Thường', 350, 350000.00, 0.00, '2026-01-15', '2027-01-15', 1),
(4, 8, 'MEM20260004', 'Bạc', 2200, 2200000.00, 5.00, '2026-01-18', '2027-01-18', 1),
(5, 9, 'MEM20260005', 'Kim Cương', 25000, 25000000.00, 20.00, '2026-01-20', '2027-01-20', 1),
(6, 22, 'Chưa có thẻ', 'Thường', 0, 0.00, 0.00, '2026-05-08', NULL, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2024_01_01_000001_create_roles_table', 1),
(2, '2024_01_01_000002_create_users_table', 1),
(3, '2024_01_01_000003_create_departments_table', 1),
(4, '2024_01_01_000004_create_rooms_table', 1),
(5, '2024_01_01_000005_create_doctors_table', 1),
(6, '2024_01_01_000006_create_doctor_schedules_table', 1),
(7, '2024_01_01_000007_create_doctor_days_off_table', 1),
(8, '2024_01_01_000008_create_services_table', 1),
(9, '2024_01_01_000009_create_service_prices_table', 1),
(10, '2024_01_01_000010_create_appointments_table', 1),
(11, '2024_01_01_000011_create_checkins_table', 1),
(12, '2024_01_01_000012_create_insurance_cards_table', 1),
(13, '2024_01_01_000013_create_membership_cards_table', 1),
(14, '2024_01_01_000014_create_payments_table', 1),
(15, '2024_01_01_000015_create_payment_items_table', 1),
(16, '2024_01_01_000016_create_medical_records_table', 1),
(17, '2024_01_01_000017_create_medical_documents_table', 1),
(18, '2024_01_01_000018_create_patient_allergies_table', 1),
(19, '2024_01_01_000019_create_patient_medical_history_table', 1),
(20, '2024_01_01_000020_create_treatment_reminders_table', 1),
(21, '2024_01_01_000021_create_vaccines_table', 1),
(22, '2024_01_01_000022_create_vaccination_records_table', 1),
(23, '2024_01_01_000023_create_medicines_table', 1),
(24, '2024_01_01_000024_create_medicine_transactions_table', 1),
(25, '2024_01_01_000025_create_reviews_table', 1),
(26, '2024_01_01_000026_create_chat_rooms_table', 1),
(27, '2024_01_01_000027_create_chat_messages_table', 1),
(28, '2024_01_01_000028_create_hospital_news_table', 1),
(29, '2024_01_01_000029_create_notifications_table', 1),
(30, '2024_01_01_000030_create_activity_logs_table', 1),
(31, '2026_04_23_082114_create_tiensu_table', 2),
(32, '2026_05_05_134149_add_appointment_time_end_to_appointments_table', 3),
(33, '2024_01_01_000031_create_bhyt_cards_table', 4),
(34, '2024_01_01_000032_create_invoices_table', 4),
(35, '2024_01_01_000033_create_invoice_items_table', 4),
(37, '2025_05_08_fix_reviews_table', 5),
(38, '2026_05_13_151551_create_hospitalnews_table', 6),
(39, '2026_05_13_161224_create_jobs_table', 7),
(40, '2026_05_13_161917_create_cache_table', 8),
(41, '2026_05_13_184531_add_is_ai_to_chatmessages_table', 9),
(42, '2026_05_14_010000_fix_chatrooms_staff_foreign_key', 10);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `notif_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_id` int UNSIGNED DEFAULT NULL,
  `ref_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `notifications_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `notif_type`, `title`, `content`, `ref_id`, `ref_type`, `is_read`, `created_at`) VALUES
(1, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 07:34 ngày 23/04/2026. Số thứ tự: #1', 1, 'appointment', 0, '2026-04-23 04:53:29'),
(2, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 13:30 ngày 23/04/2026. Số thứ tự: #1', 2, 'appointment', 0, '2026-04-23 07:09:56'),
(3, 1, 'Lịch hẹn', 'Hủy lịch hẹn thành công', 'Lịch hẹn #2 đã được hủy.', 2, 'appointment', 0, '2026-04-23 07:10:54'),
(4, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:00 ngày 23/04/2026. Số thứ tự: #1', 3, 'appointment', 0, '2026-04-23 07:12:49'),
(5, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 08:00 ngày 23/04/2026. Số thứ tự: #1', 4, 'appointment', 0, '2026-04-23 07:16:20'),
(6, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 08:00 ngày 24/04/2026. Số thứ tự: #1', 5, 'appointment', 0, '2026-04-23 07:19:22'),
(7, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 13:30 ngày 23/04/2026. Số thứ tự: #1', 6, 'appointment', 0, '2026-04-23 10:00:41'),
(8, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 10:30 ngày 25/04/2026. Số thứ tự: #1', 7, 'appointment', 0, '2026-04-23 10:08:06'),
(9, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 08:00 ngày 23/04/2026. Số thứ tự: #1', 8, 'appointment', 0, '2026-04-23 10:27:33'),
(10, 2, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 13:30 ngày 23/04/2026. Số thứ tự: #1', 10, 'appointment', 0, '2026-04-23 11:15:21'),
(11, 2, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 15:00 ngày 23/04/2026. Số thứ tự: #1', 12, 'appointment', 0, '2026-04-23 11:21:25'),
(12, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 13:30 ngày 24/04/2026. Số thứ tự: #1', 16, 'appointment', 0, '2026-04-23 13:23:49'),
(13, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 08:00 ngày 24/04/2026. Số thứ tự: #1', 17, 'appointment', 0, '2026-04-23 13:43:24'),
(14, 3, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:00 ngày 23/04/2026. Số thứ tự: #1', 18, 'appointment', 0, '2026-04-23 15:08:02'),
(15, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:00 ngày 25/04/2026. Số thứ tự: #1', 19, 'appointment', 0, '2026-04-23 15:17:18'),
(16, 1, 'Lịch hẹn', 'Dời lịch hẹn thành công', 'Lịch hẹn #19 đã được dời sang 08:00 30/04/2026', 19, 'appointment', 0, '2026-04-23 15:17:31'),
(17, 2, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 14:00 ngày 24/04/2026. Số thứ tự: #1', 20, 'appointment', 0, '2026-04-23 16:17:11'),
(18, 1, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 13:30 ngày 24/04/2026. Số thứ tự: #2', 21, 'appointment', 0, '2026-04-23 16:18:17'),
(19, 4, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:00 ngày 05/05/2026. Số thứ tự: #1', 22, 'appointment', 0, '2026-05-05 12:29:13'),
(20, 2, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 10:00 ngày 05/05/2026. Số thứ tự: #2', 23, 'appointment', 0, '2026-05-05 12:53:24'),
(21, 2, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:30 ngày 06/05/2026. Số thứ tự: #1', 24, 'appointment', 0, '2026-05-06 03:51:39'),
(22, 4, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:00 ngày 06/05/2026. Số thứ tự: #1', 25, 'appointment', 0, '2026-05-06 05:52:01'),
(23, 2, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:00 ngày 06/05/2026. Số thứ tự: #2', 26, 'appointment', 0, '2026-05-06 05:52:16'),
(24, 4, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:30 ngày 06/05/2026. Số thứ tự: #1', 27, 'appointment', 0, '2026-05-06 14:17:21'),
(25, 4, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 14:00 ngày 14/05/2026. Số thứ tự: #1', 28, 'appointment', 0, '2026-05-06 14:59:48'),
(26, 4, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 14:30 ngày 07/05/2026. Số thứ tự: #1', 29, 'appointment', 0, '2026-05-07 05:46:13'),
(27, 4, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:00 ngày 07/05/2026. Số thứ tự: #1', 30, 'appointment', 0, '2026-05-07 16:39:03'),
(28, 21, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:00 ngày 07/05/2026. Số thứ tự: #1', 31, 'appointment', 0, '2026-05-07 16:55:17'),
(29, 21, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 10:00 ngày 07/05/2026. Số thứ tự: #1', 32, 'appointment', 0, '2026-05-07 17:00:23'),
(30, 21, 'Lịch hẹn', 'Dời lịch hẹn thành công', 'Lịch hẹn #32 đã được dời sang 14:00 15/05/2026', 32, 'appointment', 0, '2026-05-07 17:01:02'),
(31, 22, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 08:30 ngày 08/05/2026. Số thứ tự: #1', 33, 'appointment', 0, '2026-05-08 01:56:50'),
(32, 23, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 09:30 ngày 13/05/2026. Số thứ tự: #1', 34, 'appointment', 0, '2026-05-13 16:36:57'),
(33, 23, 'Lịch hẹn', 'Dời lịch hẹn thành công', 'Lịch hẹn #34 đã được dời sang 14:00 15/05/2026', 34, 'appointment', 0, '2026-05-13 16:37:35'),
(34, 23, 'Lịch hẹn', 'Hủy lịch hẹn thành công', 'Lịch hẹn #34 đã được hủy.', 34, 'appointment', 0, '2026-05-13 16:42:54'),
(35, 23, 'Lịch hẹn', 'Đặt lịch hẹn thành công', 'Lịch khám lúc 13:30 ngày 13/05/2026. Số thứ tự: #1', 35, 'appointment', 0, '2026-05-13 16:53:38');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `patientallergies`
--

DROP TABLE IF EXISTS `patientallergies`;
CREATE TABLE IF NOT EXISTS `patientallergies` (
  `allergy_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `allergen` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reaction` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `severity` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `noted_date` date NOT NULL DEFAULT (curdate()),
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`allergy_id`),
  KEY `patientallergies_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `patientallergies`
--

INSERT INTO `patientallergies` (`allergy_id`, `user_id`, `allergen`, `reaction`, `severity`, `noted_date`, `notes`) VALUES
(1, 5, 'Penicillin', 'Phát ban, ngứa toàn thân', 'Nặng', '2020-03-10', 'Dị ứng kháng sinh nhóm beta-lactam'),
(2, 6, 'Tôm', 'Nổi mề đay, khó thở', 'Nặng', '2018-07-15', 'Dị ứng hải sản'),
(3, 7, 'Aspirin', 'Đau dạ dày, xuất huyết nhẹ', 'Vừa', '2022-11-20', 'Không dùng NSAID'),
(4, 8, 'Sulfonamide', 'Sốc phản vệ nhẹ', 'Nặng', '2019-05-05', 'Cần báo bác sĩ trước khi dùng thuốc'),
(5, 9, 'Phấn hoa', 'Chảy nước mắt, hắt hơi', 'Nhẹ', '2021-04-01', 'Dị ứng theo mùa'),
(6, 10, 'Mủ latex', 'Ngứa, sưng đỏ da', 'Vừa', '2017-08-22', 'Thông báo cho phòng phẫu thuật');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `patientmedicalhistory`
--

DROP TABLE IF EXISTS `patientmedicalhistory`;
CREATE TABLE IF NOT EXISTS `patientmedicalhistory` (
  `history_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `condition` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `diagnosed_at` date DEFAULT NULL,
  `treated_at` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_chronic` tinyint(1) NOT NULL DEFAULT '0',
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`history_id`),
  KEY `patientmedicalhistory_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `patientmedicalhistory`
--

INSERT INTO `patientmedicalhistory` (`history_id`, `user_id`, `condition`, `diagnosed_at`, `treated_at`, `is_chronic`, `notes`) VALUES
(1, 5, 'Tăng huyết áp', '2020-01-15', 'Bệnh viện Chợ Rẫy', 1, 'Đang dùng Amlodipine 5mg/ngày'),
(2, 5, 'Đái tháo đường type 2', '2021-06-10', 'Bệnh viện Đa khoa Trung tâm', 1, 'HbA1c 7.2%, kiểm soát bằng Metformin'),
(3, 6, 'Viêm dạ dày mạn', '2019-03-20', 'Phòng khám tư', 1, 'H. pylori dương tính, đã điều trị'),
(4, 7, 'Hen phế quản', '2015-09-05', 'Bệnh viện Nhi Đồng', 1, 'Dùng salbutamol khi cần'),
(5, 8, 'Gout', '2022-04-18', 'Bệnh viện Thống Nhất', 1, 'Uric acid cao, chế độ ăn kiêng'),
(6, 9, 'Trào ngược dạ dày thực quản', '2023-01-10', 'Phòng khám nội khoa', 0, 'Điều trị khỏi sau 3 tháng'),
(7, 10, 'Cột sống thắt lưng L4-L5', '2018-11-30', 'Bệnh viện Chấn Thương', 1, 'Thoát vị đĩa đệm, đang vật lý trị liệu');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `paymentitems`
--

DROP TABLE IF EXISTS `paymentitems`;
CREATE TABLE IF NOT EXISTS `paymentitems` (
  `item_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_id` int UNSIGNED NOT NULL,
  `item_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int UNSIGNED NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `paymentitems_payment_id_foreign` (`payment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `appointment_id` int UNSIGNED NOT NULL,
  `insurance_id` int UNSIGNED DEFAULT NULL,
  `membership_id` int UNSIGNED DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(10,2) DEFAULT NULL,
  `method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chưa thanh toán',
  `transaction_ref` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `payments_appointment_id_unique` (`appointment_id`),
  KEY `payments_insurance_id_foreign` (`insurance_id`),
  KEY `payments_membership_id_foreign` (`membership_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `review_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `appointment_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `doctor_id` int UNSIGNED NOT NULL,
  `rating` tinyint UNSIGNED DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `doctor_reply` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `doctor_reply_updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `reviews_appointment_id_unique` (`appointment_id`),
  KEY `reviews_user_id_foreign` (`user_id`),
  KEY `reviews_doctor_id_foreign` (`doctor_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`review_id`, `appointment_id`, `user_id`, `doctor_id`, `rating`, `comment`, `doctor_reply`, `doctor_reply_updated_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 5, 'oke', 'oke', NULL, '2026-04-23 12:19:37', NULL),
(2, 25, 4, 2, 5, 'Bác sĩ rất tận tâm, giải thích rõ ràng từng bước điều trị.', NULL, NULL, '2026-05-06 14:00:00', NULL),
(3, 26, 2, 2, 4, 'Khám nhanh, tuy nhiên phải chờ hơi lâu.', NULL, NULL, '2026-05-06 15:00:00', NULL),
(6, 30, 4, 7, 4, 'bình luận mới 13123123123', 'oke', NULL, '2026-05-07 16:41:18', NULL),
(5, 29, 4, 5, 5, 'ádasdluậoadoaksjldjasd', 'ok', NULL, '2026-05-07 05:46:42', NULL),
(7, 31, 21, 10, 5, 'sfsdf', NULL, '2026-05-07 00:37:31', '2026-05-07 16:56:06', NULL),
(8, 32, 21, 6, 5, 'ânjnjana 111111111', NULL, NULL, '2026-05-07 17:01:56', '2026-05-07 17:26:28'),
(9, 28, 4, 11, 5, 'asdasd', NULL, NULL, '2026-05-07 17:10:47', NULL),
(10, 33, 22, 7, 5, 'dddddwaa', NULL, NULL, '2026-05-08 01:57:45', '2026-05-08 03:11:52');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `roles_role_name_unique` (`role_name`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'Admin'),
(2, 'Bác sĩ'),
(3, 'Bệnh nhân'),
(4, 'Lễ tân'),
(5, 'Dược sĩ');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `rooms`
--

DROP TABLE IF EXISTS `rooms`;
CREATE TABLE IF NOT EXISTS `rooms` (
  `room_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `room_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department_id` int UNSIGNED DEFAULT NULL,
  `room_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Trống',
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`room_id`),
  UNIQUE KEY `rooms_room_code_unique` (`room_code`),
  KEY `rooms_department_id_foreign` (`department_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_code`, `room_name`, `department_id`, `room_type`, `status`, `notes`) VALUES
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
(12, 'P602', 'Phòng khám Nội Tiết', 12, 'Khám bệnh', 'Hoạt động', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `serviceprices`
--

DROP TABLE IF EXISTS `serviceprices`;
CREATE TABLE IF NOT EXISTS `serviceprices` (
  `price_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_id` int UNSIGNED NOT NULL,
  `price_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `effective_date` date NOT NULL DEFAULT (curdate()),
  `end_date` date DEFAULT NULL,
  `created_by` int UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`price_id`),
  KEY `serviceprices_service_id_foreign` (`service_id`),
  KEY `serviceprices_created_by_foreign` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `serviceprices`
--

INSERT INTO `serviceprices` (`price_id`, `service_id`, `price_type`, `price`, `effective_date`, `end_date`, `created_by`, `created_at`) VALUES
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
(19, 7, 'BHYT', 40000.00, '2025-01-01', NULL, NULL, '2026-05-06 21:19:13');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `service_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_id` int UNSIGNED DEFAULT NULL,
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration_minutes` int UNSIGNED NOT NULL DEFAULT '30',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`service_id`),
  UNIQUE KEY `services_service_code_unique` (`service_code`),
  KEY `services_department_id_foreign` (`department_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `services`
--

INSERT INTO `services` (`service_id`, `service_code`, `service_name`, `department_id`, `description`, `duration_minutes`, `status`) VALUES
(1, '1', 'aa', 1, 'aa', 30, 1),
(2, 'TM01', 'Điện tâm đồ (ECG)', 1, 'Ghi lại hoạt động điện của tim', 20, 1),
(3, 'TM02', 'Siêu âm tim', 1, 'Đánh giá cấu trúc và chức năng tim', 30, 1),
(4, 'PS01', 'Siêu âm thai', 2, 'Theo dõi sự phát triển thai nhi', 30, 1),
(5, 'PS02', 'Xét nghiệm Pap smear', 2, 'Tầm soát ung thư cổ tử cung', 15, 1),
(6, 'NT01', 'Xét nghiệm máu tổng quát', 3, 'CBC, sinh hoá máu cơ bản', 15, 1),
(7, 'NT02', 'Chụp X-quang ngực', 3, 'Đánh giá phổi và tim', 15, 1),
(8, 'NG01', 'Nội soi dạ dày', 4, 'Chẩn đoán bệnh lý dạ dày', 30, 1),
(9, 'NHI01', 'Khám sức khoẻ trẻ em', 5, 'Kiểm tra tổng quát và tiêm chủng', 30, 1),
(10, 'DL01', 'Điều trị mụn laser', 6, 'Laser điều trị mụn và sẹo', 45, 1),
(11, 'MAT01', 'Đo khúc xạ mắt', 7, 'Kiểm tra thị lực và độ cận/viễn', 20, 1),
(12, 'TMH01', 'Nội soi tai mũi họng', 8, 'Chẩn đoán bệnh lý TMH', 20, 1),
(13, 'TK01', 'Chụp MRI não', 9, 'Chẩn đoán hình ảnh bệnh lý thần kinh', 45, 1),
(14, 'CXK01', 'Chụp X-quang xương khớp', 10, 'Đánh giá tổn thương xương và khớp', 15, 1),
(15, 'TH01', 'Nội soi đại tràng', 11, 'Tầm soát ung thư đại trực tràng', 45, 1),
(16, 'NOI01', 'Xét nghiệm đường huyết HbA1c', 12, 'Kiểm soát đái tháo đường dài hạn', 15, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tiensu`
--

DROP TABLE IF EXISTS `tiensu`;
CREATE TABLE IF NOT EXISTS `tiensu` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `blood_group` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `yeuto_rh` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `height` double DEFAULT NULL,
  `weight` double DEFAULT NULL,
  `bmi` double DEFAULT NULL,
  `food_allergies` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `drug_allergies` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `chronic_diseases` json DEFAULT NULL,
  `other_chronic_diseases` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tiensu_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tiensu`
--

INSERT INTO `tiensu` (`id`, `user_id`, `blood_group`, `yeuto_rh`, `height`, `weight`, `bmi`, `food_allergies`, `drug_allergies`, `chronic_diseases`, `other_chronic_diseases`, `created_at`, `updated_at`) VALUES
(1, 3, 'O-', 'positive', 176, 43, 13.88, 'aaa', 'aaa', '[\"TĂNG HUYẾT ÁP\"]', NULL, '2026-04-23 06:49:56', '2026-04-23 06:49:56');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `treatmentreminders`
--

DROP TABLE IF EXISTS `treatmentreminders`;
CREATE TABLE IF NOT EXISTS `treatmentreminders` (
  `reminder_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `record_id` int UNSIGNED DEFAULT NULL,
  `reminder_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remind_at` datetime NOT NULL,
  `message` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_sent` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`reminder_id`),
  KEY `treatmentreminders_user_id_foreign` (`user_id`),
  KEY `treatmentreminders_record_id_foreign` (`record_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_id` int UNSIGNED NOT NULL,
  `avatar_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_foreign` (`role_id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `phone`, `address`, `date_of_birth`, `gender`, `role_id`, `avatar_url`, `status`, `created_at`) VALUES
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
(11, 'BS. Nguyễn Thị Thu', 'thu.bs@hospital.vn', '$2y$12$wa5hS.P1b..AgnGHh2RkZOsorAyjQBkG14cX4P.yW3sIoJ6mnZ7VC', '0901111111', 'Bệnh viện Đa khoa Trung tâm', '1975-04-20', 'Nữ', 2, NULL, 1, '2025-06-01 07:00:00'),
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
(24, 'Tú Huỳnh', 'tuh225095@gmail.com', '$2y$12$GsW2E6.alOYgVYOcioUrxenKya4KyRe1FtP4WnLahSJif/dJ8Y1JC', '1234567890', 'tuh225095@gmail.com', '2026-05-06', 'Nam', 3, NULL, 1, '2026-05-13 23:56:47');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vaccinationrecords`
--

DROP TABLE IF EXISTS `vaccinationrecords`;
CREATE TABLE IF NOT EXISTS `vaccinationrecords` (
  `vaccination_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `vaccine_id` int UNSIGNED NOT NULL,
  `doctor_id` int UNSIGNED DEFAULT NULL,
  `dose_number` int UNSIGNED NOT NULL DEFAULT '1',
  `administered_at` datetime DEFAULT NULL,
  `batch_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `next_dose_date` date DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chưa tiêm',
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`vaccination_id`),
  KEY `vaccinationrecords_user_id_foreign` (`user_id`),
  KEY `vaccinationrecords_vaccine_id_foreign` (`vaccine_id`),
  KEY `vaccinationrecords_doctor_id_foreign` (`doctor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vaccines`
--

DROP TABLE IF EXISTS `vaccines`;
CREATE TABLE IF NOT EXISTS `vaccines` (
  `vaccine_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `vaccine_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manufacturer` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doses_required` int UNSIGNED NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`vaccine_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `vaccines`
--

INSERT INTO `vaccines` (`vaccine_id`, `vaccine_name`, `description`, `manufacturer`, `doses_required`, `status`) VALUES
(1, 'Cúm mùa', 'Phòng ngừa cúm mùa hàng năm', 'Sanofi', 1, 1),
(2, 'Viêm gan B', 'Phòng ngừa viêm gan B', 'GSK', 3, 1),
(3, 'HPV (Gardasil 9)', 'Phòng ngừa ung thư cổ tử cung', 'MSD', 3, 1),
(4, 'Sởi – Quai bị – Rubella', 'Vaccine phòng 3 bệnh cùng lúc', 'Merck', 2, 1),
(5, 'Thủy đậu', 'Phòng ngừa bệnh thủy đậu', 'GlaxoSmithKline', 2, 1),
(6, 'Viêm não Nhật Bản', 'Phòng ngừa viêm não Nhật Bản', 'Vabiotech', 3, 1),
(7, 'COVID-19 (Pfizer)', 'Vaccine phòng COVID-19 mRNA', 'Pfizer-BioNTech', 2, 1),
(8, 'Uốn ván – Bạch hầu', 'Phòng uốn ván và bạch hầu', 'Pasteur', 1, 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
