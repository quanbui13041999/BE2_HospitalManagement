# MVC Refactoring Summary - Appointment Management System

## 🎯 Mục tiêu
Tách lớp Database Query sang Service Layer, giữ Controller chỉ xử lý HTTP logic - tuân theo chuẩn MVC

## ✅ Hoàn tất (2026-05-06)

### 1. AppointmentService Created
**File**: `app/Services/AppointmentService.php` (630 dòng)

#### Phương thức chính:
- **Data Retrieval**
  - `getCreateFormData()` - Dữ liệu form đặt lịch (departments, services, doctors, schedules)
  - `getSchedulesForDoctor()` - Lịch khám theo bác sĩ & ngày
  - `getUserAppointmentStats()` - Thống kê lịch user (total, upcoming, completed, cancelled)
  - `getUserAppointments()` - Danh sách lịch với filtering & pagination
  - `getAppointmentForEdit()` - Chi tiết appointment để chỉnh sửa
  - `getAvailableSchedulesForReschedule()` - Lịch khám khác để dời

- **Business Logic**
  - `createAppointment()` - Tạo appointment (validation + transaction + email)
  - `rescheduleAppointment()` - Dời lịch (validation + transaction + email)
  - `cancelAppointment()` - Hủy lịch (validation + transaction + email)

- **Private Helpers**
  - `checkAppointmentAlreadyBooked()` - Kiểm tra appointment đã đặt
  - `validateSchedule()` - Kiểm tra schedule hợp lệ
  - `calculateQueueNumber()` - Tính số thứ tự
  - `calculateAppointmentEndTime()` - Tính giờ kết thúc
  - `checkRescheduleTimeAvailable()` - Kiểm tra thời gian dời
  - `checkCancelTimeAvailable()` - Kiểm tra thời gian hủy
  - `sendAppointmentConfirmationEmail()` - Gửi email xác nhận
  - `sendAppointmentRescheduleEmail()` - Gửi email dời lịch
  - `sendAppointmentCancellationEmail()` - Gửi email hủy lịch

### 2. Appointment Model Enhanced
**File**: `app/Models/Appointment.php`

#### Thêm Query Scopes:
```php
- forUser($userId)           // Lọc appointment của 1 user
- upcoming()                 // Lọc sắp tới (Chờ xác nhận, Đã xác nhận, trong tương lai)
- completed()                // Lọc đã hoàn thành
- cancelled()                // Lọc đã hủy hoặc dời
- byStatus(array $statuses)  // Lọc theo trạng thái
- orderByDate($direction)    // Sắp xếp theo ngày
- onDate($date)              // Lọc theo ngày làm việc
- forDoctor($doctorId)       // Lọc cho 1 bác sĩ
```

### 3. User Model Enhanced
**File**: `app/Models/User.php`

#### Thêm Helper Methods:
```php
- notify($type, $title, $content, $refId, $refType)  // Tạo notification
- logActivity($action, $ipAddress)                    // Ghi log hoạt động
```

### 4. Notification Model Enhanced
**File**: `app/Models/Notification.php`

#### Cấu trúc:
```php
protected $table = 'notifications'
protected $primaryKey = 'notification_id'
protected $fillable = ['user_id', 'notif_type', 'title', 'content', 'ref_id', 'ref_type', 'is_read', 'created_at']
```

#### Query Scopes:
```php
- unread()           // Thông báo chưa đọc
- read()             // Thông báo đã đọc
- forUser($userId)   // Lọc theo user
- byType($type)      // Lọc theo loại
- latest()           // Sắp xếp mới nhất
```

#### Helpers:
```php
- markAsRead()       // Đánh dấu đã đọc
- markAsUnread()     // Đánh dấu chưa đọc
```

### 5. ActivityLog Model Enhanced
**File**: `app/Models/ActivityLog.php`

#### Cấu trúc:
```php
protected $table = 'activitylogs'
protected $primaryKey = 'activity_id'
protected $fillable = ['user_id', 'action', 'ip_address', 'created_at']
```

#### Query Scopes:
```php
- forUser($userId)               // Lọc log của 1 user
- byAction($action)              // Lọc theo action
- inDateRange($start, $end)      // Lọc theo khoảng thời gian
- latest()                        // Sắp xếp mới nhất
- oldest()                        // Sắp xếp cũ nhất
```

### 6. AppointmentController Refactored
**File**: `app/Http/Controllers/AppointmentController.php`

#### Trước: ~1000+ dòng (DB queries + business logic lẫn lộn)
#### Sau: ~250 dòng (HTTP handling sạch sẽ)

#### Phương thức:
```php
// Create Form
- create()               // Gọi service->getCreateFormData()

// List
- index()                // Gọi service->getUserAppointments() & stats
- getSchedules()         // Gọi service->getSchedulesForDoctor()

// Create
- store()                // Gọi service->createAppointment() + exception handling

// Update
- edit()                 // Gọi service->getAppointmentForEdit()
- update()               // Gọi service->rescheduleAppointment() + exception handling

// Delete
- cancel()               // Gọi service->cancelAppointment() + exception handling

// API Endpoints
- suggest()              // Sử dụng DoctorSuggestionService (GIỮU NGUYÊN)
- timeslots()            // Sử dụng DoctorTimeslotService (GIỮU NGUYÊN)
- getQueueInfo()         // Sử dụng AppointmentQueueService (GIỮU NGUYÊN)
```

## 📊 Cấu trúc MVC Chuẩn

```
Models/
  ├─ Appointment         → Relations + scopes + helpers
  ├─ DoctorSchedule      → Relations + scopes + accessors
  ├─ User                → notify() + logActivity() helpers
  ├─ Notification        → Query scopes + helpers
  └─ ActivityLog         → Query scopes

Services/
  └─ AppointmentService
      ├─ getCreateFormData()
      ├─ getSchedulesForDoctor()
      ├─ createAppointment()          (business logic + transaction)
      ├─ rescheduleAppointment()      (business logic + transaction)
      ├─ cancelAppointment()          (business logic + transaction)
      ├─ getUserAppointments()
      ├─ getUserAppointmentStats()
      ├─ getAppointmentForEdit()
      ├─ getAvailableSchedulesForReschedule()
      └─ Email helpers (private)

Controllers/
  └─ AppointmentController
      ├─ HTTP request handling
      ├─ Input validation (Laravel rules)
      ├─ Exception handling
      └─ Delegates all logic to Service
```

## 🎁 Lợi ích

### 1. Separation of Concerns
- **Controller**: Chỉ xử lý HTTP requests/responses
- **Service**: Xử lý business logic, validation, transactions
- **Model**: Quản lý data + relationships

### 2. Reusability
- Service có thể sử dụng ở:
  - Controllers
  - Commands (CLI)
  - Jobs (Queues)
  - APIs

### 3. Testability
- Unit test Service độc lập
- Mock dependencies dễ dàng
- Không cần setup HTTP context

### 4. Maintainability
- Code sạch, dễ đọc, dễ hiểu
- Business logic tập trung ở 1 chỗ
- Dễ tìm và sửa bugs
- Dễ thêm tính năng mới

### 5. Consistency
- Business rules không bị lặp lại
- Tất cả validations ở 1 nơi
- Email sending logic centralized

## 🔄 Luồng Xử Lý Mới

### Đặt Lịch (Create)
```
Controller->store()
  ├─ Validate input (Laravel rules)
  ├─ Service->createAppointment()
  │  ├─ Kiểm tra appointment đã đặt
  │  ├─ Validate schedule
  │  ├─ Tính queue number
  │  ├─ DB::transaction()
  │  │  ├─ Insert/Update appointments
  │  │  ├─ Insert notifications
  │  │  ├─ Insert activity logs
  │  │  └─ DB::commit()
  │  └─ Send email (outside transaction)
  └─ Redirect with success message
```

### Dời Lịch (Reschedule)
```
Controller->update()
  ├─ Validate input
  ├─ Service->rescheduleAppointment()
  │  ├─ Validate appointment exists & can reschedule
  │  ├─ Validate new schedule
  │  ├─ Check time availability (2 hours before)
  │  ├─ DB::transaction()
  │  │  ├─ Update appointments
  │  │  ├─ Insert notifications
  │  │  ├─ Insert activity logs
  │  │  └─ DB::commit()
  │  └─ Send email
  └─ Redirect with success message
```

### Hủy Lịch (Cancel)
```
Controller->cancel()
  ├─ Validate input
  ├─ Service->cancelAppointment()
  │  ├─ Validate appointment exists & can cancel
  │  ├─ Check time availability (2 hours before)
  │  ├─ DB::transaction()
  │  │  ├─ Update status = 'Đã hủy'
  │  │  ├─ Insert notifications
  │  │  ├─ Insert activity logs
  │  │  └─ DB::commit()
  │  └─ Send email
  └─ Redirect with success message
```

## 📝 Cách Sử Dụng

### Trong Controller
```php
class AppointmentController extends Controller
{
    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function store(Request $request)
    {
        try {
            $result = $this->appointmentService->createAppointment(
                Auth::id(),
                [
                    'schedule_id' => $request->schedule_id,
                    'work_date' => $request->work_date,
                    'appointment_time' => $request->appointment_time,
                    'note' => $request->note,
                    'ip_address' => $request->ip(),
                ]
            );
            return redirect()->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->withErrors(['msg' => $e->getMessage()]);
        }
    }
}
```

### Trong Query Builder
```php
// Sử dụng model scopes
$upcoming = Appointment::forUser($userId)
    ->upcoming()
    ->orderByDate('desc')
    ->paginate(8);

$cancelled = Appointment::forUser($userId)
    ->cancelled()
    ->latest()
    ->get();

$doctorAppointments = Appointment::forDoctor($doctorId)
    ->completed()
    ->get();
```

### Tạo Notification
```php
$user->notify(
    type: 'Lịch hẹn',
    title: 'Đặt lịch hẹn thành công',
    content: 'Lịch khám lúc 09:00 ngày 10/05/2026',
    refId: $appointmentId,
    refType: 'appointment'
);
```

### Ghi Log Hoạt Động
```php
$user->logActivity(
    action: 'Đặt lịch hẹn #123',
    ipAddress: $request->ip()
);
```

## 🚀 Next Steps

- [ ] Tách suggest/timeslots/getQueueInfo sang separate Service
- [ ] Tạo FormRequest classes cho validation tái sử dụng
- [ ] Tạo Resources/DTOs cho API responses
- [ ] Thêm Repository pattern nếu query phức tạp hơn
- [ ] Unit tests cho Service (PHPUnit)
- [ ] Integration tests cho Controller
