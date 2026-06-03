# Map 40 chức năng HospitalC

File này dùng để tra nhanh chức năng nằm ở route/controller/view nào. Smoke test chính: `tests/Feature/FortyFeatureSmokeTest.php`.

| # | Chức năng | Route chính | Controller/Service | View chính |
|---|---|---|---|---|
| 1 | Đặt lịch hẹn | `appointments.create`, `appointments.store` | `app/Http/Controllers/AppointmentController.php`, `app/Services/AppointmentService.php` | `resources/views/appointments/create.blade.php` |
| 2 | Hủy lịch hẹn | `appointments.cancel`, `appointments.index` | `app/Http/Controllers/AppointmentController.php`, `app/Services/AppointmentService.php` | `resources/views/appointments/index.blade.php` |
| 3 | Đổi lịch hẹn | `appointments.edit`, `appointments.update` | `app/Http/Controllers/AppointmentController.php`, `app/Services/AppointmentService.php` | `resources/views/appointments/edit.blade.php` |
| 4 | Xem danh sách lịch hẹn | `appointments.index` | `app/Http/Controllers/AppointmentController.php` | `resources/views/appointments/index.blade.php` |
| 5 | Hàng đợi khám bệnh | `queue.display.index`, `queue.manage.*`, `queue.doctor.*`, `admin.queue.*` | `app/Http/Controllers/Queue/*`, `app/Http/Controllers/Admin/QueueController.php`, `app/Services/QueueService.php` | `resources/views/queue/**`, `resources/views/admin/queue/**` |
| 6 | Tìm kiếm bệnh nhân | `admin.patients.search`, `admin.patients.search.results`, `admin.patients.ai-search` | `app/Http/Controllers/Admin/PatientSearchController.php` | `resources/views/admin/patients/search.blade.php`, `resources/views/admin/patients/partials/**` |
| 7 | Chat CSKH | `chat.*`, `admin.chatroom.*` | `app/Http/Controllers/ChatController.php`, `app/Http/Controllers/Admin/ChatRoomController.php`, `app/Services/GeminiChatService.php` | `resources/views/components/chat-widget.blade.php`, `resources/views/admin/chatroom/index.blade.php` |
| 8 | Nhật ký hoạt động | `admin.activity-logs.*` | `app/Http/Controllers/Admin/ActivityLogController.php`, `app/Services/ActivityLogService.php` | `resources/views/admin/activity-logs/**` |
| 9 | Bản tin bệnh viện | `news.*`, `admin.news.*` | `app/Http/Controllers/NewsController.php`, `app/Http/Controllers/Admin/NewsController.php` | `resources/views/news/**`, `resources/views/admin/news/**` |
| 10 | Thông báo | `notifications.*` | `app/Http/Controllers/NotificationController.php`, `app/Services/NotificationService.php` | `resources/views/notifications/**`, `resources/views/components/notification-bell.blade.php` |
| 11 | Tiền sử dị ứng | `health.index`, `health.store`, `medical_history.index` | `app/Http/Controllers/HealthBackgroundController.php`, `app/Http/Controllers/MedicalHistoryController.php` | `resources/views/health_background/index.blade.php`, `resources/views/medical_history/index.blade.php` |
| 12 | Hồ sơ cá nhân | `profile.*` | `app/Http/Controllers/ProfileController.php` | `resources/views/profile/**` |
| 12b | Hỗ trợ liên hệ | `emergency-contacts.*` | `app/Http/Controllers/EmergencyContactController.php` | `resources/views/emergency/emergency-contacts.blade.php` |
| 13 | Thành viên ưu đãi | `membership.show` | `app/Http/Controllers/MembershipController.php`, `app/Services/MembershipCardSyncService.php` | `resources/views/Membership/membershipcards.blade.php` |
| 14 | Nhắc nhở điều trị | `treatment.*`, `admin.treatment.*` | `app/Http/Controllers/Patient/TreatmentReminderController.php`, `app/Http/Controllers/Admin/TreatmentReminderAdminController.php` | `resources/views/patient/treatment_reminder/**`, `resources/views/admin/treatment_reminder/**` |
| 15 | Danh sách phiếu khám | `medical-records.*` | `app/Http/Controllers/MedicalRecordController.php`, `app/Services/MedicalRecordService.php` | `resources/views/medical-records/**` |
| 15b | Quản lí thiết bị | `admin.device-types.*`, `admin.devices.*` | `app/Http/Controllers/Admin/DeviceTypeController.php`, `app/Http/Controllers/Admin/DeviceController.php` | `resources/views/admin/device_types/**`, `resources/views/admin/devices/**` |
| 16 | Hồ sơ bệnh án | `medical-records.*` | `app/Http/Controllers/MedicalRecordController.php` | `resources/views/medical-records/**` |
| 17 | Thư viện phục hồi | `rehab.*`, `admin.rehab.*` | `app/Http/Controllers/RehabExerciseController.php`, `app/Http/Controllers/Admin/AdminRehabExerciseController.php` | `resources/views/patient/rehab_*.blade.php`, `resources/views/admin/rehab_*.blade.php` |
| 18 | Chế độ dinh dưỡng | `patient.nutrition.*`, `admin.nutrition.*` | `app/Http/Controllers/PatientNutritionController.php`, `app/Http/Controllers/AdminNutritionController.php` | `resources/views/nutrition/**` |
| 19 | Nhật ký sức khỏe | `health-tracking.*` | `app/Http/Controllers/HealthTrackingController.php`, `app/Services/HealthRiskService.php` | `resources/views/health-tracking/**` |
| 20 | Lưu trữ tra cứu y khoa | `documents.*`, `documents.patient.index` | `app/Http/Controllers/DocumentController.php` | `resources/views/documents/**` |
| 21 | Gợi ý bác sĩ | `appointments.suggest`, `appointments.create` | `app/Http/Controllers/AppointmentController.php`, `app/Services/Doctor/DoctorSuggestionService.php` | `resources/views/appointments/create.blade.php` |
| 22 | Tạo giờ thông minh | `appointments.timeslots`, `slot.*` | `app/Http/Controllers/AppointmentController.php`, `app/Http/Controllers/Doctor/SlotHoldController.php`, `app/Services/Doctor/DoctorTimeslotService.php` | `resources/views/appointments/create.blade.php` |
| 23 | Ước lượng thời gian | `appointments.queue-info`, `queue.api.display` | `app/Http/Controllers/AppointmentController.php`, `app/Http/Controllers/Queue/QueueDisplayController.php` | `resources/views/queue/display.blade.php` |
| 24 | Nhắc lịch hẹn | `appointments.reminders.send`, `notifications.*` | `app/Http/Controllers/AppointmentController.php`, `app/Services/AppointmentReminderService.php` | `resources/views/notifications/**`, `resources/views/mail/appointment-*.blade.php` |
| 25 | Đánh giá sau khám | `reviews.*` | `app/Http/Controllers/Doctor/ReviewsDoctorController.php`, `app/Services/Doctor/ReviewsDoctorService.php` | `resources/views/appointments/reviews.blade.php` |
| 26 | Cài đặt lịch làm việc | `doctor.schedule`, `api/v1/schedules/recurring*` | `app/Http/Controllers/Doctor/DoctorScheduleController.php`, `app/Services/Doctor/RecurringScheduleService.php` | `resources/views/doctor/doctor-schedule.blade.php` |
| 27 | Quản lý ngày nghỉ | `api/v1/schedules/day-off*` | `app/Http/Controllers/Doctor/DoctorScheduleController.php`, `app/Services/Doctor/DayOffService.php` | `resources/views/doctor/doctor-schedule.blade.php` |
| 28 | Giữ slot | `slot.hold`, `slot.release`, `slot.status` | `app/Http/Controllers/Doctor/SlotHoldController.php`, `app/Services/User/SlotHoldService.php` | `resources/views/appointments/create.blade.php` |
| 29 | Tự động dời lịch | `appointments.doctor-off`, `appointments.reschedule-confirm`, `api/v1/appointments/reschedule-confirm` | `app/Http/Controllers/AppointmentController.php`, `app/Services/Doctor/DayOffService.php` | `resources/views/appointments/doctor-off.blade.php` |
| 30 | Dashboard bác sĩ / thống kê | `doctor.dashboard`, `doctor/dashboard/*` | `app/Http/Controllers/Doctor/DashboardController.php`, `app/Services/Doctor/DoctorDashboardService.php` | `resources/views/doctor/dashboard.blade.php` |
| 31 | Quản lý phòng khám | `admin.rooms.*` | `app/Http/Controllers/Admin/RoomController.php`, `app/Services/Admin/RoomService.php` | `resources/views/admin/rooms/**` |
| 32 | Quản lý danh mục dịch vụ | `admin.services.*` | `app/Http/Controllers/Admin/ServiceController.php`, `app/Services/Admin/ServiceService.php` | `resources/views/admin/services/**` |
| 33 | Thanh toán online | `user.payments.*`, `admin.payments.*`, `api/payments/webhook` | `app/Http/Controllers/User/PaymentController.php`, `app/Http/Controllers/Admin/PaymentController.php`, `app/Services/PayOsService.php` | `resources/views/user/payments/**`, `resources/views/admin/payments/**` |
| 34 | Quản lý BHYT | `admin.bhyt.*`, `user.insurance` | `app/Http/Controllers/Admin/BhytController.php`, `app/Services/Admin/BhytService.php` | `resources/views/bhyt/index.blade.php` |
| 35 | Quản lý viện phí | `admin.payments.*` | `app/Http/Controllers/Admin/PaymentController.php`, `app/Services/Admin/PaymentService.php` | `resources/views/admin/payments/**` |
| 36 | Thống kê theo bác sĩ | `admin.doctor-statistics.index` | `app/Http/Controllers/Admin/DoctorStatisticController.php`, `app/Services/Admin/DoctorStatisticService.php` | `resources/views/admin/doctor_statistics/index.blade.php` |
| 37 | Quản lý tiêm chủng | `admin.vaccines.*`, `admin.vaccination-records.*` | `app/Http/Controllers/Admin/VaccineController.php`, `app/Http/Controllers/Admin/VaccinationRecordController.php`, `app/Services/Admin/VaccineService.php` | `resources/views/admin/vaccines/**`, `resources/views/admin/vaccination_records/**` |
| 38 | Báo cáo doanh thu | `admin.revenue.index` | `app/Http/Controllers/Admin/RevenueController.php`, `app/Services/Admin/RevenueService.php` | `resources/views/admin/revenue/index.blade.php` |
| 39 | Bệnh nhân ưu tiên | `queue.manage.checkin`, `queue.manage.checkin.store` | `app/Http/Controllers/Queue/QueueManageController.php`, `app/Services/QueueService.php` | `resources/views/queue/manage/checkin.blade.php` |
| 40 | CRUD thanh toán | `admin.payments.*` | `app/Http/Controllers/Admin/PaymentController.php`, `app/Services/Admin/PaymentService.php` | `resources/views/admin/payments/**` |

## Checklist test đang có

- `tests/Feature/FortyFeatureSmokeTest.php`: smoke 40 chức năng, route chính, API/URL lỗi thường gặp.
- `tests/Feature/TenFeatureHardeningTest.php`: hardening 10 chức năng ban đầu, select giả, text trắng/full-width, conflict hủy lịch, dời lịch.
- `tests/Feature/VerifyAllTenFeaturesTest.php`: route/view regression của nhóm 10 chức năng và thanh toán dịch vụ.
- `tests/Feature/PatientSearchTest.php`: tìm kiếm bệnh nhân thường + AI fallback.
- `tests/Feature/QueueSystemSmokeTest.php`: luồng hàng đợi lễ tân/bác sĩ/admin.
- `tests/Feature/ChatCskhSmokeTest.php`: luồng chat user/admin và API không in JSON thô khi mở browser.
