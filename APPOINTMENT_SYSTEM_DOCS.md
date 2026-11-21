# Hệ Thống Đặt Lịch Khám - Tài Liệu Kỹ Thuật

## 📋 Tổng Quan

Hệ thống quản lý đặt lịch khám bệnh cho bệnh nhân với các tính năng:
- ✅ Đặt lịch khám mới
- ✅ Hủy lịch khám
- ✅ Dời lịch khám
- ✅ Xem danh sách lịch khám
- ✅ Thông báo qua email tự động

---

## 🏗️ Kiến Trúc Hệ Thống

### Controller: `AppointmentController`
**Vị trí**: `app/Http/Controllers/AppointmentController.php`

#### Các Phương Thức Chính:

1. **`create()`** - Hiển thị form đặt lịch
   - Route: `GET /dat-lich`
   - Lấy danh sách: khoa, bác sĩ, dịch vụ, lịch làm việc
   - Return: View `appointments.create`

2. **`store(Request $request)`** - Lưu lịch khám mới
   - Route: `POST /dat-lich`
   - Validation: schedule_id, work_date, appointment_time
   - Kiểm tra: slot còn trống, bệnh nhân chưa đặt, bác sĩ hoạt động
   - Action: Insert/Update appointment, gửi email
   - Return: Redirect to appointments.index

3. **`index(Request $request)`** - Danh sách lịch khám
   - Route: `GET /lich-hen`
   - Hỗ trợ lọc: status, sort
   - Join: appointments + schedules + doctors + departments
   - Pagination: 8 items/page
   - Return: View `appointments.index` với $appointments, $counts, $status

4. **`edit($id)`** - Form dời lịch
   - Route: `GET /lich-hen/{id}/doi`
   - Check: appointment ownership, status, 2-hour rule
   - Lấy available schedules từ doctor và date range
   - Return: View `appointments.edit`

5. **`update(Request $request, $id)`** - Cập nhật lịch khám
   - Route: `PUT /lich-hen/{id}/doi`
   - Validation: new_schedule_id, new_appointment_time
   - Check: slot availability, doctor schedule, time rule
   - Update: appointment record, queue_number
   - Action: Gửi email thông báo
   - Return: Redirect to appointments.index

6. **`cancel(Request $request, $id)`** - Hủy lịch khám
   - Route: `POST /lich-hen/{id}/huy`
   - Check: appointment status, 2-hour rule
   - Update: status = 'Đã hủy', cancel_reason
   - Action: Insert notification, gửi email
   - Return: Redirect to appointments.index

7. **`getSchedules(Request $request)`** - AJAX lấy khung giờ
   - Route: `GET /dat-lich/schedules`
   - Params: doctor_id, work_date
   - Return: JSON {schedules: [...]}

---

## 📊 Database Schema

### Appointments Table
```sql
- appointment_id (PK)
- user_id (FK) - Bệnh nhân
- schedule_id (FK) - Lịch bác sĩ
- service_id (FK) - Dịch vụ (nullable)
- appointment_time (datetime) - Giờ khám
- queue_number (int) - Số thứ tự
- status (enum) - Chờ xác nhận | Đã xác nhận | Đang khám | Hoàn thành | Đã hủy
- note (text) - Ghi chú triệu chứng
- cancel_reason (text) - Lý do hủy
- slot_hold_expire (datetime) - Hết hạn giữ slot
- rescheduled_from (int) - Dời từ schedule cũ
- created_at (timestamp)
```

### DoctorSchedules Table
```sql
- schedule_id (PK)
- doctor_id (FK)
- room_id (FK)
- work_date (date)
- start_time (time) - Giờ bắt đầu
- end_time (time) - Giờ kết thúc
- slot_duration (int) - Thời lượng/slot (phút)
- max_slot (int) - Số slot tối đa
- status (enum) - Hoạt động | Đã khóa
```

### DoctorDaysOff Table
```sql
- day_off_id (PK)
- doctor_id (FK)
- off_date (date)
- reason (text)
```

---

## 🔐 Xử Lý Race Condition

### Vấn Đề:
Hai bệnh nhân cùng lúc đặt lịch slot cuối cùng → Double booking

### Giải Pháp:
1. **Database Transaction**: 
   ```php
   DB::beginTransaction();
   // Check booked count
   // Insert/Update
   DB::commit();
   ```

2. **Count Validation**: 
   ```php
   $booked = DB::table('appointments')
       ->where('schedule_id', $request->schedule_id)
       ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot'])
       ->count();
   
   if ($booked >= $schedule->max_slot) {
       // Reject booking
   }
   ```

3. **Status Filtering**: 
   Chỉ count appointments ở trạng thái valid:
   - ❌ Đã hủy
   - ❌ Dời lịch
   - ❌ Giữ slot (reserved)

---

## ✉️ Email Notifications

### Mail Classes
1. **AppointmentConfirmed** - Xác nhận đặt lịch
2. **AppointmentCancelled** - Xác nhận hủy lịch
3. **AppointmentRescheduled** - Xác nhận dời lịch

### Templates
- `mail/appointment-confirmed.blade.php`
- `mail/appointment-cancelled.blade.php`
- `mail/appointment-rescheduled.blade.php`

### Usage
```php
Mail::to($user->email)->send(
    new AppointmentConfirmed($user, $appointment)
);
```

---

## 🎨 Views

### 1. `appointments.create` - Đặt Lịch Khám
**URL**: `/dat-lich`

**Features**:
- Chọn khoa → Lấy danh sách bác sĩ
- Chọn bác sĩ → Lấy lịch làm việc (AJAX)
- Chọn ngày + khung giờ
- Thêm ghi chú triệu chứng
- Live summary panel
- Doctor profile card

**JS Functions**:
- `onDeptChange()` - Cập nhật danh sách bác sĩ
- `onDoctorChange()` - Tải khung giờ
- `onDateChange()` - Cập nhật khung giờ
- `loadSlots()` - Fetch AJAX
- `selectSlot(el)` - Chọn slot
- `updateSummary()` - Cập nhật tóm tắt

### 2. `appointments.index` - Danh Sách Lịch Khám
**URL**: `/lich-hen`

**Features**:
- Bảng danh sách appointments
- Filter tabs: Tất cả | Sắp tới | Hoàn thành | Đã hủy
- Hiển thị: Bác sĩ | Dịch vụ | Ngày | Trạng thái | Thao tác
- Action buttons: Dời lịch | Hủy (điều kiện)
- Modal xác nhận hủy với field ghi chú

**Trạng thái Badge**:
- **Chờ xác nhận** - Badge vàng
- **Đã xác nhận** - Badge xanh
- **Hoàn thành** - Badge xám
- **Đã hủy** - Badge đỏ

### 3. `appointments.edit` - Dời Lịch Khám
**URL**: `/lich-hen/{id}/doi`

**Features**:
- Hiển thị thông tin lịch cũ (read-only)
- Danh sách available schedules (radio buttons)
- Field ghi chú lý do dời
- Auto-select nếu chỉ 1 lịch
- Disabled nếu không có lịch trống

---

## 🔑 Quy Tắc Kinh Doanh

### Đặt Lịch Khám
- ✅ Chỉ bệnh nhân đã đăng nhập
- ✅ Phải chọn đủ: Khoa, Bác sĩ, Ngày, Giờ
- ✅ Ngày khám từ hôm nay trở đi
- ✅ Slot phải còn trống (booked < max_slot)
- ✅ Bác sĩ không được trong DayOff
- ❌ Bệnh nhân không được đặt 2 lần cùng schedule

### Hủy Lịch Khám
- ✅ Status phải là 'Chờ xác nhận' hoặc 'Đã xác nhận'
- ✅ Phải hủy trước giờ khám **ít nhất 2 tiếng**
- ✅ Có thể thêm lý do hủy (optional)
- ❌ Không được hủy nếu đã khám (status = 'Đã khám')

### Dời Lịch Khám
- ✅ Giống quy tắc hủy (2-hour rule)
- ✅ Slot mới phải còn trống
- ✅ Bác sĩ phải có lịch làm việc
- ✅ Ngày dời phải trong tương lai
- ❌ Không được dời lịch cùng bác sĩ

---

## 📈 Time Validation Logic

```php
// Tính khoảng cách từ giờ khám đến hiện tại
$appointmentTime = Carbon::parse($schedule->work_date . ' ' . $schedule->start_time);
$hoursUntilAppointment = $appointmentTime->diffInHours(now(), false);

// false = không absolute, có thể âm
// Nếu $hours > -2, tức khoảng cách < 2 giờ → Không được hủy/dời
if ($hoursUntilAppointment > -2) {
    // Error: Chỉ có thể hủy/dời trước giờ khám ít nhất 2 tiếng
}
```

---

## 🚀 Routes Configuration

```php
// File: routes/web.php
Route::middleware('auth')->group(function () {
    
    // Booking
    Route::get('/dat-lich', [AppointmentController::class, 'create'])
        ->name('appointments.create');
    Route::post('/dat-lich', [AppointmentController::class, 'store'])
        ->name('appointments.store');
    
    // AJAX
    Route::get('/dat-lich/schedules', [AppointmentController::class, 'getSchedules'])
        ->name('appointments.schedules');
    
    // List
    Route::get('/lich-hen', [AppointmentController::class, 'index'])
        ->name('appointments.index');
    
    // Reschedule
    Route::get('/lich-hen/{id}/doi', [AppointmentController::class, 'edit'])
        ->name('appointments.edit');
    Route::put('/lich-hen/{id}/doi', [AppointmentController::class, 'update'])
        ->name('appointments.update');
    
    // Cancel
    Route::post('/lich-hen/{id}/huy', [AppointmentController::class, 'cancel'])
        ->name('appointments.cancel');
});
```

---

## 📝 Error Handling

### Validation Errors
- Missing required fields → Blade displays $errors
- Invalid schedule_id → "Khung giờ không hợp lệ"
- Date validation → "Ngày khám phải từ hôm nay trở đi"

### Business Logic Errors
- Slot full → "Khung giờ này đã hết chỗ"
- Doctor day off → "Bác sĩ nghỉ ngày này"
- 2-hour rule violation → "Chỉ có thể hủy/dời trước giờ khám ít nhất 2 tiếng"
- Wrong appointment owner → "Không tìm thấy lịch hẹn"

### Database Errors
- Transaction fails → Rollback + Error message
- Email fails → Log warning (non-blocking)

---

## 🧪 Testing Checklist

### Đặt Lịch (Booking)
- [ ] Chọn khoa → Bác sĩ list populate
- [ ] Chọn bác sĩ → AJAX lấy schedules
- [ ] Chọn ngày → Slots update
- [ ] Click slot → Slot highlight, hidden field fill
- [ ] Submit → DB insert, email sent
- [ ] Redirect → Thấy success message

### Danh Sách (List)
- [ ] Load list → Hiển thị tất cả appointments
- [ ] Filter tabs → Danh sách filter đúng
- [ ] Pagination → 8 items/page
- [ ] Action buttons → Hiển thị/ẩn theo status

### Hủy (Cancel)
- [ ] Click Hủy → Modal open
- [ ] Input reason → Field capture
- [ ] Submit → Status = 'Đã hủy', email sent
- [ ] 2-hour rule → Error if within 2 hours

### Dời (Reschedule)
- [ ] Click Dời lịch → Form load
- [ ] Select new schedule → Radio select, time fill
- [ ] Submit → Status = 'Chờ xác nhận', email sent
- [ ] Check validation → Error if slot full

---

## 🔧 Configuration

### .env Settings
```
MAIL_DRIVER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@hospitalbooking.com
MAIL_FROM_NAME="HospitalBooking"
```

### Config (app/config/mail.php)
```php
'from' => [
    'address' => env('MAIL_FROM_ADDRESS', 'noreply@hospitalbooking.com'),
    'name' => env('MAIL_FROM_NAME', 'HospitalBooking'),
],
```

---

## 📚 Dependencies

- Laravel 10+
- Carbon (datetime handling)
- Blade (templating)
- Mail (SMTP)

---

## 🎯 Future Enhancements

- [ ] SMS notifications
- [ ] Appointment reminders (1 day before)
- [ ] Doctor calendar view
- [ ] Bulk appointment management
- [ ] Appointment history export
- [ ] Patient feedback after appointment
- [ ] Multi-language support
- [ ] Mobile app integration

---

**Last Updated**: April 2026
**Version**: 1.0
