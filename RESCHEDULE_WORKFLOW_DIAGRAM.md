# 🔄 Automatic Appointment Rescheduling - Workflow Diagram

## Overall Process Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                                                                       │
│                    DOCTOR TAKES DAY-OFF                             │
│          (e.g., unexpected illness, emergency)                      │
│                                                                       │
└──────────────────────────┬──────────────────────────────────────────┘
                           │
                           ▼
        ┌──────────────────────────────────────────┐
        │  DayOffService::process()                │
        │  - Input: doctor_id, date, session       │
        │  - Type: sick/leave/conference           │
        │  - Reason: explanation                   │
        └──────────┬───────────────────────────────┘
                   │
        ┌──────────▼───────────────────────────────┐
        │ 1. Block Schedule (status='blocked')      │
        │ 2. Find Affected Appointments            │
        │ 3. Update to 'Bác sĩ nghỉ' Status        │
        └──────────┬───────────────────────────────┘
                   │
        ┌──────────▼─────────────────────────────────────────┐
        │ 4. Find Alternative Doctors (same department)      │
        │    Using DoctorScoringService:                     │
        │    • Get all doctors from same department         │
        │    • For each doctor:                             │
        │      - Find schedule with available slots         │
        │      - Calculate SCORE:                           │
        │        * 40% available_slots_ratio               │
        │        * 35% average_rating                       │
        │        * 15% experience (cap 20 yrs)             │
        │        * 10% num_reviews (cap 50)                │
        │    • Sort by score (highest first)               │
        │    • Return top 5 alternatives                   │
        └──────────┬───────────────────────────────────────┘
                   │
        ┌──────────▼──────────────────────────────┐
        │ 5. Send AppointmentRescheduleMail       │
        │    - Patient name                       │
        │    - Old appointment details            │
        │    - Top 5 scored alternatives          │
        │    - Confirmation buttons (with tokens) │
        └──────────┬──────────────────────────────┘
                   │
                   ▼
    ┌──────────────────────────────────────────────┐
    │                                              │
    │  📧 EMAIL RECEIVED BY PATIENT                │
    │                                              │
    │  Subject: Lịch hẹn của bạn đã thay đổi      │
    │                                              │
    │  ⚠️  Lịch bị hủy: Dr. A, 2026-06-05 10:00  │
    │                                              │
    │  ⭐ Bác sĩ gợi ý (xếp hạng theo chất lượng) │
    │                                              │
    │  Doctor B - Score 82.8/100                 │
    │  ├─ Cardiology | 15 years | 4.8★ (42)     │
    │  ├─ 2026-06-06 09:00 | 3 slots available  │
    │  ├─ Score breakdown:                       │
    │  │  • Slots: 75% (30 pts) ████████░░      │
    │  │  • Rating: 96% (33.6 pts) ██████████    │
    │  │  • Experience: 75% (11.25 pts) ███░░   │
    │  │  • Reviews: 84% (8.4 pts) ███░░░       │
    │  └─ [✅ Xác nhận chọn lịch này]            │
    │                                              │
    │  Doctor C - Score 65.2/100                 │
    │  ... (similar format)                       │
    │                                              │
    └──────────┬───────────────────────────────────┘
               │
        ┌──────▼────────────────────────────────────┐
        │ PATIENT CLICKS CONFIRMATION BUTTON        │
        │ (or visits link manually)                 │
        │                                            │
        │ URL: /dat-lich/xac-nhan-doi-lich         │
        │      ?old_id=123                         │
        │      &new_schedule_id=456                │
        │      &token=HMAC_SHA256(...)             │
        └──────┬────────────────────────────────────┘
               │
        ┌──────▼──────────────────────────────────────────┐
        │ AppointmentController::confirmRescheduleFromEmail│
        │                                                   │
        │ 1. Verify user is logged in                     │
        │ 2. Validate token (HMAC)                        │
        │ 3. Call quickRescheduleFromDayOff():            │
        │    - Verify old appointment exists              │
        │    - Verify belongs to current user            │
        │    - Verify status = 'Bác sĩ nghỉ'             │
        │    - Check new schedule has slots              │
        │    - Create NEW appointment                    │
        │    - Update OLD to 'Dời lịch'                 │
        │    - Create notification                       │
        │    - Send confirmation email                   │
        │ 4. Redirect to appointments.index              │
        │    with success message                        │
        └──────┬──────────────────────────────────────┘
               │
               ▼
    ┌─────────────────────────────────────┐
    │   ✅ SUCCESS                        │
    │                                     │
    │   Patient's appointment updated:    │
    │   • Old: 'Dời lịch' (archived)    │
    │   • New: 'Chờ xác nhận'           │
    │   • Confirmation email sent       │
    │   • Redirect to appointments page │
    │                                     │
    └─────────────────────────────────────┘
```

---

## Database State Changes

### Before Day-Off:
```
Appointments Table:
┌──────┬────────────┬──────────────┬──────────┬────────────────┐
│ ID   │ Doctor ID  │ Appt Time    │ Status   │ Queue #        │
├──────┼────────────┼──────────────┼──────────┼────────────────┤
│ 123  │ 9 (Dr. A)  │ 2026-06-05   │ ✓ Đã    │ 5              │
│      │            │ 10:00        │ xác nhận │                │
└──────┴────────────┴──────────────┴──────────┴────────────────┘

Doctor Schedules Table:
┌────────────┬──────────┬────────┬─────────┬──────────┐
│ Schedule   │ Doctor   │ Date   │ Time    │ Status   │
├────────────┼──────────┼────────┼─────────┼──────────┤
│ 789        │ 9        │ 06-05  │ 10:00   │ Hoạt động│
│ 790        │ 9        │ 06-05  │ 13:30   │ Hoạt động│
└────────────┴──────────┴────────┴─────────┴──────────┘
```

### After Day-Off Registration:
```
Appointments Table (Updated):
┌──────┬────────────┬──────────────┬──────────────┬────────────────┐
│ ID   │ Doctor ID  │ Appt Time    │ Status       │ Cancel Reason  │
├──────┼────────────┼──────────────┼──────────────┼────────────────┤
│ 123  │ 9 (Dr. A)  │ 2026-06-05   │ Bác sĩ nghỉ  │ leave: Personal│
│      │            │ 10:00        │              │ emergency      │
└──────┴────────────┴──────────────┴──────────────┴────────────────┘

Doctor Schedules Table (Updated):
┌────────────┬──────────┬────────┬─────────┬─────────────┐
│ Schedule   │ Doctor   │ Date   │ Time    │ Status      │
├────────────┼──────────┼────────┼─────────┼─────────────┤
│ 789        │ 9        │ 06-05  │ 10:00   │ blocked     │
│ 790        │ 9        │ 06-05  │ 13:30   │ blocked     │
└────────────┴──────────┴────────┴─────────┴─────────────┘

Doctor Days Off Table (New):
┌──────────┬──────────┬──────────────┬──────────┐
│ Doctor   │ From     │ To           │ Type     │
├──────────┼──────────┼──────────────┼──────────┤
│ 9        │ 2026-06-05│ 2026-06-05   │ leave    │
└──────────┴──────────┴──────────────┴──────────┘
```

### After Patient Confirms Reschedule:
```
Appointments Table (Final):
┌──────┬────────────┬──────────────┬──────────────┬──────────────────┐
│ ID   │ Doctor ID  │ Appt Time    │ Status       │ Notes            │
├──────┼────────────┼──────────────┼──────────────┼──────────────────┤
│ 123  │ 9 (Dr. A)  │ 2026-06-05   │ Dời lịch     │ Rescheduled to   │
│      │            │ 10:00        │              │ appointment 124  │
├──────┼────────────┼──────────────┼──────────────┼──────────────────┤
│ 124  │ 10 (Dr. B) │ 2026-06-06   │ Chờ xác      │ Rescheduled from │
│      │            │ 09:00        │ nhận         │ appointment 123  │
└──────┴────────────┴──────────────┴──────────────┴──────────────────┘
```

---

## Scoring Calculation Example

```
Available Doctors for Rescheduling:
Department: Cardiology (same as Dr. A)

Doctor B:
├─ Experience: 15 years
├─ Average Rating: 4.8/5.0
├─ Total Reviews: 42
├─ Available Slots This Week: 3/4 (75%)
│
└─ SCORE CALCULATION:
   • Available Slots: 75% × 40% = 30.00 points
   • Rating: (4.8/5.0) × 35% = 33.60 points
   • Experience: min(15/20) × 15% = 11.25 points
   • Reviews: min(42/50) × 10% = 8.40 points
   ├─────────────────────────────────
   └─ TOTAL SCORE: 83.25 / 100 ⭐⭐⭐⭐⭐

Doctor C:
├─ Experience: 8 years
├─ Average Rating: 4.2/5.0
├─ Total Reviews: 28
├─ Available Slots This Week: 1/4 (25%)
│
└─ SCORE CALCULATION:
   • Available Slots: 25% × 40% = 10.00 points
   • Rating: (4.2/5.0) × 35% = 29.40 points
   • Experience: min(8/20) × 15% = 6.00 points
   • Reviews: min(28/50) × 10% = 5.60 points
   ├─────────────────────────────────
   └─ TOTAL SCORE: 51.00 / 100 ⭐⭐⭐

Doctor D:
├─ Experience: 5 years
├─ Average Rating: 4.5/5.0
├─ Total Reviews: 15
├─ Available Slots This Week: 2/4 (50%)
│
└─ SCORE CALCULATION:
   • Available Slots: 50% × 40% = 20.00 points
   • Rating: (4.5/5.0) × 35% = 31.50 points
   • Experience: min(5/20) × 15% = 3.75 points
   • Reviews: min(15/50) × 10% = 3.00 points
   ├─────────────────────────────────
   └─ TOTAL SCORE: 58.25 / 100 ⭐⭐⭐

═══════════════════════════════════════
RANKED RESULTS (Top 5):
1. Doctor B - 83.25 ⭐⭐⭐⭐⭐
2. Doctor D - 58.25 ⭐⭐⭐
3. Doctor C - 51.00 ⭐⭐⭐
═══════════════════════════════════════
```

---

## Security Flow

```
Email Confirmation Link Generation:
┌────────────────────────────────────────────────────────────┐
│                                                             │
│ 1. Generate Components:                                    │
│    old_appointment_id = 123                                │
│    new_schedule_id = 456                                   │
│                                                             │
│ 2. Create Hash:                                            │
│    data = "123|456"                                        │
│    token = HMAC-SHA256(data, config('app.key'))           │
│    token = "a7f3c2e1d9b4f6c8..."                          │
│                                                             │
│ 3. Build URL:                                              │
│    /dat-lich/xac-nhan-doi-lich                            │
│    ?old_id=123                                             │
│    &new_schedule_id=456                                    │
│    &token=a7f3c2e1d9b4f6c8...                             │
│                                                             │
│ 4. Insert into Email Template                             │
│                                                             │
└────────────────────────────────────────────────────────────┘

Email Link Validation:
┌────────────────────────────────────────────────────────────┐
│                                                             │
│ 1. User Clicks Link in Email                              │
│    ↓                                                        │
│ 2. Server receives request with:                          │
│    old_id=123, new_schedule_id=456, token=a7f3c2...       │
│    ↓                                                        │
│ 3. Recalculate expected token:                            │
│    data = "123|456"                                        │
│    expected_token = HMAC-SHA256(data, app.key)            │
│    ↓                                                        │
│ 4. Compare tokens (using hash_equals for timing safety):  │
│    hash_equals(provided_token, expected_token)             │
│    ✓ MATCH → Proceed with rescheduling                    │
│    ✗ NO MATCH → Reject with error                         │
│                                                             │
│ 5. Additional Checks:                                      │
│    ✓ User is logged in                                     │
│    ✓ Old appointment exists                               │
│    ✓ Old appointment belongs to current user              │
│    ✓ Old appointment status = 'Bác sĩ nghỉ'              │
│    ✓ New schedule exists and is available                 │
│                                                             │
│ 6. All Checks Pass → Create New Appointment               │
│                                                             │
└────────────────────────────────────────────────────────────┘
```

---

## Email Template Structure

```
┌─────────────────────────────────────────────────────┐
│  HEADER (Red gradient background)                   │
│  ⚠️  Lịch hẹn của bạn đã thay đổi                  │
│  Bác sĩ có lịch nghỉ đột xuất                      │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│  BODY                                               │
│                                                     │
│  Greeting:                                          │
│  "Kính gửi John Doe, chúng tôi xin thông báo..."  │
│                                                     │
│  ❌ CANCELLED APPOINTMENT CARD                     │
│  ├─ 👨‍⚕️ Bác sĩ: Dr. A                                │
│  ├─ 🏥 Chuyên khoa: Cardiology                     │
│  ├─ 📅 Ngày hẹn: Tuesday, 05/06/2026               │
│  ├─ ⏰ Giờ hẹn: 10:00 – 10:30                       │
│  └─ 📝 Lý do: leave — Personal emergency           │
│                                                     │
│  ⭐ RECOMMENDED DOCTORS (Ranked by Score)          │
│                                                     │
│  ┌─────────────────────────────────────────────┐  │
│  │ DOCTOR CARD #1 (Highest Score)              │  │
│  │                                             │  │
│  │ [Avatar] Dr. B                 Score: 83.25 │  │
│  │         Cardiology             ▓▓▓▓▓░░░░░  │  │
│  │         15 years • 4.8★ (42)                │  │
│  │                                             │  │
│  │ Schedule Info:                              │  │
│  │ 📅 06/06/2026  ⏰ 09:00-17:00  💺 3 slots  │  │
│  │                                             │  │
│  │ Score Breakdown:                            │  │
│  │ Slots (40%): 75% ▓▓▓░░  30.0 pts           │  │
│  │ Rating (35%): 96% ▓▓▓▓▓░ 33.6 pts          │  │
│  │ Experience (15%): 75% ▓▓▓░░  11.25 pts     │  │
│  │ Reviews (10%): 84% ▓▓▓░░  8.4 pts          │  │
│  │                                             │  │
│  │ [✅ Xác nhận chọn lịch này]                 │  │
│  │ (Click → /dat-lich/xac-nhan-doi-lich)      │  │
│  └─────────────────────────────────────────────┘  │
│                                                     │
│  [More doctor cards...]                            │
│                                                     │
│  💡 IMPORTANT NOTES:                               │
│  • Refund will be automatic (3-5 days)            │
│  • Click above to confirm and reschedule          │
│  • Contact us if no suitable option available     │
│                                                     │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│  FOOTER                                             │
│  Don't reply to this email                         │
│  © 2026 MediBook • Privacy Policy                 │
└─────────────────────────────────────────────────────┘
```

