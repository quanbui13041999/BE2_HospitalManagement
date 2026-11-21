# Luồng Hệ Thống Nhắc Nhở Tuân Thủ Điều Trị

Tài liệu này mô tả luồng chính của chức năng nhắc nhở tuân thủ điều trị: tạo lịch nhắc, gửi email cho bệnh nhân, bệnh nhân xác nhận đã thực hiện và hệ thống tổng hợp báo cáo tuân thủ.

## 1. Các thành phần chính

### Model

- `TreatmentReminder`: lưu lịch nhắc điều trị.
  - Bảng: `treatmentreminders`
  - Khóa chính: `reminder_id`
  - Các trường chính: `user_id`, `record_id`, `reminder_type`, `remind_at`, `message`, `is_sent`
  - Quan hệ:
    - `user`: bệnh nhân nhận nhắc nhở
    - `medicalRecord`: hồ sơ bệnh án liên quan
    - `confirmation`: xác nhận đã thực hiện

- `MedicalRecord`: hồ sơ bệnh án.
  - Trường `patient_id` trỏ tới `users.user_id`
  - Quan hệ `patient` trả về user bệnh nhân.

- `TreatmentConfirmation`: lưu xác nhận bệnh nhân đã uống thuốc hoặc hoàn thành hướng dẫn.
  - Bảng: `treatment_confirmations`
  - Các trường chính: `reminder_id`, `user_id`, `confirmed_at`, `confirm_type`, `note`

- `TreatmentHomeInstruction`: lưu hướng dẫn điều trị tại nhà.
  - Bảng: `treatment_home_instructions`
  - Các trường chính: `record_id`, `user_id`, `instruction_text`, `detail`, `icon`, `sort_order`, `is_active`

- `InstructionDailyCheck`: lưu trạng thái check hằng ngày của hướng dẫn điều trị tại nhà.
  - Bảng: `instruction_daily_checks`
  - Các trường chính: `instruction_id`, `user_id`, `checked_date`, `is_done`, `checked_at`

## 2. Luồng tạo nhắc nhở

Có 2 cách tạo `TreatmentReminder`.

### 2.1. Admin tạo thủ công

1. Admin vào chức năng quản lý nhắc nhở điều trị.
2. Form gửi dữ liệu qua `Admin\TreatmentReminderAdminController@store`.
3. Request được validate bằng `StoreTreatmentReminderRequest`.
4. Hệ thống tạo bản ghi:

```php
TreatmentReminder::create($request->validated());
```

Khi tạo thủ công, `user_id` là bệnh nhân được chọn. Email gửi nhắc sẽ lấy từ user bệnh nhân này nếu reminder không gắn với hồ sơ bệnh án.

### 2.2. Sinh tự động từ hồ sơ bệnh án

1. Admin chọn hồ sơ bệnh án và gọi route generate.
2. `TreatmentReminderAdminController@generateFromRecord` lấy `MedicalRecord`.
3. Gọi `TreatmentReminderService@generateFromRecord($record)`.
4. Service đọc danh sách đơn thuốc trong `$record->prescriptions`.
5. Hệ thống phân tích `instructions` để xác định thời điểm uống thuốc:
   - `sáng` -> `06:00`
   - `trưa` -> `12:00`
   - `chiều` -> `15:00`
   - `tối` -> `18:00`
   - `trước khi ngủ` -> `21:00`
   - Nếu không nhận diện được thì mặc định `08:00`
6. Với mỗi ngày trong `duration_days`, hệ thống tạo `TreatmentReminder`.

Trong luồng này, `user_id` được lấy từ:

```php
$userId = $record->patient_id;
```

Nghĩa là reminder được gắn trực tiếp với bệnh nhân của hồ sơ bệnh án.

## 3. Luồng gửi email nhắc nhở

### 3.1. Scheduler

File `routes/console.php` đăng ký lịch chạy:

```php
Schedule::command('reminders:send')->everyMinute();
```

Khi Laravel scheduler chạy, mỗi phút hệ thống sẽ gọi command:

```bash
php artisan reminders:send
```

Ở môi trường local có thể chạy:

```bash
php artisan schedule:work
```

Ở production nên cấu hình cron gọi `php artisan schedule:run` mỗi phút.

### 3.2. Command gửi mail

Command xử lý tại:

```text
app/Console/Commands/SendTreatmentReminders.php
```

Luồng xử lý:

1. Lấy các reminder chưa gửi:

```php
TreatmentReminder::where('is_sent', 0)
    ->where('remind_at', '<=', now()->addMinutes(5))
    ->with(['user', 'medicalRecord.patient'])
    ->get();
```

2. Xác định bệnh nhân nhận email:

```php
$patient = $reminder->medicalRecord?->patient ?? $reminder->user;
$email = $patient?->email;
```

Ưu tiên lấy email từ bệnh nhân của hồ sơ bệnh án:

```text
TreatmentReminder -> MedicalRecord -> patient -> email
```

Nếu reminder không gắn hồ sơ bệnh án, fallback về:

```text
TreatmentReminder -> user -> email
```

3. Nếu email rỗng hoặc sai định dạng, hệ thống bỏ qua reminder đó và không đánh dấu đã gửi.

4. Nếu email hợp lệ, hệ thống gửi:

```php
Mail::to($email)->send(new TreatmentReminderMail($reminder));
```

5. Sau khi gửi thành công, cập nhật:

```php
$reminder->update(['is_sent' => 1]);
```

## 4. Nội dung email

Mail class:

```text
app/Mail/TreatmentReminderMail.php
```

Template:

```text
resources/views/emails/treatment_reminder.blade.php
```

`TreatmentReminderMail` truyền vào template:

- `$reminder`: thông tin lịch nhắc.
- `$patient`: bệnh nhân nhận email.

Template hiển thị:

- Tên bệnh nhân.
- Nội dung nhắc nhở.
- Thời gian nhắc.
- Nút mở trang lịch trình điều trị.

## 5. Luồng bệnh nhân xem dashboard

Controller:

```text
app/Http/Controllers/Patient/TreatmentReminderController.php
```

Khi bệnh nhân vào trang nhắc nhở:

```php
$data = $this->service->getDashboardData(Auth::id());
```

Service lấy:

- Danh sách reminder hôm nay.
- Danh sách hướng dẫn điều trị tại nhà đang active.
- Thống kê tuân thủ 7 ngày.
- Thống kê tuân thủ trong tháng.
- Lịch tái khám tiếp theo.

View chính:

```text
resources/views/patient/treatment_reminder/index.blade.php
```

## 6. Luồng xác nhận đã uống thuốc

1. Bệnh nhân bấm nút "Đánh dấu đã uống".
2. Frontend gọi:

```text
POST /treatment/confirm/{reminder}
```

3. Controller gọi:

```php
$this->service->confirmReminder($reminder, Auth::id());
```

4. Service kiểm tra reminder thuộc đúng bệnh nhân:

```php
TreatmentReminder::where('reminder_id', $reminderId)
    ->where('user_id', $userId)
    ->firstOrFail();
```

5. Hệ thống tạo `TreatmentConfirmation` nếu chưa có:

```php
TreatmentConfirmation::firstOrCreate([
    'reminder_id' => $reminderId,
    'user_id' => $userId,
], [
    'confirmed_at' => now(),
    'confirm_type' => $reminder->reminder_type === 'medicine' ? 'medicine' : 'instruction',
]);
```

6. Frontend đổi trạng thái dòng reminder thành đã hoàn thành.

## 7. Luồng check hướng dẫn điều trị tại nhà

1. Bệnh nhân tick checkbox hướng dẫn tại nhà.
2. Frontend gọi:

```text
POST /treatment/instruction/toggle
```

3. Controller gọi:

```php
$this->service->toggleInstruction($request->integer('instruction_id'), Auth::id());
```

4. Service kiểm tra hướng dẫn thuộc đúng bệnh nhân.
5. Hệ thống tạo hoặc cập nhật `InstructionDailyCheck` theo ngày hiện tại.
6. Nếu đang chưa hoàn thành thì chuyển thành hoàn thành và set `checked_at`.
7. Nếu đang hoàn thành thì bỏ hoàn thành và xóa `checked_at`.

## 8. Luồng báo cáo tuân thủ

### 8.1. Báo cáo cho bệnh nhân

`TreatmentReminderService` tính:

- `getWeeklyComplianceStats($userId)`: thống kê 7 ngày gần nhất.
- `getMonthComplianceStats($userId)`: thống kê tháng hiện tại.

Dữ liệu được hiển thị tại dashboard bệnh nhân.

### 8.2. Báo cáo cho admin

Service:

```text
app/Services/ComplianceReportService.php
```

Admin xem báo cáo tổng hợp gồm:

- Tổng số bệnh nhân.
- Tổng số reminder trong tháng.
- Tổng số xác nhận đã hoàn thành.
- Tổng số reminder đã gửi mail.
- Tỷ lệ tuân thủ toàn hệ thống.
- Top bệnh nhân tuân thủ cao nhất.
- Top bệnh nhân tuân thủ thấp nhất.

## 9. Trạng thái quan trọng

- `TreatmentReminder.is_sent = 0`: reminder chưa gửi email.
- `TreatmentReminder.is_sent = 1`: reminder đã gửi email thành công.
- `TreatmentConfirmation` tồn tại: bệnh nhân đã xác nhận thực hiện reminder.
- `InstructionDailyCheck.is_done = 1`: bệnh nhân đã hoàn thành hướng dẫn trong ngày.

## 10. Tóm tắt luồng chính

```text
Admin tạo reminder
        |
        v
TreatmentReminder được lưu với user_id bệnh nhân
        |
        v
Laravel Scheduler gọi reminders:send mỗi phút
        |
        v
Command lấy reminder chưa gửi và đã đến thời gian nhắc
        |
        v
Xác định email bệnh nhân
        |
        v
Gửi TreatmentReminderMail tới email bệnh nhân
        |
        v
Cập nhật is_sent = 1
        |
        v
Bệnh nhân vào dashboard và xác nhận đã uống thuốc / hoàn thành
        |
        v
Tạo TreatmentConfirmation hoặc InstructionDailyCheck
        |
        v
Service tổng hợp tỷ lệ tuân thủ cho bệnh nhân và admin
```

