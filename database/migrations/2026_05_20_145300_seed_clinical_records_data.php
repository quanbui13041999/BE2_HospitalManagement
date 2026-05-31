<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::connection()->getPdo()->exec('SET FOREIGN_KEY_CHECKS=0');

        try {
            // 1. BẢNG medicalrecords (bảng cũ – appointment-based)
            DB::statement("
                INSERT IGNORE INTO `medicalrecords`
                  (`appointment_id`, `user_id`, `doctor_id`, `diagnosis`, `prescription`, `follow_up_date`, `notes`, `created_at`)
                VALUES
                (29, 4, 5,
                 'Viêm họng cấp, amidan sưng độ 1. Không có biến chứng.',
                 'Amoxicillin 500mg x 3 viên/ngày x 7 ngày; Paracetamol 500mg khi sốt; Vitamin C 1000mg x 1 viên/ngày x 14 ngày',
                 '2026-05-21',
                 'Bệnh nhân cần uống nhiều nước, nghỉ ngơi, tái khám nếu sốt cao không hạ sau 48h.',
                 '2026-05-07 15:10:00'),

                (30, 4, 7,
                 'Viêm kết mạc dị ứng hai mắt. Thị lực bình thường.',
                 'Cetirizine 10mg x 1 viên/ngày x 7 ngày; Nhỏ mắt Cromoglicate 2% x 3 lần/ngày x 10 ngày',
                 '2026-05-21',
                 'Tránh tiếp xúc với khói bụi, không dụi mắt. Đeo kính bảo hộ khi ra đường.',
                 '2026-05-07 09:25:00'),

                (31, 21, 7,
                 'Cận thị độ cao 4.5 diop mắt phải, 4.0 diop mắt trái. Không có tổn thương võng mạc.',
                 'Đề xuất đo kính lại, xem xét phẫu thuật LASIK. Nhỏ mắt nhân tạo Hypromellose x 4 lần/ngày.',
                 '2026-06-07',
                 'Bệnh nhân được tư vấn phẫu thuật khúc xạ. Hẹn tái khám để đánh giá giác mạc.',
                 '2026-05-07 09:30:00'),

                (32, 21, 6,
                 'Mụn trứng cá độ 2 vùng mặt và cổ. Có sẹo thâm nhẹ.',
                 'Clindamycin gel 1% bôi buổi tối x 30 ngày; Benzoyl peroxide 5% bôi sáng x 30 ngày; Vitamin C 1000mg x 1 viên/ngày',
                 '2026-06-15',
                 'Không nặn mụn, rửa mặt 2 lần/ngày, dùng kem chống nắng SPF 50+.',
                 '2026-05-15 14:20:00'),

                (33, 22, 7,
                 'Khô mắt độ nhẹ, mỏi mắt do sử dụng màn hình máy tính nhiều. Áp lực mắt bình thường 14mmHg.',
                 'Nước mắt nhân tạo Systane Ultra x 4 lần/ngày; Omega-3 bổ sung x 2 viên/ngày x 30 ngày',
                 '2026-06-08',
                 'Áp dụng quy tắc 20-20-20: cứ 20 phút nhìn xa 20 giây. Giảm thời gian dùng thiết bị điện tử.',
                 '2026-05-08 09:05:00'),

                (35, 23, 7,
                 'Loạn thị nhẹ mắt phải 0.75 diop. Kết mạc bình thường. Không viêm nhiễm.',
                 'Kính điều chỉnh loạn thị, nhỏ mắt dưỡng Artelac x 3 lần/ngày x 14 ngày',
                 '2026-07-13',
                 'Tái khám sau 2 tháng để kiểm tra tiến triển loạn thị.',
                 '2026-05-13 13:45:00');
            ");

            // 2. BẢNG medical_records (bảng mới – chi tiết hơn)
            DB::statement("
                INSERT IGNORE INTO `medical_records`
                  (`record_id`, `record_code`, `patient_id`, `patient_name`, `patient_code`, `doctor_id`, `doctor_name`,
                   `appointment_id`, `exam_date`, `exam_time`, `visit_type`, `chief_complaint`, `status`, `status_note`,
                   `created_at`, `updated_at`)
                VALUES
                (2, 'CK-2026-0002', 5, 'Trần Thị Mai', 'TR2026010001', 12, 'Đặng Thị Hằng',
                 NULL, '2026-01-20', '09:00:00', 'Kham moi',
                 'Mệt mỏi kéo dài, khát nước nhiều, tiểu nhiều lần trong ngày.',
                 'completed', NULL, '2026-01-20 09:00:00', '2026-01-20 10:30:00'),

                (3, 'CK-2026-0003', 6, 'Nguyễn Văn Bình', 'NG2026012001', 3, 'Nguyễn Thị Thu',
                 NULL, '2026-02-05', '10:30:00', 'Tai kham',
                 'Đau thượng vị sau ăn, ợ chua, buồn nôn tái phát.',
                 'completed', NULL, '2026-02-05 10:30:00', '2026-02-05 11:45:00'),

                (4, 'CK-2026-0004', 7, 'Lê Thị Hoa', 'LE2026015001', 5, 'Lê Thị Phương',
                 NULL, '2026-02-18', '08:30:00', 'Kham moi',
                 'Ho khan, khó thở nhẹ khi gắng sức, tiền sử hen phế quản.',
                 'completed', NULL, '2026-02-18 08:30:00', '2026-02-18 09:30:00'),

                (5, 'CK-2026-0005', 8, 'Phạm Minh Tuấn', 'PH2026018001', 10, 'Ngô Thị Bích',
                 NULL, '2026-03-01', '14:00:00', 'Tai kham',
                 'Đau khớp ngón tay và ngón chân, sưng đỏ, uric acid cao.',
                 'completed', NULL, '2026-03-01 14:00:00', '2026-03-01 15:00:00'),

                (6, 'CK-2026-0006', 9, 'Hoàng Thị Lan', 'HO2026020001', 11, 'Lý Văn Thành',
                 NULL, '2026-03-15', '09:00:00', 'Kham moi',
                 'Ợ nóng sau bữa ăn, đau rát vùng ngực dưới, triệu chứng tái phát.',
                 'completed', NULL, '2026-03-15 09:00:00', '2026-03-15 10:15:00'),

                (7, 'CK-2026-0007', 10, 'Đỗ Văn Hùng', 'DO2026022001', 10, 'Ngô Thị Bích',
                 NULL, '2026-04-01', '13:30:00', 'Tai kham',
                 'Đau lưng dưới lan xuống chân phải, tê bì ngón chân, tiền sử thoát vị L4-L5.',
                 'completed', NULL, '2026-04-01 13:30:00', '2026-04-01 14:45:00'),

                (8, 'CK-2026-0008', 1, 'aaa', 'AA2026042001', 3, 'Nguyễn Thị Thu',
                 NULL, '2026-04-23', '08:00:00', 'Kham moi',
                 'Khám tổng quát định kỳ, không có triệu chứng đặc biệt.',
                 'completed', NULL, '2026-04-23 08:00:00', '2026-04-23 08:45:00'),

                (9, 'CK-2026-0009', 2, 'bbb', 'BB2026042301', 4, 'Trần Văn Minh',
                 NULL, '2026-04-23', '13:30:00', 'Kham moi',
                 'Đau bụng phải âm ỉ, đặc biệt sau khi ăn nhiều dầu mỡ.',
                 'completed', NULL, '2026-04-23 13:30:00', '2026-04-23 14:15:00'),

                (10, 'CK-2026-0010', 3, 'aaa', 'AA2026042302', 8, 'Vũ Thị Ngọc',
                 NULL, '2026-04-23', '09:00:00', 'Kham moi',
                 'Nghẹt mũi kéo dài 2 tuần, chảy nước mũi trong, hắt hơi nhiều.',
                 'completed', NULL, '2026-04-23 09:00:00', '2026-04-23 09:40:00'),

                (11, 'CK-2026-0011', 4, 'bbb', 'BB2026050701', 5, 'Lê Thị Phương',
                 29, '2026-05-07', '14:30:00', 'Kham moi',
                 'Đau họng 3 ngày, sốt nhẹ 37.8°C, khó nuốt.',
                 'completed', NULL, '2026-05-07 14:30:00', '2026-05-07 15:10:00'),

                (12, 'CK-2026-0012', 21, 'ádsad', 'AD2026050701', 7, 'Hoàng Văn Tùng',
                 31, '2026-05-07', '09:00:00', 'Kham moi',
                 'Mờ mắt khi nhìn xa, nhức đầu sau khi đọc sách.',
                 'completed', NULL, '2026-05-07 09:00:00', '2026-05-07 09:35:00'),

                (13, 'CK-2026-0013', 24, 'Tú Huỳnh', 'TU2026051301', 3, 'Nguyễn Thị Thu',
                 NULL, '2026-05-13', '10:00:00', 'Kham moi',
                 'Mệt mỏi, hoa mắt chóng mặt khi đứng dậy đột ngột.',
                 'completed', NULL, '2026-05-13 10:00:00', '2026-05-13 10:50:00');
            ");

            // 3. BẢNG vital_signs – chỉ số sinh tồn
            DB::statement("
                INSERT IGNORE INTO `vital_signs`
                  (`vital_id`, `record_id`, `blood_pressure`, `bp_status`, `heart_rate`, `hr_status`,
                   `temperature`, `temp_status`, `spo2`, `spo2_status`, `weight`, `blood_sugar`, `sugar_status`,
                   `created_at`, `updated_at`)
                VALUES
                (2,  2,  '130/85', 'high',   78.0, 'normal', 36.7, 'normal', 98.0, 'normal', 58.0,  7.80, 'high', NOW(), NOW()),
                (3,  3,  '125/80', 'normal', 80.0, 'normal', 36.5, 'normal', 97.0, 'normal', 72.0,  5.20, 'normal', NOW(), NOW()),
                (4,  4,  '110/70', 'normal', 88.0, 'normal', 36.8, 'normal', 96.0, 'normal', 52.0,  5.00, 'normal', NOW(), NOW()),
                (5,  5,  '128/82', 'normal', 76.0, 'normal', 36.6, 'normal', 98.0, 'normal', 80.0,  5.50, 'normal', NOW(), NOW()),
                (6,  6,  '118/75', 'normal', 72.0, 'normal', 36.5, 'normal', 99.0, 'normal', 55.0,  5.10, 'normal', NOW(), NOW()),
                (7,  7,  '135/90', 'high',   82.0, 'normal', 36.9, 'normal', 97.0, 'normal', 78.0,  5.40, 'normal', NOW(), NOW()),
                (8,  8,  '120/80', 'normal', 75.0, 'normal', 36.6, 'normal', 98.0, 'normal', 65.0,  5.00, 'normal', NOW(), NOW()),
                (9,  9,  '122/78', 'normal', 79.0, 'normal', 36.7, 'normal', 98.0, 'normal', 70.0,  5.20, 'normal', NOW(), NOW()),
                (10, 10, '115/72', 'normal', 74.0, 'normal', 36.5, 'normal', 99.0, 'normal', 60.0,  4.90, 'normal', NOW(), NOW()),
                (11, 11, '110/68', 'normal', 92.0, 'normal', 37.8, 'high',   97.0, 'normal', 62.0,  5.10, 'normal', NOW(), NOW()),
                (12, 12, '118/76', 'normal', 76.0, 'normal', 36.6, 'normal', 98.0, 'normal', 68.0,  5.30, 'normal', NOW(), NOW()),
                (13, 13, '105/65', 'low',    70.0, 'normal', 36.5, 'normal', 99.0, 'normal', 63.0,  4.80, 'normal', NOW(), NOW());
            ");

            // 4. BẢNG prescriptions – đơn thuốc chi tiết
            DB::statement("
                INSERT IGNORE INTO `prescriptions`
                  (`record_id`, `drug_name`, `dosage`, `instructions`, `duration_days`, `quantity`, `unit`, `created_at`, `updated_at`)
                VALUES
                (2, 'Metformin 500mg',    '500mg',  'Uống 1 viên sau bữa ăn sáng và tối', 30, 60,  'viên', '2026-01-20 10:30:00', '2026-01-20 10:30:00'),
                (2, 'Amlodipine 5mg',     '5mg',    'Uống 1 viên vào buổi sáng',          30, 30,  'viên', '2026-01-20 10:30:00', '2026-01-20 10:30:00'),
                (2, 'Vitamin C 1000mg',   '1000mg', 'Uống 1 viên sau bữa ăn',             30, 30,  'viên', '2026-01-20 10:30:00', '2026-01-20 10:30:00'),

                (3, 'Omeprazole 20mg',    '20mg',   'Uống 1 viên trước bữa ăn sáng 30 phút', 28, 28, 'viên', '2026-02-05 11:45:00', '2026-02-05 11:45:00'),
                (3, 'Azithromycin 500mg', '500mg',  'Uống 1 viên/ngày x 5 ngày',          5,  5,   'viên', '2026-02-05 11:45:00', '2026-02-05 11:45:00'),

                (4, 'Prednisolone 5mg',   '5mg',    'Uống 2 viên buổi sáng x 7 ngày, giảm liều dần', 14, 20, 'viên', '2026-02-18 09:30:00', '2026-02-18 09:30:00'),
                (4, 'Cetirizine 10mg',    '10mg',   'Uống 1 viên vào buổi tối',           7,  7,   'viên', '2026-02-18 09:30:00', '2026-02-18 09:30:00'),

                (5, 'Ibuprofen 400mg',    '400mg',  'Uống 1 viên sau ăn khi đau, không quá 3 viên/ngày', 14, 30, 'viên', '2026-03-01 15:00:00', '2026-03-01 15:00:00'),
                (5, 'Paracetamol 500mg',  '500mg',  'Uống 1 viên khi đau, cách nhau ít nhất 6 giờ', 14, 20, 'viên', '2026-03-01 15:00:00', '2026-03-01 15:00:00'),

                (6, 'Omeprazole 20mg',    '20mg',   'Uống 1 viên trước bữa ăn sáng 30 phút', 14, 14, 'viên', '2026-03-15 10:15:00', '2026-03-15 10:15:00'),

                (7, 'Ibuprofen 400mg',    '400mg',  'Uống 1 viên sau ăn, ngày 2 lần',     14, 28, 'viên', '2026-04-01 14:45:00', '2026-04-01 14:45:00'),
                (7, 'Prednisolone 5mg',   '5mg',    'Uống 1 viên buổi sáng x 5 ngày',     5,  5,  'viên', '2026-04-01 14:45:00', '2026-04-01 14:45:00'),

                (8, 'Vitamin C 1000mg',   '1000mg', 'Uống 1 viên/ngày sau bữa ăn',        30, 30, 'viên', '2026-04-23 08:45:00', '2026-04-23 08:45:00'),

                (9, 'Omeprazole 20mg',    '20mg',   'Uống 1 viên trước bữa sáng',         14, 14, 'viên', '2026-04-23 14:15:00', '2026-04-23 14:15:00'),
                (9, 'Paracetamol 500mg',  '500mg',  'Uống khi đau, tối đa 3 viên/ngày',   7,  15, 'viên', '2026-04-23 14:15:00', '2026-04-23 14:15:00'),

                (10, 'Cetirizine 10mg',    '10mg',  'Uống 1 viên/ngày vào buổi tối',      14, 14, 'viên', '2026-04-23 09:40:00', '2026-04-23 09:40:00'),

                (11, 'Amoxicillin 500mg', '500mg',  'Uống 1 viên x 3 lần/ngày x 7 ngày', 7,  21, 'viên', '2026-05-07 15:10:00', '2026-05-07 15:10:00'),
                (11, 'Paracetamol 500mg', '500mg',  'Uống khi sốt trên 38.5°C',           5,  10, 'viên', '2026-05-07 15:10:00', '2026-05-07 15:10:00'),
                (11, 'Vitamin C 1000mg',  '1000mg', 'Uống 1 viên/ngày sau ăn',            14, 14, 'viên', '2026-05-07 15:10:00', '2026-05-07 15:10:00'),

                (12, 'Nước mắt nhân tạo Hypromellose 0.3%', '1-2 giọt/lần', 'Nhỏ 3-4 lần/ngày vào mỗi mắt', 30, 2, 'lọ', '2026-05-07 09:35:00', '2026-05-07 09:35:00'),

                (13, 'Paracetamol 500mg', '500mg',  'Uống khi đau đầu chóng mặt',         7,  10, 'viên', '2026-05-13 10:50:00', '2026-05-13 10:50:00'),
                (13, 'Vitamin C 1000mg',  '1000mg', 'Uống 1 viên/ngày sau ăn x 30 ngày', 30, 30, 'viên', '2026-05-13 10:50:00', '2026-05-13 10:50:00');
            ");

            // 5. BẢNG medical_orders – y lệnh xét nghiệm / chẩn đoán hình ảnh
            DB::statement("
                INSERT IGNORE INTO `medical_orders`
                  (`record_id`, `order_type`, `order_name`, `description`, `result_status`, `result_note`, `created_at`, `updated_at`)
                VALUES
                (2, 'lab',     'Xét nghiệm HbA1c',           'Đánh giá kiểm soát đường huyết dài hạn',    'Có kết quả', 'HbA1c: 7.2% – kiểm soát chưa tốt, cần điều chỉnh thuốc',          '2026-01-20 10:30:00', '2026-01-20 10:30:00'),
                (2, 'lab',     'Xét nghiệm đường huyết đói',  'Đo glucose máu sau nhịn ăn 8 giờ',          'Có kết quả', 'Glucose: 8.5 mmol/L – cao hơn bình thường (>7.0)',                  '2026-01-20 10:30:00', '2026-01-20 10:30:00'),

                (3, 'lab',     'Test HP (CLO test)',           'Xét nghiệm vi khuẩn H. pylori',             'Có kết quả', 'CLO test dương tính – cần phác đồ diệt HP',                        '2026-02-05 11:45:00', '2026-02-05 11:45:00'),
                (3, 'imaging', 'Siêu âm ổ bụng tổng quát',    'Đánh giá gan mật lách tụy',                 'Có kết quả', 'Dạ dày có nhiều dịch, niêm mạc dày nhẹ. Gan mật bình thường.',     '2026-02-05 11:45:00', '2026-02-05 11:45:00'),

                (4, 'lab',     'Đo chức năng hô hấp (Spirometry)', 'Đánh giá FEV1/FVC',                   'Có kết quả', 'FEV1/FVC = 68% – tắc nghẽn nhẹ. Khuyến nghị dùng thuốc giãn phế quản khi cần', '2026-02-18 09:30:00', '2026-02-18 09:30:00'),

                (5, 'lab',     'Xét nghiệm acid uric máu',    'Đánh giá mức độ tăng acid uric',            'Có kết quả', 'Acid uric: 520 µmol/L – tăng (bình thường <420)',                  '2026-03-01 15:00:00', '2026-03-01 15:00:00'),
                (5, 'imaging', 'X-quang khớp bàn chân',       'Kiểm tra tổn thương khớp do Gout',          'Có kết quả', 'Hình ảnh đục vôi nhẹ quanh khớp ngón cái bàn chân phải.',          '2026-03-01 15:00:00', '2026-03-01 15:00:00'),

                (7, 'imaging', 'MRI cột sống thắt lưng',       'Đánh giá thoát vị L4-L5',                  'Có kết quả', 'Thoát vị đĩa đệm L4-L5 phải, chèn ép rễ thần kinh S1 nhẹ.',       '2026-04-01 14:45:00', '2026-04-01 14:45:00'),

                (9, 'imaging', 'Siêu âm ổ bụng phải',         'Loại trừ viêm ruột thừa, sỏi mật',         'Có kết quả', 'Túi mật không có sỏi. Không có dấu hiệu viêm ruột thừa. Có nhẹ dịch vùng hố chậu phải.', '2026-04-23 14:15:00', '2026-04-23 14:15:00'),

                (11, 'lab',    'Xét nghiệm công thức máu',     'Đánh giá bạch cầu, phân biệt vi khuẩn/virus', 'Có kết quả', 'Bạch cầu: 11.5 G/L – tăng nhẹ, tỷ lệ Neutrophil 72% – gợi ý nhiễm khuẩn', '2026-05-07 15:10:00', '2026-05-07 15:10:00'),

                (13, 'lab',    'Xét nghiệm tổng phân tích máu', 'Đánh giá thiếu máu, điện giải',           'Có kết quả', 'Hb: 11.8 g/dL – thiếu máu nhẹ. Kali 3.4 mEq/L – giới hạn thấp. Đề xuất bổ sung sắt.', '2026-05-13 10:50:00', '2026-05-13 10:50:00');
            ");

            // 6. BẢNG patientallergies – dị ứng cho các user chưa có
            DB::statement("
                INSERT IGNORE INTO `patientallergies` (`user_id`, `allergen`, `reaction`, `severity`, `noted_date`, `notes`)
                VALUES
                (1,  'Không có dị ứng đã biết', 'Không có',           'Không xác định', '2026-04-23', 'Khám tổng quát, không ghi nhận dị ứng'),
                (2,  'Cá biển',                  'Nổi mề đay, ngứa',   'Vừa',            '2026-04-23', 'Dị ứng hải sản, tránh các loại cá biển'),
                (3,  'Không có dị ứng đã biết', 'Không có',           'Không xác định', '2026-04-23', NULL),
                (4,  'Penicillin',               'Phát ban da',         'Nhẹ',            '2026-05-07', 'Dị ứng nhẹ với kháng sinh nhóm beta-lactam'),
                (21, 'Bụi nhà',                  'Hắt hơi, ngứa mắt', 'Nhẹ',            '2026-05-07', 'Dị ứng bụi mãn tính, đặc biệt buổi sáng'),
                (22, 'Không có dị ứng đã biết', 'Không có',           'Không xác định', '2026-05-08', NULL),
                (23, 'Không có dị ứng đã biết', 'Không có',           'Không xác định', '2026-05-13', NULL),
                (24, 'Sulfonamide',              'Ngứa da, phát ban',  'Vừa',            '2026-05-13', 'Cần báo bác sĩ khi được kê kháng sinh');
            ");

            // 7. BẢNG patientmedicalhistory – tiền sử bệnh cho user chưa có
            DB::statement("
                INSERT IGNORE INTO `patientmedicalhistory` (`user_id`, `condition`, `diagnosed_at`, `treated_at`, `is_chronic`, `notes`)
                VALUES
                (1,  'Không có tiền sử bệnh đặc biệt',    '2026-04-23', NULL,                              0, 'Sức khỏe tổng thể tốt'),
                (2,  'Đau túi mật chức năng',              '2025-03-10', 'Phòng khám đa khoa tư',           0, 'Đã điều trị, không tái phát'),
                (3,  'Viêm mũi dị ứng mãn tính',          '2020-06-01', 'Bệnh viện Tai Mũi Họng TP.HCM',  1, 'Điều trị theo mùa, dùng Cetirizine khi cần'),
                (4,  'Viêm họng tái phát',                 '2024-01-15', 'Phòng khám nhi khoa',             0, 'Tái phát khoảng 2-3 lần/năm'),
                (21, 'Cận thị tiến triển',                 '2022-09-01', 'Trung tâm mắt Sài Gòn',          1, 'Độ cận tăng hàng năm, đang theo dõi'),
                (22, 'Khô mắt do công nghệ',               '2025-11-20', 'Phòng khám nhãn khoa',            0, 'Liên quan đến thói quen dùng màn hình nhiều giờ'),
                (23, 'Loạn thị nhẹ',                       '2026-05-13', 'Bệnh viện Đa khoa Trung tâm',    0, 'Phát hiện lần đầu, chưa điều trị'),
                (24, 'Thiếu máu nhẹ (thiếu sắt)',          '2026-05-13', 'Bệnh viện Đa khoa Trung tâm',    0, 'Hb 11.8 g/dL, đang bổ sung sắt');
            ");

            // 8. BẢNG record_allergies – dị ứng gắn với từng hồ sơ khám
            DB::statement("
                INSERT IGNORE INTO `record_allergies` (`record_id`, `allergen`, `severity`, `reaction`, `created_at`, `updated_at`)
                VALUES
                (2,  'Penicillin',               'Nặng',  'Phát ban toàn thân, ngứa',         '2026-01-20 09:00:00', '2026-01-20 09:00:00'),
                (3,  'Tôm cua',                  'Nặng',  'Nổi mề đay, khó thở',              '2026-02-05 10:30:00', '2026-02-05 10:30:00'),
                (4,  'Aspirin',                  'Vừa',   'Đau dạ dày, xuất huyết nhẹ',       '2026-02-18 08:30:00', '2026-02-18 08:30:00'),
                (5,  'Sulfonamide',              'Nặng',  'Sốc phản vệ nhẹ',                  '2026-03-01 14:00:00', '2026-03-01 14:00:00'),
                (11, 'Penicillin',               'Nhẹ',   'Phát ban da nhẹ',                  '2026-05-07 14:30:00', '2026-05-07 14:30:00');
            ");

        } finally {
            DB::connection()->getPdo()->exec('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::connection()->getPdo()->exec('SET FOREIGN_KEY_CHECKS=0');

        try {
            DB::statement("DELETE FROM `record_allergies` WHERE `record_id` IN (2, 3, 4, 5, 11)");
            DB::statement("DELETE FROM `patientmedicalhistory` WHERE `user_id` IN (1, 2, 3, 4, 21, 22, 23, 24)");
            DB::statement("DELETE FROM `patientallergies` WHERE `user_id` IN (1, 2, 3, 4, 21, 22, 23, 24)");
            DB::statement("DELETE FROM `medical_orders` WHERE `record_id` IN (2, 3, 4, 5, 7, 9, 11, 13)");
            DB::statement("DELETE FROM `prescriptions` WHERE `record_id` IN (2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13)");
            DB::statement("DELETE FROM `vital_signs` WHERE `vital_id` IN (2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13)");
            DB::statement("DELETE FROM `medical_records` WHERE `record_id` IN (2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13)");
            DB::statement("DELETE FROM `medicalrecords` WHERE `appointment_id` IN (29, 30, 31, 32, 33, 35)");
        } finally {
            DB::connection()->getPdo()->exec('SET FOREIGN_KEY_CHECKS=1');
        }
    }
};
