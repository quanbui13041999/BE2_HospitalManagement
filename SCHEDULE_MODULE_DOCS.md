# MediBook — Doctor Schedule Module
## Kiến trúc & Tài liệu API

---

## 1. Sơ đồ luồng

```
Frontend (doctor-schedule.html)
        │
        │  HTTP JSON
        ▼
┌─────────────────────────────────┐
│  DoctorScheduleController       │  ← nhận request, validate, trả response
│  app/Http/Controllers/          │
└──────┬──────────────────────────┘
       │ gọi service
       ├─────────────────────────────────────────────┐
       ▼                                             ▼
┌──────────────────────┐               ┌─────────────────────────┐
│ RecurringScheduleService│              │ DayOffService           │
│  - generate()        │               │  - process()            │
│  - preview()         │               │  - listDayOffs()        │
└──────┬───────────────┘               │  - cancel()             │
       │                               └──────┬──────────────────┘
       │                                      │
       ▼                                      ├── block schedules
┌──────────────┐                              ├── cancel appointments
│ DoctorSchedule│                             └── Mail::queue(AppointmentRescheduleMail)
│    Model      │                                        │
└──────────────┘                                         ▼
                                              ┌─────────────────────────┐
                                              │  Queue Worker           │
                                              │  (php artisan queue:work│
                                              │  → gửi email SMTP)      │
                                              └─────────────────────────┘
```

---

## 2. Cấu trúc file

```
app/
├── Http/
│   ├── Controllers/
│   │   └── DoctorScheduleController.php   ← điều phối request/response
│   └── Requests/
│       └── ScheduleRequests.php           ← StoreRecurringScheduleRequest
│                                             StoreDayOffRequest
├── Mail/
│   └── AppointmentRescheduleMail.php      ← Mailable (queued)
├── Models/
│   ├── DoctorSchedule.php                 ← model chính
│   ├── DoctorAppointmentModels.php        ← Doctor, Appointment, Department
│   └── User.php                           ← (Laravel mặc định)
└── Services/
    ├── RecurringScheduleService.php       ← logic tạo lịch lặp lại
    └── DayOffService.php                  ← logic block lịch + email

database/migrations/
└── schedule_migrations.php               ← migration doctorschedules + appointments

resources/views/emails/
└── appointment-reschedule.blade.php      ← template email HTML

routes/
└── api.php                               ← định nghĩa route API
```

---

## 3. API Endpoints

### A. Recurring Schedule

| Method | URL | Mô tả |
|--------|-----|--------|
| `POST` | `/api/v1/schedules/recurring/preview` | Xem trước lịch (không ghi DB) |
| `POST` | `/api/v1/schedules/recurring` | Tạo lịch lặp lại |
| `GET`  | `/api/v1/schedules/recurring/{doctorId}` | Danh sách lịch bác sĩ |
| `DELETE` | `/api/v1/schedules/recurring/{scheduleId}` | Xoá 1 slot |

#### POST /recurring — Request body
```json
{
  "doctor_id": 1,
  "room_id": 3,
  "days_of_week": [1, 2, 3, 4, 5],
  "morning_enabled": true,
  "morning_start": "08:00",
  "morning_end": "12:00",
  "afternoon_enabled": true,
  "afternoon_start": "13:30",
  "afternoon_end": "17:00",
  "slot_duration": 30,
  "max_slot": 10,
  "apply_weeks": 4
}
```

#### POST /recurring — Response
```json
{
  "success": true,
  "message": "Đã tạo 40 lịch, bỏ qua 2 lịch đã tồn tại.",
  "data": {
    "created": 40,
    "skipped": 2
  }
}
```

#### POST /recurring/preview — Response
```json
{
  "success": true,
  "data": {
    "apply_until": "10/06/2026",
    "total_days": 20,
    "total_slots": 40,
    "slot_count": 40,
    "sessions": [
      { "label": "Sáng", "start": "08:00", "end": "12:00" },
      { "label": "Chiều", "start": "13:30", "end": "17:00" }
    ]
  }
}
```

---

### B. Day-Off

| Method | URL | Mô tả |
|--------|-----|--------|
| `POST` | `/api/v1/schedules/day-off` | Đăng ký nghỉ + block + email |
| `GET`  | `/api/v1/schedules/day-off/{doctorId}` | Danh sách ngày nghỉ |
| `DELETE` | `/api/v1/schedules/day-off/{scheduleId}` | Mở lại lịch đã block |

#### POST /day-off — Request body
```json
{
  "doctor_id": 1,
  "type": "sick",
  "date": "2026-05-20",
  "end_date": "2026-05-21",
  "session": "all",
  "reason": "Cảm cúm đột xuất"
}
```
> `type`: `sick` | `leave` | `conference`  
> `session`: `all` | `morning` | `afternoon`  
> `end_date`: bỏ qua nếu nghỉ 1 ngày

#### POST /day-off — Response
```json
{
  "success": true,
  "message": "Đã block 4 ca khám. Đã gửi email thông báo + gợi ý lịch mới cho 6 bệnh nhân.",
  "data": {
    "blocked_schedules": 4,
    "affected_appointments": 6,
    "emails_sent": 6
  }
}
```

#### GET /day-off/{doctorId} — Response
```json
{
  "success": true,
  "data": [
    {
      "date": "2026-05-20",
      "sessions": [
        {
          "schedule_id": 42,
          "start_time": "08:00:00",
          "end_time": "12:00:00",
          "note": "[sick] Cảm cúm đột xuất"
        }
      ]
    }
  ],
  "total": 1
}
```

---

## 4. Luồng gửi email chi tiết

```
storeDayOff()
     │
     ▼
DayOffService::process()
     │
     ├─ Loop qua từng ngày nghỉ
     │      │
     │      ├─ filterBySession() → chỉ block đúng buổi
     │      │
     │      ├─ schedule->update(status = 'blocked')
     │      │
     │      └─ Loop qua appointment bị ảnh hưởng
     │              │
     │              ├─ appointment->update(status = 'cancelled')
     │              │
     │              ├─ findAlternativeSlots()
     │              │      └─ Tìm slot còn trống của bác sĩ cùng khoa
     │              │         trong vòng 7 ngày tới (tối đa 3 gợi ý)
     │              │
     │              └─ Mail::to(patient)->send(AppointmentRescheduleMail)
     │                     └─ ShouldQueue → đẩy vào queue
     │                        (không block HTTP response)
     │
     └─ return { blocked, affected, emails_sent }
```

---

## 5. Hướng dẫn tích hợp Frontend

### Kết nối preview real-time
```javascript
// Gọi mỗi khi user thay đổi thông số lịch
async function updatePreview() {
  const res = await fetch('/api/v1/schedules/recurring/preview', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    },
    body: JSON.stringify(buildFormData()),
  });
  const { data } = await res.json();
  // Cập nhật UI: data.total_days, data.total_slots, data.apply_until
}
```

### Lưu lịch lặp lại
```javascript
async function saveRecurring() {
  const res = await fetch('/api/v1/schedules/recurring', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
    body: JSON.stringify(buildFormData()),
  });
  const json = await res.json();
  showToast(json.message, json.success ? 'success' : 'error');
}
```

### Đăng ký ngày nghỉ
```javascript
async function saveDayOff() {
  const res = await fetch('/api/v1/schedules/day-off', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
    body: JSON.stringify({
      doctor_id: selectedDoctorId,
      type: selectedDayOffType,       // 'sick' | 'leave' | 'conference'
      date: document.getElementById('dayoff-start').value,
      end_date: rangeOn ? document.getElementById('dayoff-end').value : null,
      session: selectedSession,       // 'all' | 'morning' | 'afternoon'
      reason: document.getElementById('dayoff-reason').value,
    }),
  });
  const json = await res.json();
  showToast(json.message, json.success ? 'success' : 'error');
}
```

---

## 6. Cấu hình Queue (bắt buộc để email hoạt động)

```bash
# .env
QUEUE_CONNECTION=database   # hoặc redis nếu có
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=xxx
MAIL_PASSWORD=xxx
MAIL_FROM_ADDRESS=noreply@medibook.vn
MAIL_FROM_NAME="MediBook"

# Tạo bảng jobs
php artisan queue:table
php artisan migrate

# Chạy worker (production: dùng supervisor)
php artisan queue:work --tries=3 --timeout=60
```

---

## 7. Service Provider — Dependency Injection

Thêm vào `AppServiceProvider::register()` nếu cần bind interface:

```php
// app/Providers/AppServiceProvider.php
use App\Services\RecurringScheduleService;
use App\Services\DayOffService;

public function register(): void
{
    // Laravel tự resolve constructor injection — không cần bind thêm
    // nếu các Service không dùng interface.
    // Nếu muốn singleton:
    $this->app->singleton(RecurringScheduleService::class);
    $this->app->singleton(DayOffService::class);
}
```
