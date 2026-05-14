# 🔗 MediBook - Feature Integration Map

## 📊 Quy Trình Hoàn Chỉnh (End-to-End Workflow)

```
BƯỚC 1: BÁC SĨ CHUẨN BỊ LỊCH LÀM VIỆC
│
├─ Truy cập: /schedules
├─ Tạo lịch lặp lại (T2-T6, sáng/chiều)
├─ API: POST /api/v1/schedules/recurring
└─ Kết quả: 40 slot được tạo trong DB
    │
    └─ doctor_schedules table
        ├─ work_date: 2026-05-14...
        ├─ start_time: 08:00
        ├─ status: 'active'
        └─ max_slot: 10


BƯỚC 2: BÁC SĨ QUẢN LÝ NGÀY NGHỈ
│
├─ Truy cập: /schedules → Tab "Quản lý ngày nghỉ"
├─ Đăng ký: 2026-05-20 (bệnh), buổi sáng
├─ API: POST /api/v1/schedules/day-off
│   │
│   ├─ Block 4 ca khám (status='blocked')
│   ├─ Cancel appointment bị ảnh hưởng
│   ├─ Find alternative slots (bác sĩ cùng khoa)
│   └─ Queue email job → php artisan queue:work
│
└─ Kết quả: 
    ├─ doctor_schedules.status = 'blocked'
    ├─ appointments.status = 'cancelled'
    ├─ jobs table: email job đang chờ
    └─ Email gửi đến bệnh nhân


BƯỚC 3: BỆNH NHÂN ĐẶT LỊCH KHÁM
│
├─ Truy cập: /dat-lich (appointments.create)
├─ Chọn: Bác sĩ, Ngày, Buổi (Morning/Afternoon)
├─ API: GET /api/appointments/timeslots
│   │
│   └─ Query active schedules:
│       SELECT * FROM doctor_schedules
│       WHERE doctor_id = ? 
│       AND status = 'active'
│       AND work_date >= today
│
├─ Hiển thị: Available slots để bệnh nhân chọn
├─ Confirm: POST /dat-lich (AppointmentController@store)
│   │
│   └─ Tạo appointment:
│       INSERT INTO appointments
│       (user_id, schedule_id, appointment_time, status)
│
└─ Kết quả:
    ├─ Appointment được tạo
    ├─ Email confirmation gửi cho bệnh nhân
    ├─ doctor_schedules.max_slot -= 1
    └─ Queue job: send confirmation email


BƯỚC 4: BỆNH NHÂN XEM LỊCH HẸN
│
├─ Truy cập: /lich-hen (appointments.index)
├─ Hiển thị: 
│   ├─ Bác sĩ: (từ schedule → doctor)
│   ├─ Ngày giờ: (từ schedule.work_date + time)
│   ├─ Phòng: (từ schedule.room_id)
│   └─ Trạng thái: pending/confirmed/completed
│
└─ Hành động:
    ├─ Dời lịch: /lich-hen/{id}/doi
    │   └─ Hiển thị alternative slots từ bác sĩ khác
    │
    └─ Hủy lịch: /lich-hen/{id}/huy
        └─ Tăng max_slot của schedule cũ


BƯỚC 5: BÁC Sĩ XEM DASHBOARD
│
├─ Truy cập: /doctor-dashboard (hoặc /bac-si)
├─ Thống kê:
│   ├─ 📅 Lịch tuần tới: GET /api/v1/schedules/recurring/{doctorId}
│   ├─ 👥 Appointment sắp tới: WHERE schedule_id IN (active schedules)
│   ├─ 🏥 Lịch nghỉ: GET /api/v1/schedules/day-off/{doctorId}
│   └─ ⭐ Đánh giá trung bình: AVG(reviews.rating)
│
└─ CTA: 
    ├─ Nút "Quản lý lịch làm việc" → /schedules
    └─ Nút "Xem lịch tuần" → Weekly schedule view
```

---

## 🔄 **Mapping Database Relationships**

```
┌─────────────────────────────────────────────────────┐
│ DOCTORS                                             │
│ ├─ doctor_id (PK)                                  │
│ ├─ user_id                                         │
│ ├─ full_name                                       │
│ ├─ department_id                                   │
│ └─ status = 'active'                              │
└──────────────────────┬────────────────────────────┘
                       │ 1:N
                       ▼
┌─────────────────────────────────────────────────────┐
│ DOCTOR_SCHEDULES (Recurring)                        │
│ ├─ schedule_id (PK)                                │
│ ├─ doctor_id (FK)                                  │
│ ├─ room_id (FK)                                    │
│ ├─ work_date                                       │
│ ├─ start_time                                      │
│ ├─ end_time                                        │
│ ├─ status = 'active' | 'blocked' | 'full'         │
│ ├─ note = 'Sáng' | 'Chiều' | '[sick] Lý do...'   │
│ ├─ max_slot = 10                                   │
│ └─ created_at                                      │
└──────────────────────┬────────────────────────────┘
                       │ 1:N
                       ▼
┌─────────────────────────────────────────────────────┐
│ APPOINTMENTS (Patient bookings)                     │
│ ├─ appointment_id (PK)                             │
│ ├─ user_id (FK) → Users                            │
│ ├─ schedule_id (FK) → DoctorSchedules              │
│ ├─ service_id (FK)                                 │
│ ├─ appointment_time                                │
│ ├─ status = 'pending' | 'confirmed' | 'cancelled' │
│ ├─ queue_number                                    │
│ ├─ rescheduled_from (FK) → self                    │
│ └─ created_at                                      │
└─────────────────────────────────────────────────────┘
```

---

## 🌐 **API + View Integration Points**

### **1. When creating appointment** (appointments/create.blade.php)
```javascript
// Get available schedules
GET /api/appointments/timeslots?doctor_id=1&date=2026-05-14
└─ Backend: Query doctor_schedules WHERE status='active'

// Create appointment
POST /dat-lich
└─ Backend: Insert appointment linked to schedule_id
```

### **2. When registering day-off** (/schedules tab 2)
```javascript
// Block schedule
POST /api/v1/schedules/day-off
├─ UPDATE doctor_schedules SET status='blocked'
├─ UPDATE appointments SET status='cancelled'
└─ Queue email job

// Email content shows:
├─ Doctor name
├─ Original appointment time
├─ 3 alternative slots from same department doctors
└─ CTA: Book new appointment
```

### **3. When rescheduling appointment** (appointments/edit.blade.php)
```javascript
// Find alternative slots
GET /api/appointments/suggest?doctor_id=1&date=2026-05-20
└─ Backend: Same logic as day-off → find alternatives

// Update appointment
PUT /lich-hen/{id}/doi
├─ Free old schedule slot
├─ Assign to new schedule
└─ Queue email: confirmation
```

### **4. Doctor dashboard stats** (doctor-dashboard.html)
```javascript
// Weekly schedule preview
GET /api/v1/schedules/recurring/{doctorId}
└─ Display: Calendar view of active schedules

// Upcoming appointments
SELECT a.* FROM appointments a
JOIN doctor_schedules ds ON a.schedule_id = ds.schedule_id
WHERE ds.doctor_id = ?
AND ds.work_date >= today
ORDER BY ds.work_date

// Day-off list
GET /api/v1/schedules/day-off/{doctorId}
└─ Display: Upcoming blocked dates
```

---

## 📁 **File Structure & Linking**

```
routes/
├─ web.php
│  ├─ /dat-lich → appointments.create (AppointmentController)
│  ├─ /lich-hen → appointments.index
│  ├─ /schedules → doctor.schedule ✅ NEW
│  ├─ /bac-si → doctors.index
│  └─ /admin → admin.dashboard

resources/views/
├─ appointments/
│  ├─ create.blade.php [需要联动]
│  │  └─ 使用: GET /api/appointments/timeslots
│  │     → 从 doctor_schedules 读取 active slots
│  │
│  ├─ edit.blade.php
│  │  └─ 使用: GET /api/appointments/suggest
│  │     → 从 doctor_schedules + DayOffService 推荐
│  │
│  └─ index.blade.php
│     └─ 显示: doctor_schedules 信息
│
├─ doctor/
│  ├─ doctor-schedule.blade.php ✅ LINKED
│  │  ├─ API: POST /recurring
│  │  ├─ API: POST /day-off
│  │  └─ Navigation: Links to /dat-lich, /lich-hen, etc.
│  │
│  └─ doctor-dashboard.html
│     ├─ Stats: Pull from doctor_schedules
│     ├─ Link: "Quản lý lịch" → /schedules
│     └─ Link: "Xem lịch tuần" → doctor-schedule weekly view
│
└─ admin/
   └─ rooms/
      ├─ schedule-create.blade.php
      └─ schedule-edit.blade.php
         └─ NOTE: This is ADMIN schedule (room-based)
            vs DOCTOR schedule (recurring)
```

---

## 🔗 **Navigation Flow Diagram**

```
HOME (index)
  │
  ├─ Đặt lịch khám → /dat-lich
  │  ├─ Select Doctor → Queries: doctor_schedules WHERE status='active'
  │  ├─ Select Time → Shows: available slots from schedule
  │  ├─ Confirm → Creates: appointment linked to schedule_id
  │  └─ Success → Email: confirmation to patient
  │
  ├─ Lịch hẹn của tôi → /lich-hen
  │  ├─ View: appointment with doctor info from schedule
  │  ├─ Dời lịch → Suggests: alternatives from doctor_schedules
  │  └─ Hủy lịch → Frees: slot in schedule
  │
  ├─ Bác sĩ → /bac-si
  │  └─ View: doctor list (used in step 1)
  │
  ├─ Lịch làm việc → /schedules ✅ NEW
  │  ├─ Create recurring schedule ← Generates: doctor_schedules rows
  │  ├─ Register day-off ← Blocks: schedules + cancels appointments
  │  └─ Get suggested alternatives
  │
  └─ Thống kê → /admin
     ├─ Dashboard: Stats from doctor_schedules
     ├─ Room Schedule: Admin-managed schedule (DIFFERENT from recurring)
     └─ Payments: Related to appointments
```

---

## ✅ **Integration Checklist**

- [x] Doctor schedule creation (`POST /api/v1/schedules/recurring`)
- [x] Day-off management with email (`POST /api/v1/schedules/day-off`)
- [x] Route: `/schedules` → `doctor.schedule`
- [x] Navigation: All features linked
- [x] Database: doctor_schedules linked to appointments
- [ ] **TODO**: Update `appointments/create.blade.php` to use schedules
- [ ] **TODO**: Update `appointments/edit.blade.php` for rescheduling
- [ ] **TODO**: Update `doctor-dashboard.html` stats
- [ ] **TODO**: Add schedule preview widget
- [ ] **TODO**: Add email notification for alternative doctors

---

## 🚀 **Next Steps to Complete Integration**

### Step 1: Update Appointment Booking
```blade
<!-- appointments/create.blade.php -->
<!-- Show available schedules when doctor is selected -->
<script>
async function loadSchedules(doctorId, date) {
    const res = await fetch(`/api/appointments/timeslots?doctor_id=${doctorId}&date=${date}`);
    // This should query doctor_schedules WHERE status='active'
}
</script>
```

### Step 2: Update Appointment Rescheduling
```blade
<!-- appointments/edit.blade.php -->
<!-- Show suggested alternatives from other doctors -->
<script>
async function getSuggestedDoctors(appointmentId) {
    const res = await fetch(`/api/appointments/suggest?appointment_id=${appointmentId}`);
    // Uses: DayOffService::findAlternativeSlots logic
}
</script>
```

### Step 3: Update Doctor Dashboard
```blade
<!-- doctor-dashboard.html -->
<!-- Add weekly schedule preview -->
<div id="weekly-schedule">
    <!-- Query: GET /api/v1/schedules/recurring/{doctorId} -->
    <!-- Display: Calendar grid of active schedules -->
</div>
```

### Step 4: Add Email Improvements
- [x] Appointment confirmation email
- [x] Appointment rescheduled email
- [x] Day-off notification with alternatives
- [ ] **TODO**: Weekly schedule reminder email
- [ ] **TODO**: Patient reschedule confirmation email

