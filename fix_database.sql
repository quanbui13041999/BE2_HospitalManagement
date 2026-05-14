USE `hospitalbookingdb`;

CREATE TABLE IF NOT EXISTS `medical_records` (
  `record_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `record_code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ma phieu VD: CK-2026-0001',
  `patient_id` int UNSIGNED DEFAULT NULL,
  `patient_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `patient_code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ma benh nhan',
  `doctor_id` int UNSIGNED DEFAULT NULL,
  `doctor_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `appointment_id` int UNSIGNED DEFAULT NULL COMMENT 'FK appointments.appointment_id',
  `exam_date` date NOT NULL,
  `exam_time` time DEFAULT NULL,
  `visit_type` enum('Tai kham','Kham moi','Cap cuu','Tái khám','Khám mới','Cấp cứu') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Khám mới',
  `status` enum('pending','examining','completed','prescribed','follow_up','emergency','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `status_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `chief_complaint` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ly do den kham',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `medical_records_record_code_unique` (`record_code`),
  KEY `medical_records_patient_id_index` (`patient_id`),
  KEY `medical_records_doctor_id_index` (`doctor_id`),
  KEY `medical_records_appointment_id_index` (`appointment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ho so benh an moi lan kham';

CREATE TABLE IF NOT EXISTS `vital_signs` (
  `vital_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `record_id` bigint UNSIGNED NOT NULL,
  `blood_pressure` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bp_status` enum('normal','high','low') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `heart_rate` decimal(5,1) DEFAULT NULL,
  `hr_status` enum('normal','high','low') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `temperature` decimal(4,1) DEFAULT NULL,
  `temp_status` enum('normal','high','low') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `spo2` decimal(5,1) DEFAULT NULL,
  `spo2_status` enum('normal','high','low') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `weight` decimal(5,1) DEFAULT NULL,
  `blood_sugar` decimal(5,2) DEFAULT NULL,
  `sugar_status` enum('normal','high','low') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`vital_id`),
  UNIQUE KEY `vital_signs_record_id_unique` (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `diagnoses` (
  `diagnosis_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `record_id` bigint UNSIGNED NOT NULL,
  `diagnosis_name` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icd_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diagnosis_type` enum('primary','secondary','complication') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'primary',
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`diagnosis_id`),
  KEY `diagnoses_record_id_index` (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prescriptions` (
  `prescription_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `record_id` bigint UNSIGNED NOT NULL,
  `drug_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dosage` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instructions` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration_days` int UNSIGNED NOT NULL DEFAULT '30',
  `quantity` int UNSIGNED DEFAULT NULL,
  `unit` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`prescription_id`),
  KEY `prescriptions_record_id_index` (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `medical_orders` (
  `order_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `record_id` bigint UNSIGNED NOT NULL,
  `order_type` enum('lab','imaging','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lab',
  `order_name` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `result_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chờ kết quả',
  `result_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `medical_orders_record_id_index` (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `medical_attachments` (
  `attachment_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `record_id` bigint UNSIGNED NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint UNSIGNED DEFAULT NULL,
  `attachment_category` enum('result','image','document','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'document',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`attachment_id`),
  KEY `medical_attachments_record_id_index` (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `record_allergies` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `record_id` bigint UNSIGNED NOT NULL,
  `allergen` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `severity` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reaction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `record_allergies_record_id_index` (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `migrations` (`migration`, `batch`)
VALUES ('2026_05_08_000001_create_medical_records_tables', 11);
