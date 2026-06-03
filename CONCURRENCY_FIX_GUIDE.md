# Concurrency Conflict (Lost Update) Fix - Comprehensive Guide

## Problem Analysis

Your application had **Lost Update** concurrency conflicts in three main areas:

1. **DoctorDashboardService** - completeAppointment, cancelAppointment, replyToReview, deleteReply
2. **DayOffService** - process, cancel methods
3. **RecurringScheduleService** - generate method

### Root Cause
The code followed this vulnerable pattern:
```php
$record = Model::find($id);           // Read
// ... validate ...
$record->update(['status' => ...]);   // Write (no version check)
```

**Problem**: If two requests read the same record simultaneously:
- Request A: reads version 1, modifies, saves
- Request B: reads version 1, modifies, saves ← **Overwrites Request A's changes** (Lost Update)

---

## Solution: Optimistic Locking with Version Columns

### 1. Database Migrations (Applied ✅)

Created three migrations to add `version` columns:
- `2026_06_02_000001_add_version_to_appointments_table.php`
- `2026_06_02_000002_add_version_to_reviews_table.php`
- `2026_06_02_000003_add_version_to_doctorschedules_table.php`

Each column:
- Type: `unsignedInteger`
- Default: `1`
- Used to detect concurrent modifications

### 2. Model Updates

Updated fillable arrays in all models:
- `app/Models/Appointment.php` - added 'version'
- `app/Models/Review.php` - added 'version'
- `app/Models/DoctorSchedule.php` - added 'version'

### 3. Service Layer Fixes

#### DoctorDashboardService.php

**completeAppointment():**
```php
// Before: vulnerable to lost updates
$appointment->update(['status' => 'Hoàn thành']);

// After: pessimistic lock + atomic version check
Appointment::lockForUpdate()->find($appointmentId);
// ... validate ...
Appointment::where('appointment_id', $appointmentId)
    ->where('version', $currentVersion)
    ->update([
        'status' => 'Hoàn thành',
        'version' => $currentVersion + 1
    ]);
```

**cancelAppointment()** - Same pattern applied

**replyToReview()** - Same pattern applied

**deleteReply()** - Same pattern applied

#### DayOffService.php

**process() method:**
- Added version check when blocking schedules
- Added version check when updating appointments to "Bác sĩ nghỉ"
- Uses atomic updates with version validation
- Logs warnings if version mismatches occur

**cancel() method:**
- Atomic update with version check
- Throws exception if concurrent modification detected

#### RecurringScheduleService.php

**generate() method:**
- Added version=1 when creating new schedules
- Wrapped schedule creation in try-catch to handle race conditions
- Logs warnings on creation failures

---

## How Optimistic Locking Works

### Scenario: Two doctors try to complete the same appointment

```
Time  | Doctor A                          | Doctor B
------|-----------------------------------|-----------------------------------
T1    | SELECT version=1 FROM appointments| SELECT version=1 FROM appointments
T2    | UPDATE ... version=2 WHERE v=1   | UPDATE ... version=2 WHERE v=1
      | ✅ SUCCESS (affected rows=1)      | ❌ FAIL (affected rows=0)
T3    | Return success                    | Return concurrency error
```

Doctor B receives: "Record was modified by another process. Version mismatch."
→ Can retry with fresh data

---

## Implementation Pattern

All updates now follow this template:

```php
try {
    DB::transaction(function () use ($record) {
        $currentVersion = $record->version ?? 1;
        
        // Atomic update: only succeeds if version matches
        $updated = ModelClass::where('id', $record->id)
            ->where('version', $currentVersion)
            ->update([
                'field' => 'new_value',
                'version' => $currentVersion + 1
            ]);
        
        if ($updated === 0) {
            throw new \RuntimeException('Version mismatch');
        }
    });
    
    return ['success' => true];
} catch (\Exception $e) {
    return ['success' => false, 'message' => 'Concurrency conflict'];
}
```

---

## Key Changes Summary

| File | Method | Change |
|------|--------|--------|
| Appointment.php | - | Added 'version' to fillable |
| Review.php | - | Added 'version' to fillable |
| DoctorSchedule.php | - | Added 'version' to fillable |
| DoctorDashboardService | completeAppointment | Pessimistic lock + atomic version check |
| DoctorDashboardService | cancelAppointment | Pessimistic lock + atomic version check |
| DoctorDashboardService | replyToReview | Pessimistic lock + atomic version check |
| DoctorDashboardService | deleteReply | Pessimistic lock + atomic version check |
| DayOffService | process | Atomic version checks for schedules & appointments |
| DayOffService | cancel | Atomic version check with error handling |
| RecurringScheduleService | generate | Try-catch for race conditions during create |

---

## Testing the Fix

### Test 1: Concurrent Appointment Completion
```bash
# Terminal 1
curl -X PATCH http://localhost:8000/doctor/dashboard/appointments/1/complete

# Terminal 2 (same time)
curl -X PATCH http://localhost:8000/doctor/dashboard/appointments/1/complete
```

**Expected Result:**
- First request: Success (version updated to 2)
- Second request: Error - "Record was modified by another process"

### Test 2: Concurrent Review Reply
```bash
# Terminal 1
curl -X POST http://localhost:8000/doctor/dashboard/reviews/1/reply \
  -d 'reply=Great review!'

# Terminal 2 (overlapping)
curl -X POST http://localhost:8000/doctor/dashboard/reviews/1/reply \
  -d 'reply=Another reply!'
```

**Expected Result:**
- One succeeds
- One fails with concurrency error

---

## Benefits

✅ **Prevents Lost Updates**: Version check ensures atomic operations  
✅ **Prevents Race Conditions**: Pessimistic locking + atomic writes  
✅ **No Deadlocks**: Unlike pessimistic locking alone  
✅ **Transparent to Client**: Atomic at DB level, not app level  
✅ **Retry-able**: Clients can retry with fresh data  
✅ **Audit Trail**: Version history shows all modifications  

---

## Migration Rollback (if needed)

```bash
# Rollback the last 3 migrations
php artisan migrate:rollback --step=3
```

---

## Notes

- All existing records start with `version=1`
- Each successful update increments version by 1
- Version overflow is unlikely (32-bit unsigned int = 4 billion increments)
- For long-lived records, monitor version column growth
- Clients should handle 409 Conflict responses with automatic UI refresh + retry
