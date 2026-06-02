# Automatic Appointment Rescheduling - Complete Implementation Guide

## 📋 Overview

A complete solution for automatically rescheduling patient appointments when a doctor takes an unexpected day-off. The system uses intelligent scoring to recommend the best alternative doctors based on multiple factors.

---

## 🎯 Features Implemented

### 1. **Smart Doctor Scoring System**
- **Algorithm**: Weighted scoring system (total: 100 points)
  - **40%** - Available slots ratio (more slots = better score)
  - **35%** - Average rating (out of 5 stars)
  - **15%** - Experience level (capped at 20 years)
  - **10%** - Number of reviews (capped at 50 reviews)

- **Example Calculation**:
  ```
  Doctor A:
  - Slots: 2/4 = 50% available → 20 points
  - Rating: 4.5/5 → 31.5 points
  - Experience: 15 years → 11.25 points
  - Reviews: 40 reviews → 8 points
  - TOTAL: 70.75/100
  ```

### 2. **Enhanced Email Notification**
Patients receive a professional email showing:
- Details of the cancelled appointment
- Reason for cancellation
- **Top 5 recommended doctors** (ranked by score)
- For each doctor:
  - Name, department, experience, rating
  - Available schedule (date & time)
  - Number of available slots
  - **Score breakdown** with visual progress bars
  - Direct "Xác nhận chọn lịch này" (Confirm Schedule) button

### 3. **One-Click Appointment Confirmation**
Patients can confirm rescheduling directly from email by:
- Clicking the confirmation button
- Secure token-based link (HMAC-SHA256)
- Automatic appointment creation
- Instant confirmation and redirect

### 4. **Automatic Appointment Creation**
When confirmed, the system:
- Validates patient owns the original appointment
- Checks for available slots
- Preserves original appointment details (service, priority)
- Creates new appointment with updated schedule
- Sends confirmation email
- Updates status tracking

---

## 📁 Files Modified/Created

### New Files:
```
app/Services/Doctor/DoctorScoringService.php
resources/views/emails/appointment-reschedule-smart.blade.php
```

### Modified Files:
```
app/Services/Doctor/DayOffService.php
app/Services/AppointmentService.php
app/Mail/AppointmentRescheduleMail.php
app/Http/Controllers/AppointmentController.php
routes/web.php
```

---

## 🔧 Technical Details

### Class: `DoctorScoringService`

**Location**: `app/Services/Doctor/DoctorScoringService.php`

**Main Methods**:

```php
// Find top N scored alternatives
findScoredAlternatives(
    Collection $doctors,      // Doctors in same department
    Carbon $originalTime,     // Original appointment time
    int $daysAhead = 7,      // Search window (days)
    int $limit = 5           // Max alternatives to return
): Collection

// Calculate score for a doctor
calculateScore(Doctor $doctor, DoctorSchedule $schedule): float

// Get detailed score breakdown for display
getScoreBreakdown(Doctor $doctor, DoctorSchedule $schedule): array
```

**Usage**:
```php
$scoring = app(DoctorScoringService::class);
$alternatives = $scoring->findScoredAlternatives(
    $alterDoctors,
    Carbon::parse($appointment->appointment_time),
    daysAhead: 7,
    limit: 5
);

// Returns: Collection of objects with:
// - doctor: Doctor model
// - schedule: DoctorSchedule model
// - available_slots: int
// - score: float (0-100)
// - score_breakdown: array with detailed scores
```

### Class: `DayOffService`

**Enhanced**: Integrated `DoctorScoringService` for intelligent alternative selection

```php
// Updated method signature
private function findAlternativeSlots(
    Collection $alterDoctors,
    $originalTime
): array
```

Now returns scored alternatives instead of simple list.

### Method: `AppointmentService::quickRescheduleFromDayOff()`

**Location**: `app/Services/AppointmentService.php`

**Signature**:
```php
public function quickRescheduleFromDayOff(
    int $oldAppointmentId,
    int $newScheduleId,
    int $userId
): array
```

**Process**:
1. Validates old appointment belongs to user
2. Checks new schedule availability
3. Creates new appointment
4. Updates old appointment to "Dời lịch" status
5. Sends confirmation email

**Returns**:
```php
[
    'old_appointment_id' => int,
    'new_appointment_id' => int,
    'queue_number' => int,
    'work_date' => string,
    'appointment_time' => string,
    'message' => string
]
```

### Email Template

**File**: `resources/views/emails/appointment-reschedule-smart.blade.php`

**Features**:
- Responsive design (mobile-friendly)
- Color-coded status (red header for cancellation)
- Doctor cards with avatars and badges
- Score breakdown with visual progress bars
- CTA buttons with secure token links
- Fallback info for no alternatives

---

## 🌐 API Endpoints

### GET - Email Confirmation Link
```
/dat-lich/xac-nhan-doi-lich?old_id=X&new_schedule_id=Y&token=HASH
```

**Parameters**:
- `old_id`: Original appointment ID
- `new_schedule_id`: New schedule ID to confirm
- `token`: HMAC-SHA256 hash for verification

**Response**: Redirect to appointments index with success message

### POST - Programmatic Confirmation (Optional)
```
POST /api/v1/appointments/reschedule-confirm
Content-Type: application/json
Authorization: Bearer <token>

{
    "old_appointment_id": 123,
    "new_schedule_id": 456
}
```

**Response**:
```json
{
    "success": true,
    "message": "Dời lịch hẹn thành công!...",
    "data": {
        "old_appointment_id": 123,
        "new_appointment_id": 124,
        "queue_number": 5,
        "work_date": "2026-06-05",
        "appointment_time": "09:30"
    }
}
```

---

## 🧪 Testing

### Test Day-Off Workflow
```bash
# Run the full day-off process
php artisan test:day-off --doctor_id=9 --date=2026-06-01

# Expected output:
# ✓ Estimated Affected Appointments: 1
# ✓ Blocked Schedules: 2
# ✓ Affected Appointments: 1  
# ✓ Emails Sent: 1
# ✅ SUCCESS: All emails sent successfully!
```

### Manual Testing Steps

1. **Create appointment with doctor**:
   ```bash
   php artisan test:create-appointment --doctor_id=9 --date=2026-06-05
   ```

2. **Register doctor day-off**:
   - Go to Admin → Doctor Schedule
   - Select doctor, date, session
   - Click "Block & Notify"
   - View email preview

3. **Verify email content**:
   - Check that smart template is used
   - Verify score calculation for each doctor
   - Test confirmation button

4. **Click confirmation link**:
   - Copy button URL from email
   - Paste in browser (must be logged in as patient)
   - Should redirect to appointments list with success message

---

## 📊 Scoring Example

### Scenario: Doctor A takes day-off on 2026-06-05

**Original Appointment**:
- Patient: John Doe
- Doctor: Dr. A (Cardiologist)
- Date: 2026-06-05 10:00 AM

**Alternative Doctors (same department - Cardiology)**:

| Doctor | Slots | Rating | Exp | Reviews | Score |
|--------|-------|--------|-----|---------|-------|
| Dr. B  | 3/4   | 4.8/5  | 15yr| 42      | **82.8**|
| Dr. C  | 1/4   | 4.2/5  | 8yr | 28      | 65.2  |
| Dr. D  | 2/4   | 4.5/5  | 5yr | 15      | 64.1  |

**Dr. B's Breakdown**:
- Available slots: 75% × 40 = **30 points**
- Rating: (4.8/5) × 35 = **33.6 points**
- Experience: (15/20) × 15 = **11.25 points**
- Reviews: (42/50) × 10 = **8.4 points**
- **Total: 83.25/100**

---

## ⚙️ Configuration

No additional configuration needed! The system uses:
- `config('app.key')` for HMAC token generation
- `config('app.url')` for email links
- Existing appointment service setup

---

## 🔐 Security Measures

1. **Token Validation**:
   - HMAC-SHA256 with app key
   - Prevents tampering with old_id or new_schedule_id
   - Link expires if app key is rotated

2. **User Validation**:
   - GET endpoint requires login
   - Appointment ownership verified
   - Schedule availability double-checked

3. **Database Transactions**:
   - Atomic operations
   - Rollback on any failure
   - Prevents partial updates

---

## 📈 Future Enhancements

1. **SMS/Push Notifications**:
   - Send link via SMS as backup
   - Mobile app push notification

2. **Refund Management**:
   - Auto-refund for pre-paid appointments
   - Integration with payment system

3. **Advanced Preferences**:
   - Allow patients to set preferred doctors
   - Time slot preferences
   - Automatic rescheduling without confirmation

4. **Analytics**:
   - Track rescheduling success rate
   - Measure patient satisfaction
   - Doctor availability patterns

---

## 📝 Troubleshooting

### Emails not being sent
- Check `storage/logs/laravel.log` for error messages
- Verify SMTP/Brevo configuration
- Check patient email address is not empty

### Score shows 0
- Verify doctor has reviews (avg_rating)
- Check experience field is set
- Ensure schedule has available slots

### Token validation fails
- Verify app key hasn't changed
- Check old_id and new_schedule_id are not modified
- Try clearing browser cache

---

## 📞 Support

For issues with the implementation:
1. Check logs: `grep "Day-off:" storage/logs/laravel.log`
2. Run test command: `php artisan test:day-off --doctor_id=X --date=YYYY-MM-DD`
3. Verify all files are created: `find app/Services/Doctor -name "*Scoring*"`

