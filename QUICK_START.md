# Quick Start Guide - Automatic Appointment Rescheduling

## ✅ What's Been Implemented

You now have a **complete automatic appointment rescheduling system** that:

1. ✅ Detects when a doctor takes unexpected day-off
2. ✅ Finds the best alternative doctors using intelligent scoring
3. ✅ Sends beautiful, professional emails to affected patients
4. ✅ Shows doctor recommendations ranked by quality metrics
5. ✅ Allows one-click confirmation directly from the email
6. ✅ Automatically creates new appointments
7. ✅ Sends confirmation and updates status tracking

---

## 🚀 How to Test

### Step 1: Create a Test Appointment
```bash
cd /path/to/BE2_HospitalManagement
php artisan test:create-appointment --doctor_id=9 --date=2026-06-05
```

### Step 2: Register Doctor Day-Off
Option A - Using Admin Panel:
1. Go to Admin Dashboard
2. Navigate to "Doctor Schedule"
3. Select doctor #9
4. Choose date: 2026-06-05
5. Select session: "all" or "morning"
6. Type reason: "Personal emergency"
7. Click "Block & Notify"

Option B - Using Test Command:
```bash
php artisan test:day-off --doctor_id=9 --date=2026-06-05
```

### Step 3: Check Email
1. **Development**: Check `storage/logs/laravel.log` for email content
   ```bash
   grep -A 50 "AppointmentRescheduleMail" storage/logs/laravel.log
   ```

2. **Production**: Check patient's email inbox (if real email is configured)

### Step 4: Verify Scoring
Look for output like:
```
Doctor B - Score: 82.8/100
├─ Available Slots: 75% (30 points)
├─ Rating: 96% (33.6 points)
├─ Experience: 75% (11.25 points)
└─ Reviews: 84% (8.4 points)
```

### Step 5: Test Confirmation Link
1. Extract confirmation link from email log
2. Format: `/dat-lich/xac-nhan-doi-lich?old_id=X&new_schedule_id=Y&token=HASH`
3. Copy and paste in browser (must be logged in)
4. Should redirect to appointments page with success message

---

## 📊 Scoring Weights Reference

Keep these weights in mind when evaluating doctor quality:

| Factor | Weight | Calculation |
|--------|--------|------------|
| Available Slots | 40% | (available_slots / max_slots) × 40 |
| Doctor Rating | 35% | (avg_rating / 5.0) × 35 |
| Experience | 15% | (years / 20, capped) × 15 |
| Review Count | 10% | (count / 50, capped) × 10 |

**Example**: A doctor with all perfect scores = 100/100 (40 + 35 + 15 + 10)

---

## 🔧 Configuration Checklist

- [ ] Verify app key is set: `php artisan key:generate`
- [ ] Check mail driver configured in `.env`
- [ ] Test doctor has other doctors in same department
- [ ] Verify test doctors have ratings and experience
- [ ] Check email template renders correctly
- [ ] Test HMAC token generation

---

## 📁 Files Changed

### New Files Created:
```
✨ app/Services/Doctor/DoctorScoringService.php
✨ resources/views/emails/appointment-reschedule-smart.blade.php
✨ RESCHEDULE_IMPLEMENTATION.md
✨ RESCHEDULE_WORKFLOW_DIAGRAM.md
✨ QUICK_START.md (this file)
```

### Modified Files:
```
📝 app/Services/Doctor/DayOffService.php
📝 app/Services/AppointmentService.php
📝 app/Mail/AppointmentRescheduleMail.php
📝 app/Http/Controllers/AppointmentController.php
📝 routes/web.php
```

**Total Changes**: ~800 lines of new code + documentation

---

## 🎯 Key Features at a Glance

### 1. Intelligent Doctor Scoring
- Multi-factor weighted algorithm
- Considers availability, quality, experience
- Always returns best alternatives

### 2. Beautiful Email Design
- Professional HTML template
- Mobile-responsive layout
- Color-coded status indicators
- Visual score breakdown

### 3. One-Click Confirmation
- Secure token-based links
- No form submission needed
- Works in all email clients
- Direct browser-based action

### 4. Automatic Appointment Creation
- Preserves original service type
- Maintains priority settings
- Updates queue numbers
- Sends confirmation email

### 5. Data Integrity
- Database transactions
- Rollback on errors
- Status tracking
- Audit logging

---

## 🆘 Troubleshooting

### "Email not being sent"
```bash
# Check logs
tail -f storage/logs/laravel.log | grep "Day-off\|Mail"

# Verify mail config
php artisan tinker
>>> config('mail')
```

### "Token validation failed"
- Verify app key hasn't changed
- Check old_id and new_schedule_id haven't been modified
- Clear browser cookies and try again

### "No alternative doctors found"
- Ensure doctor has colleagues in same department
- Check if colleagues have available schedules
- Verify schedule status is not 'blocked'

### "Scoring shows 0"
- Check if doctor has reviews (ratings)
- Verify experience field is populated
- Ensure schedule has available slots

---

## 📈 Performance Tips

1. **For Large Datasets**:
   - DoctorScoringService searches 7 days ahead by default
   - Adjust `daysAhead` parameter if needed
   - Cache doctor department relationships

2. **Email Optimization**:
   - Consider using queue for large patient lists
   - Change from `send()` to `queue()` in production
   - Ensure queue worker is running

3. **Database**:
   - Add index on `appointments(user_id, status)`
   - Add index on `doctorschedules(doctor_id, status)`
   - Monitor transaction times

---

## 🔐 Security Notes

### Token Generation
```php
// Secure HMAC-SHA256 token
$token = hash_hmac('sha256', "{$old_id}|{$new_schedule_id}", config('app.key'));
```

### Validation Steps
1. ✅ User authentication required
2. ✅ Token verified with hash_equals()
3. ✅ Appointment ownership verified
4. ✅ Status validation (must be 'Bác sĩ nghỉ')
5. ✅ Schedule availability double-checked
6. ✅ Database transaction for atomicity

---

## 📞 Next Steps

1. **Test the workflow** following steps above
2. **Review email template** in `resources/views/emails/appointment-reschedule-smart.blade.php`
3. **Customize scoring weights** if needed (edit `DoctorScoringService.php`)
4. **Integrate with production mail service** (Brevo, SendGrid, etc.)
5. **Train staff** on the day-off process
6. **Monitor logs** for the first week: `grep "Day-off:" storage/logs/laravel.log`

---

## 💡 Pro Tips

- **Batch Operations**: Register multiple days off at once
- **Scoring Customization**: Edit weights in `DoctorScoringService::calculateScore()`
- **Email Branding**: Customize colors and logo in email template
- **Notifications**: Staff receives notification when day-off is registered
- **Analytics**: Track success rates and patient satisfaction

---

## 📞 Support

For implementation details, see:
- [RESCHEDULE_IMPLEMENTATION.md](./RESCHEDULE_IMPLEMENTATION.md) - Full technical docs
- [RESCHEDULE_WORKFLOW_DIAGRAM.md](./RESCHEDULE_WORKFLOW_DIAGRAM.md) - Visual diagrams
- [app/Services/Doctor/DoctorScoringService.php](./app/Services/Doctor/DoctorScoringService.php) - Source code

---

**Last Updated**: May 31, 2026  
**Version**: 1.0  
**Status**: ✅ Ready for production testing
