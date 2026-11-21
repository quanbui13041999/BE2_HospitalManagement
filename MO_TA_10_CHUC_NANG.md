# Mo ta 10 chuc nang trong thu muc `Tu/`

Tai lieu nay dung de tra cuu nhanh: moi chuc nang nam o route nao, file nao xu ly, view nao hien thi va file do co nhiem vu gi.

Nguon chinh: `routes/web.php`, cac controller trong `app/Http/Controllers`, service trong `app/Services`, model trong `app/Models`, view trong `resources/views` va test trong `tests/Feature`.

## Tong quan nhanh

| STT | Chuc nang | URL / Route chinh | File xu ly chinh |
|---:|---|---|---|
| 1 | Dat lich hen | `/dat-lich` | `app/Http/Controllers/AppointmentController.php`, `app/Services/AppointmentService.php` |
| 2 | Huy lich hen | `/lich-hen/{id}/huy` | `app/Http/Controllers/AppointmentController.php`, `app/Services/AppointmentService.php` |
| 3 | Doi lich hen | `/lich-hen/{id}/doi` | `app/Http/Controllers/AppointmentController.php`, `app/Services/AppointmentService.php` |
| 4 | Xem danh sach lich hen | `/lich-hen` | `app/Http/Controllers/AppointmentController.php`, `resources/views/appointments/index.blade.php` |
| 5 | Hang doi kham benh | `/queue/*`, `/admin/queue/*` | `app/Http/Controllers/Queue/*`, `app/Services/QueueService.php` |
| 6 | Tim kiem benh nhan | `/admin/patients/search` | `app/Http/Controllers/Admin/PatientSearchController.php` |
| 7 | Chat CSKH | `/chat/*`, `/admin/chatroom/*` | `app/Http/Controllers/ChatController.php`, `app/Http/Controllers/Admin/ChatRoomController.php` |
| 8 | Nhat ky hoat dong | `/admin/activity-logs` | `app/Http/Controllers/Admin/ActivityLogController.php` |
| 9 | Ban tin benh vien | `/news`, `/admin/news` | `app/Http/Controllers/NewsController.php`, `app/Http/Controllers/Admin/NewsController.php` |
| 10 | Thong bao | `/notifications/*` | `app/Http/Controllers/NotificationController.php`, `app/Services/NotificationService.php` |

## 1. Dat lich hen

### Route

Nam trong `routes/web.php`:

| Method | URL | Route name | Y nghia |
|---|---|---|---|
| GET | `/dat-lich` | `appointments.create` | Mo form dat lich |
| POST | `/dat-lich` | `appointments.store` | Gui yeu cau dat lich |
| GET | `/dat-lich/schedules` | `appointments.schedules` | Lay lich bac si theo bo loc |
| GET | `/api/appointments/suggest` | `appointments.suggest` | Goi y lich hen |
| GET | `/api/appointments/timeslots` | `appointments.timeslots` | Lay khung gio trong |
| GET | `/api/appointments/queue-info` | `appointments.queue-info` | Lay thong tin hang doi cua lich |

### File lien quan

| File | Lam gi |
|---|---|
| `app/Http/Controllers/AppointmentController.php` | Nhan request dat lich, validate input, tra view/redirect/JSON. Cac ham chinh: `create`, `store`, `getSchedules`, `suggest`, `timeslots`, `getQueueInfo`. |
| `app/Services/AppointmentService.php` | Chua nghiep vu dat lich: kiem tra slot, lock du lieu khi dat lich, tao appointment, chong trung lich va chong nhieu nguoi dat cung slot. |
| `app/Models/Appointment.php` | Model bang lich hen. Luu benh nhan, bac si, schedule, ngay gio, trang thai. |
| `app/Models/DoctorSchedule.php` | Model lich lam viec cua bac si, dung de tinh slot con trong. |
| `app/Models/Doctor.php` | Model bac si, lay thong tin bac si/chuyen khoa khi dat lich. |
| `resources/views/appointments/create.blade.php` | Giao dien form dat lich. Co CSRF, field chon bac si, ngay, gio, ly do kham. |
| `resources/views/appointments/doctor-off.blade.php` | Man hinh thong bao/chuyen huong khi bac si nghi. |
| `app/Mail/AppointmentConfirmed.php` | Mail xac nhan lich hen neu luong gui mail duoc goi. |
| `tests/Feature/QueueSystemSmokeTest.php` | Co kiem tra lien quan luong appointment va queue. |

### Ghi chu ky thuat

- Input dat lich duoc validate trong controller.
- Tao lich co dung transaction/lock trong service de tranh 2 nguoi dat cung slot.
- Khi phat hien du lieu da thay doi, he thong tra thong bao va yeu cau tai lai trang.

## 2. Huy lich hen

### Route

Nam trong `routes/web.php`:

| Method | URL | Route name | Y nghia |
|---|---|---|---|
| POST | `/lich-hen/{id}/huy` | `user.appointments.cancel` | Huy lich hen cua nguoi dung |
| POST | `/lich-hen/{id}/huy` | `appointments.cancel` | Alias cu de view cu van chay |

### File lien quan

| File | Lam gi |
|---|---|
| `app/Http/Controllers/AppointmentController.php` | Ham `cancel(Request $request, int $id)` validate ly do/version, kiem tra user va goi service huy lich. |
| `app/Services/AppointmentService.php` | Ham `cancelAppointment` xu ly nghiep vu huy lich, lock appointment, kiem tra version, cap nhat trang thai. |
| `app/Exceptions/ConcurrentModificationException.php` | Exception rieng khi lich hen da bi nguoi khac thay doi truoc do. |
| `resources/views/appointments/index.blade.php` | Nut/modal huy lich, hidden `version`, hien thi warning va reload khi co xung dot du lieu. |
| `app/Mail/AppointmentCancelled.php` | Mail thong bao huy lich neu luong gui mail duoc goi. |

### Ghi chu ky thuat

- Huy lich co CSRF trong form.
- Co optimistic locking bang `version` de tranh truong hop 2 tab/2 nguoi cung thao tac.
- Loi noi bo duoc log, user chi thay thong bao than thien.

## 3. Doi lich hen

### Route

Nam trong `routes/web.php`:

| Method | URL | Route name | Y nghia |
|---|---|---|---|
| GET | `/lich-hen/{id}/doi` | `user.appointments.edit` | Mo form doi lich |
| PUT | `/lich-hen/{id}/doi` | `user.appointments.update` | Luu lich moi |
| GET | `/lich-hen/{id}/doi` | `appointments.edit` | Alias cu |
| PUT | `/lich-hen/{id}/doi` | `appointments.update` | Alias cu |

### File lien quan

| File | Lam gi |
|---|---|
| `app/Http/Controllers/AppointmentController.php` | Ham `edit` load lich can doi, ham `update` validate input va goi service doi lich. |
| `app/Services/AppointmentService.php` | Ham doi lich/reschedule: kiem tra quyen, lock lich, kiem tra slot moi, cap nhat appointment. |
| `resources/views/appointments/edit.blade.php` | Giao dien doi lich. Co hidden `version`, CSRF, method PUT va script reload khi co xung dot. |
| `app/Mail/AppointmentRescheduleMail.php` | Mail thong bao doi lich neu luong gui mail duoc goi. |

### Ghi chu ky thuat

- Doi lich dung validation server, khong tin du lieu tu frontend.
- Neu slot da bi nguoi khac dat truoc, service tra loi va controller hien thong bao.
- View giu `old()` input khi validation fail.

## 4. Xem danh sach lich hen

### Route

Nam trong `routes/web.php`:

| Method | URL | Route name | Y nghia |
|---|---|---|---|
| GET | `/lich-hen` | `user.appointments.index` | Danh sach lich hen cua user |
| GET | `/lich-hen` | `appointments.index` | Alias cu |
| GET | `/lich-hen/{id}/bac-si-nghi` | `user.appointments.doctor-off` | Trang xu ly khi bac si nghi |

### File lien quan

| File | Lam gi |
|---|---|
| `app/Http/Controllers/AppointmentController.php` | Ham `index(Request $request)` lay danh sach lich hen theo user/bac si/admin va filter. |
| `app/Services/AppointmentService.php` | Cung cap query/logic ho tro lay lich, trang thai, slot, queue info. |
| `resources/views/appointments/index.blade.php` | Man hinh danh sach lich hen, filter, nut doi/huy, thong bao loi/thanh cong. |
| `resources/views/doctors/appointments/index.blade.php` | Man hinh lich hen phia bac si neu truy cap theo doctor route. |
| `app/Models/Appointment.php` | Quan he voi patient, doctor, schedule, payment/queue neu co. |

### Ghi chu ky thuat

- Controller dung eager loading cho quan he can hien thi de giam N+1.
- Danh sach chi tra du lieu dung quyen cua user dang dang nhap.
- View escape output bang Blade `{{ }}`.

## 5. Hang doi kham benh

### Route

Nam trong `routes/web.php`:

| Method | URL | Route name | Y nghia |
|---|---|---|---|
| GET | `/queue/display` | `queue.display.index` | Man hinh chon bang hien thi hang doi |
| GET | `/queue/display/{scheduleId}` | `queue.display` | Man hinh TV/display theo lich |
| GET | `/api/queue/{scheduleId}/snapshot` | `queue.api.display` | API snapshot hang doi cho display |
| GET | `/queue/manage` | `queue.manage.index` | Le tan/admin xem danh sach ca co hang doi |
| GET | `/queue/manage/schedule/{scheduleId}` | `queue.manage.show` | Quan ly hang doi cua mot ca |
| GET | `/queue/manage/checkin` | `queue.manage.checkin` | Tim benh nhan de check-in |
| POST | `/queue/manage/checkin` | `queue.manage.checkin.store` | Tao ticket check-in |
| POST | `/queue/manage/ticket/{ticketId}/skip` | `queue.manage.ticket.skip` | Bo qua/huy ticket |
| GET | `/queue/doctor` | `queue.doctor.index` | Bac si xem hang doi cua minh |
| POST | `/queue/doctor/schedule/{scheduleId}/call-next` | `queue.doctor.call.next` | Goi benh nhan tiep theo |
| POST | `/queue/doctor/ticket/{ticketId}/start` | `queue.doctor.start` | Bat dau kham |
| POST | `/queue/doctor/ticket/{ticketId}/complete` | `queue.doctor.complete` | Hoan thanh kham |
| GET | `/admin/queue` | `admin.queue.index` | Admin dashboard hang doi |
| GET | `/admin/queue/report` | `admin.queue.report` | Bao cao hang doi |

### File lien quan

| File | Lam gi |
|---|---|
| `app/Http/Controllers/Queue/QueueManageController.php` | Le tan/admin: danh sach ca, tim benh nhan, check-in, skip ticket, API snapshot. |
| `app/Http/Controllers/Queue/QueueDoctorController.php` | Bac si/admin: xem hang doi, goi tiep, bat dau kham, hoan thanh kham. |
| `app/Http/Controllers/Queue/QueueDisplayController.php` | Man hinh hien thi cong khai va API snapshot cho TV/display. |
| `app/Http/Controllers/Admin/QueueController.php` | Dashboard va bao cao hang doi trong admin. |
| `app/Services/QueueService.php` | Nghiep vu hang doi: tao ticket, tinh so thu tu, goi tiep, start, complete, skip, snapshot. |
| `app/Models/QueueTicket.php` | Model ticket hang doi. |
| `app/Models/QueueCounter.php` | Model dem so thu tu theo ngay/lich. |
| `app/Observers/QueueTicketObserver.php` | Observer khi ticket thay doi, dung cho event/cap nhat realtime neu duoc cau hinh. |
| `app/Events/QueueUpdated.php` | Event thong bao hang doi thay doi. |
| `app/Http/Middleware/CheckQueueRole.php` | Chan quyen truy cap theo role hang doi. |
| `resources/views/queue/display.blade.php` | Man hinh display mot hang doi. |
| `resources/views/queue/display-list.blade.php` | Danh sach display/cac ca. |
| `resources/views/queue/manage/index.blade.php` | Danh sach ca cho le tan/admin. |
| `resources/views/queue/manage/show.blade.php` | Man hinh quan ly ticket trong mot ca. |
| `resources/views/queue/manage/checkin.blade.php` | Tim benh nhan va check-in. |
| `resources/views/queue/manage/_current_ticket.blade.php` | Partial ticket hien tai phia quan ly. |
| `resources/views/queue/doctor/index.blade.php` | Man hinh bac si xu ly hang doi. |
| `resources/views/queue/doctor/_current.blade.php` | Partial ticket hien tai phia bac si. |
| `resources/views/admin/queue/dashboard.blade.php` | Dashboard hang doi phia admin. |
| `resources/views/admin/queue/show.blade.php` | Chi tiet mot ca phia admin. |
| `resources/views/admin/queue/report.blade.php` | Bao cao hang doi. |
| `tests/Feature/QueueSystemSmokeTest.php` | Test smoke cho luong hang doi, validation va thao tac ticket. |

### Ghi chu ky thuat

- Route quan ly co middleware `auth` va `check_queue_role`.
- Cac thao tac ghi quan trong dung transaction/lock trong `QueueService`.
- Neu 2 nguoi cung thao tac mot ticket, nguoi sau se nhan thong bao va trang duoc load lai.

## 6. Tim kiem benh nhan

### Route

Nam trong `routes/web.php`:

| Method | URL | Route name | Y nghia |
|---|---|---|---|
| GET | `/admin/patients/search` | `admin.patients.search` | Man hinh tim kiem benh nhan nang cao |
| GET | `/admin/patients/search/results` | `admin.patients.search.results` | API/HTML partial danh sach ket qua |
| GET | `/admin/patients/{id}/detail` | `admin.patients.detail` | Chi tiet benh nhan |
| POST | `/admin/patients/ai-search` | `admin.patients.ai-search` | Tim kiem bang AI tu ngon ngu tu nhien |

### File lien quan

| File | Lam gi |
|---|---|
| `app/Http/Controllers/Admin/PatientSearchController.php` | Hien thi trang search, validate filter, query benh nhan, chi tiet benh nhan, goi Gemini AI va fallback khi AI loi. |
| `resources/views/admin/patients/search.blade.php` | Giao dien tim kiem nang cao, tab tim thuong/tim AI, goi AJAX va render ket qua. |
| `resources/views/admin/patients/partials/patient-card.blade.php` | Card hien thi mot benh nhan trong danh sach. |
| `resources/views/admin/patients/partials/patient-detail.blade.php` | Partial chi tiet benh nhan. |
| `app/Models/User.php` | Model user/benh nhan. |
| `app/Models/Appointment.php` | Lay lich hen gan voi benh nhan. |
| `app/Models/Doctor.php` | Loc theo bac si tung tham kham. |
| `config/services.php` | Cau hinh `gemini.api_key` va CA bundle cho AI search/chat. |
| `tests/Feature/PatientSearchTest.php` | Test validate input, empty filter, fallback AI khi Gemini loi. |

### Ghi chu ky thuat

- Tim kiem AI se phan tich cau tu nhien thanh filter, sau do goi lai danh sach ket qua.
- Neu Gemini timeout/503/loi key, he thong fallback bo loc co ban de khong lam mat danh sach.
- Filter rong duoc loai bo truoc khi query de tranh ket qua bi ve 0 sai.

## 7. Chat CSKH

### Route

Nam trong `routes/web.php`:

| Method | URL | Route name | Y nghia |
|---|---|---|---|
| GET | `/chat` | `chat.index` | Trang/chat widget phia user |
| POST | `/chat/room` | `chat.room` | Lay hoac tao phong chat |
| GET | `/chat/messages/{roomId}` | `chat.messages` | Lay tin nhan phong chat |
| POST | `/chat/send` | `chat.send` | Gui tin nhan user |
| DELETE | `/chat/messages/{messageId}` | `chat.recall` | Thu hoi tin nhan user |
| GET | `/admin/chatroom` | `admin.chatroom.index` | Man hinh CSKH/admin |
| GET | `/admin/chatroom/list` | `admin.chatroom.list` | API danh sach phong chat |
| GET | `/admin/chatroom/{roomId}/messages` | `admin.chatroom.messages` | API tin nhan phong chat |
| POST | `/admin/chatroom/{roomId}/send` | `admin.chatroom.send` | Admin gui tin nhan |
| POST | `/admin/chatroom/{roomId}/close` | `admin.chatroom.close` | Dong phong chat |
| DELETE | `/admin/chatroom/{roomId}` | `admin.chatroom.delete` | Xoa phong chat |
| DELETE | `/admin/chatroom/messages/{messageId}` | `admin.chatroom.deleteMessage` | Xoa tin nhan |

### File lien quan

| File | Lam gi |
|---|---|
| `app/Http/Controllers/ChatController.php` | API chat phia user: tao room, lay messages, gui tin, thu hoi tin. |
| `app/Http/Controllers/Admin/ChatRoomController.php` | API/man hinh CSKH: danh sach room, lay tin, gui tin, dong/xoa room/message. |
| `app/Services/GeminiChatService.php` | Goi Gemini de tra loi tu dong, xu ly TLS CA bundle, retry/fallback va log loi da che key. |
| `app/Models/ChatRoom.php` | Model phong chat. |
| `app/Models/ChatMessage.php` | Model tin nhan. |
| `resources/views/components/chat-widget.blade.php` | Widget chat noi tren giao dien user. |
| `resources/views/admin/chatroom/index.blade.php` | Man hinh quan ly chat cua admin/CSKH. |
| `config/services.php` | Cau hinh `GEMINI_API_KEY` va `GEMINI_CA_BUNDLE`. |
| `tests/Feature/ChatCskhSmokeTest.php` | Test smoke cho chat va validate message rong. |

### Ghi chu ky thuat

- Response chat la JSON co cau truc de frontend xu ly.
- Khong in API key ra response; log loi AI phai che key.
- Khi AI loi, user nhan thong bao chung de CSKH ho tro tiep.

## 8. Nhat ky hoat dong

### Route

Nam trong `routes/web.php`:

| Method | URL | Route name | Y nghia |
|---|---|---|---|
| GET | `/admin/activity-logs` | `admin.activity-logs.index` | Danh sach nhat ky hoat dong |
| GET | `/admin/activity-logs/{activityLog}` | `admin.activity-logs.show` | Chi tiet mot log |

### File lien quan

| File | Lam gi |
|---|---|
| `app/Http/Controllers/Admin/ActivityLogController.php` | Hien thi danh sach, filter, chi tiet log. |
| `app/Services/ActivityLogService.php` | Service tao log hoat dong, gom du lieu actor, action, subject, IP, user agent. |
| `app/Models/ActivityLog.php` | Model bang activity logs. |
| `resources/views/admin/activity-logs/index.blade.php` | Giao dien danh sach log, bo loc va phan trang. |
| `resources/views/admin/activity-logs/show.blade.php` | Giao dien xem chi tiet log. |

### Ghi chu ky thuat

- Route nam trong group admin co middleware `auth` va `is_admin`.
- Log dung de truy vet thao tac quan trong trong he thong.
- Nen khong hien thi thong tin nhay cam nhu password, token, API key trong metadata log.

## 9. Ban tin benh vien

### Route

Nam trong `routes/web.php`:

| Method | URL | Route name | Y nghia |
|---|---|---|---|
| GET | `/news` | `news.index` | Danh sach tin cong khai |
| GET | `/news/{id}` | `news.show` | Chi tiet tin cong khai |
| GET | `/admin/news` | `admin.news.index` | Admin danh sach ban tin |
| GET | `/admin/news/create` | `admin.news.create` | Form tao ban tin |
| POST | `/admin/news` | `admin.news.store` | Luu ban tin moi |
| GET | `/admin/news/{id}/edit` | `admin.news.edit` | Form sua ban tin |
| PUT/PATCH | `/admin/news/{id}` | `admin.news.update` | Cap nhat ban tin |
| DELETE | `/admin/news/{id}` | `admin.news.destroy` | Xoa ban tin |
| PATCH | `/admin/news/{id}/toggle` | `admin.news.toggle` | Bat/tat public |
| POST | `/admin/news/{id}/send-email` | `admin.news.sendEmail` | Gui email ban tin |

### File lien quan

| File | Lam gi |
|---|---|
| `app/Http/Controllers/NewsController.php` | Trang tin cong khai, validate category va chi hien tin published. |
| `app/Http/Controllers/Admin/NewsController.php` | CRUD ban tin admin, upload thumbnail, toggle publish, gui email, lock/version chong thao tac dong thoi. |
| `app/Http/Requests/StoreNewsRequest.php` | Validate input tao/sua ban tin va file upload. |
| `app/Models/HospitalNews.php` | Model ban tin benh vien. |
| `app/Mail/NewsPublishedMail.php` | Mail gui ban tin. |
| `resources/views/news/index.blade.php` | Danh sach tin cong khai. |
| `resources/views/news/show.blade.php` | Chi tiet tin cong khai. |
| `resources/views/admin/news/index.blade.php` | Danh sach/quan ly ban tin admin. |
| `resources/views/admin/news/create.blade.php` | Form tao ban tin. |
| `resources/views/admin/news/edit.blade.php` | Form sua ban tin, co hidden `version`. |
| `resources/views/emails/news-published.blade.php` | Template email ban tin. |
| `resources/views/mail/news-published.blade.php` | Template mail khac/tuong thich neu duoc dung. |

### Ghi chu ky thuat

- Admin route co middleware `auth` va `is_admin`.
- Upload thumbnail can validate mime/size trong request/controller.
- Update/delete/toggle/email co version de bao nguoi sau load lai khi ban tin da doi.

## 10. Thong bao

### Route

Nam trong `routes/web.php`:

| Method | URL | Route name | Y nghia |
|---|---|---|---|
| GET | `/notifications` | `notifications.index` | Trang danh sach thong bao |
| GET | `/notifications/dropdown` | `notifications.dropdown` | API dropdown thong bao |
| GET | `/notifications/unread-count` | `notifications.unread-count` | API dem chua doc |
| POST | `/notifications/mark-all-read` | `notifications.mark-all-read` | Danh dau tat ca da doc |
| GET | `/notifications/{notification}` | `notifications.show` | Xem chi tiet thong bao |
| POST | `/notifications/{notification}/mark-read` | `notifications.mark-read` | Danh dau mot thong bao da doc |

### File lien quan

| File | Lam gi |
|---|---|
| `app/Http/Controllers/NotificationController.php` | Hien danh sach, dropdown, unread count, xem chi tiet, mark read/all read. |
| `app/Services/NotificationService.php` | Tao thong bao cho 1 user, nhieu user, theo role, tat ca; dem/chuyen trang thai da doc. |
| `app/Models/Notification.php` | Model noi dung thong bao. |
| `app/Models/NotificationUser.php` | Pivot/model trang thai thong bao theo tung user. |
| `resources/views/notifications/index.blade.php` | Trang danh sach thong bao. |
| `resources/views/notifications/show.blade.php` | Trang chi tiet thong bao. |
| `resources/views/components/notification-bell.blade.php` | Chuong thong bao tren layout, goi dropdown/unread count. |
| `resources/views/layouts/app.blade.php` | Layout user co gan component thong bao. |
| `resources/views/layouts/admin.blade.php` | Layout admin co gan component thong bao. |

### Ghi chu ky thuat

- Tat ca route thong bao nam trong middleware `auth`.
- Service tach logic tao thong bao de controller khac co the dung lai.
- Khi tra JSON cho chuong thong bao, can giu format gon va khong lo thong tin he thong.

## File cau hinh va ha tang lien quan ca 10 chuc nang

| File | Lam gi |
|---|---|
| `routes/web.php` | Khai bao route web cua 10 chuc nang. Day la noi xem URL nao tro den controller nao. |
| `bootstrap/app.php` | Cau hinh xu ly exception/JSON error chung. Dung de khong lo stack trace va format validation/API error. |
| `config/services.php` | Cau hinh dich vu ngoai nhu Gemini. Key lay tu `.env`, khong hard-code trong code. |
| `.env` | Chua bien moi truong nhu `APP_DEBUG`, `GEMINI_API_KEY`, database. Khong commit file nay. |
| `resources/views/layouts/app.blade.php` | Layout user, flash message, reload khi co xung dot thao tac. |
| `resources/views/layouts/admin.blade.php` | Layout admin, flash message, reload khi co xung dot thao tac. |
| `app/Exceptions/ConcurrentModificationException.php` | Exception dung khi phat hien du lieu da bi nguoi khac them/sua/xoa truoc. |

## Test da co cho cac chuc nang nay

| Test file | Kiem tra chuc nang |
|---|---|
| `tests/Feature/QueueSystemSmokeTest.php` | Hang doi kham benh, check-in, validate input, thao tac ticket. |
| `tests/Feature/PatientSearchTest.php` | Tim kiem benh nhan, validate filter, AI fallback khi Gemini loi. |
| `tests/Feature/ChatCskhSmokeTest.php` | Chat CSKH, tao room/gui tin/validate tin nhan rong. |

## Cach doc code nhanh

1. Muon biet URL nao goi file nao: mo `routes/web.php`.
2. Muon biet request vao duoc validate va redirect/JSON ra sao: mo controller tuong ung trong `app/Http/Controllers`.
3. Muon biet nghiep vu that su nhu lock, transaction, tao ticket/dat lich: mo service trong `app/Services`.
4. Muon sua giao dien: mo file trong `resources/views`.
5. Muon biet cot/quan he du lieu: mo model trong `app/Models`.
6. Muon test lai nhanh cac phan da fix: chay cac test trong `tests/Feature`.
