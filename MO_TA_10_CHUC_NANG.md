# Mô tả 10 chức năng và màn hình giao diện

Tài liệu này chỉ tổng hợp theo các file đang có trong mã nguồn dự án.

Nguồn đọc chính: `routes/web.php`, `routes/medical_records.php`, `routes/medical_history.php`, `routes/nutrition.php`, `routes/health_tracking.php`, các controller, service, request, model và view liên quan.

## Phần 1. Mô tả toàn bộ 10 chức năng

### Chức năng 1. Quản lý tiền sử sức khỏe và dị ứng

| Mục | Nội dung |
|---|---|
| Chức năng 1 | Quản lý tiền sử sức khỏe và dị ứng |
| File thực thi | `app/Http/Controllers/HealthBackgroundController.php`; view `resources/views/health_background/index.blade.php`; model `app/Models/HealthBackground.php` |
| Mô tả chi tiết | Hệ thống lấy bản ghi `HealthBackground` theo `user_id` của người dùng đang đăng nhập để hiển thị nhóm máu, yếu tố Rh, chiều cao, cân nặng, BMI, dị ứng thực phẩm, dị ứng thuốc, bệnh mãn tính và bệnh mãn tính khác. Khi người dùng gửi form, controller validate `height`, `weight` là số không âm, tính BMI từ chiều cao/cân nặng, map dữ liệu từ form sang các cột trong bảng `health_backgrounds`, sau đó `updateOrCreate` theo `user_id`. Controller cũng có luồng `showPatient($patientId)` cho bác sĩ/admin xem tiền sử của bệnh nhân cụ thể. |
| Định danh | Người dùng đang đăng nhập qua `Auth::id()`. Luồng bác sĩ/admin xem bệnh nhân dùng `patientId` trên URL và kiểm tra `role_id` thuộc `[1, 2]`. |
| Ngoại lệ / Xử lý lỗi | Nếu `height`, `weight` không hợp lệ Laravel trả lỗi validation. Nếu người không phải bác sĩ/admin gọi `showPatient`, hệ thống `abort(403)`. View hiện có đoạn dùng `$$healthData` trong select nhóm máu/Rh, đây là hiện trạng trong code. |

### Chức năng 2. Hồ sơ cá nhân

| Mục | Nội dung |
|---|---|
| Chức năng 2 | Hồ sơ cá nhân |
| File thực thi | `app/Http/Controllers/ProfileController.php`; requests `UpdateProfileRequest.php`, `UpdatePasswordRequest.php`; views `resources/views/profile/show.blade.php`, `edit.blade.php`, `password.blade.php` |
| Mô tả chi tiết | Trang hồ sơ hiển thị thông tin người dùng đang đăng nhập gồm avatar, họ tên, email, vai trò, số điện thoại, giới tính, ngày sinh, địa chỉ và các liên kết tiện ích tài khoản. Người dùng có thể mở form chỉnh sửa để cập nhật họ tên, email, số điện thoại, địa chỉ, ngày sinh, giới tính, avatar. Khi upload avatar mới, hệ thống xóa avatar cũ trên disk `public`, lưu file mới vào thư mục `avatars` và cập nhật `avatar_url`. Người dùng cũng có thể mở form đổi mật khẩu; hệ thống kiểm tra mật khẩu hiện tại bằng `Hash::check`, sau đó lưu mật khẩu mới đã hash. Có route xóa avatar, khi có avatar hiện tại thì xóa file và set `avatar_url = null`. |
| Định danh | Người dùng đang đăng nhập qua `Auth::user()` / `Auth::id()`. Email unique theo bảng `users`, bỏ qua chính `user_id` hiện tại. |
| Ngoại lệ / Xử lý lỗi | Nếu chưa đăng nhập thì FormRequest không authorize. Cập nhật profile validate họ tên bắt buộc, email hợp lệ/unique, phone đúng regex, ngày sinh trước hôm nay, gender thuộc `Nam/Nữ/Khác`, avatar là ảnh jpg/jpeg/png/webp tối đa 2MB. Đổi mật khẩu validate mật khẩu hiện tại bắt buộc, mật khẩu mới tối thiểu 8 ký tự, confirmed và khác mật khẩu hiện tại; nếu mật khẩu hiện tại sai trả lỗi `current_password`. |

### Chức năng 3. Thẻ thành viên

| Mục | Nội dung |
|---|---|
| Chức năng 3 | Thẻ thành viên |
| File thực thi | `app/Http/Controllers/MembershipController.php`; model `app/Models/Membershipcard.php`; view `resources/views/Membership/membershipcards.blade.php` |
| Mô tả chi tiết | Khi vào trang thẻ thành viên, hệ thống lấy user đang đăng nhập. Nếu chưa có thẻ, `MembershipCard::firstOrCreate` tạo thẻ với `points = 0`, `total_spent = 0`, hạng mặc định Đồng, số thẻ dạng `MB-YYYYMMDD-USERID`, ngày hết hạn sau 1 năm. Sau đó controller đồng bộ điểm theo `floor(total_spent / 1000)` và lưu lại nếu điểm/hạng khác dữ liệu đang có. Model tự xếp hạng theo tổng chi tiêu: Đồng, Bạc, Vàng, Kim Cương; đồng thời cung cấp accessor phần trăm tiến trình, số tiền còn thiếu và hạng tiếp theo. |
| Định danh | Người dùng đang đăng nhập qua `Auth::user()`, thẻ gắn với `user_id = $user->user_id`. |
| Ngoại lệ / Xử lý lỗi | Nếu chưa đăng nhập, controller redirect về `login` với thông báo. Dữ liệu phụ trong controller hiện là dữ liệu giả lập: `visit_count = 48`, `pending_points = 200`, `voucher_count = 3`, `saved_money = 890k`. |

### Chức năng 4. Nhắc nhở tuân thủ điều trị

| Mục | Nội dung |
|---|---|
| Chức năng 4 | Nhắc nhở tuân thủ điều trị |
| File thực thi | `app/Http/Controllers/Patient/TreatmentReminderController.php`; service `app/Services/TreatmentReminderService.php`; requests `ConfirmReminderRequest.php`, `ToggleInstructionRequest.php`; views `resources/views/patient/treatment_reminder/*` |
| Mô tả chi tiết | Dashboard lấy các nhắc nhở hôm nay của bệnh nhân, hướng dẫn điều trị tại nhà đang active, thống kê tuân thủ 7 ngày, thống kê tháng hiện tại và lịch hẹn tiếp theo. Người dùng có thể xác nhận đã uống thuốc/hoàn thành nhắc nhở; service kiểm tra reminder thuộc đúng `user_id` và tạo `TreatmentConfirmation` nếu chưa có. Người dùng cũng có thể tick/untick hướng dẫn tại nhà; hệ thống tạo hoặc cập nhật `InstructionDailyCheck` theo `instruction_id`, `user_id`, `checked_date = hôm nay`. API `/treatment/report` trả thống kê tuân thủ tháng. |
| Định danh | Người dùng đang đăng nhập qua `Auth::id()`. Confirm reminder authorize bằng route param `reminder` và so khớp `user_id`. |
| Ngoại lệ / Xử lý lỗi | Nếu reminder không thuộc user hoặc không tồn tại sẽ không authorize / `firstOrFail`. Toggle instruction validate `instruction_id` bắt buộc, integer và tồn tại trong `treatment_home_instructions`. View có xử lý rollback checkbox nếu gọi API toggle lỗi. |

### Chức năng 5. Lịch sử khám `medical_history`

| Mục | Nội dung |
|---|---|
| Chức năng 5 | Lịch sử khám của bệnh nhân |
| File thực thi | `app/Http/Controllers/MedicalHistoryController.php`; route `routes/medical_history.php`; view `resources/views/medical_history/index.blade.php`; service `MedicalRecordService.php` |
| Mô tả chi tiết | Hệ thống chỉ cho bệnh nhân xem lịch sử khám của chính mình. Controller kiểm tra đăng nhập và `role_id = 3`, lấy các filter từ request gồm `search`, `visit_type`, `status`, `date_from`, `date_to`, `sort_by`, `sort_order`, `per_page`, sau đó gọi `MedicalRecordService::getPatientRecords($user->user_id, $filters)`. Service truy vấn bảng `medical_records`, eager load `vitalSigns`, `diagnoses`, `doctor`, lọc theo `patient_id`, áp dụng tìm kiếm theo mã phiếu/tên bệnh nhân/tên bác sĩ, lọc loại khám, trạng thái, khoảng ngày, sắp xếp và phân trang. View hiển thị bảng lịch sử khám, link sang chi tiết hồ sơ bệnh án. |
| Định danh | Người dùng đăng nhập và là bệnh nhân `role_id = 3`; dữ liệu giới hạn theo `patient_id = $user->user_id`. |
| Ngoại lệ / Xử lý lỗi | Nếu chưa đăng nhập redirect `login`. Nếu role không phải bệnh nhân redirect `home`. Nếu không có dữ liệu, view hiển thị trạng thái rỗng "Không tìm thấy phiếu khám nào". |

### Chức năng 6. Hồ sơ bệnh án / phiếu khám

| Mục | Nội dung |
|---|---|
| Chức năng 6 | Hồ sơ bệnh án / phiếu khám |
| File thực thi | `app/Http/Controllers/MedicalRecordController.php`; service `MedicalRecordService.php`; requests `StoreMedicalRecordRequest.php`, `UpdateMedicalRecordRequest.php`; views `resources/views/medical-records/*` |
| Mô tả chi tiết | Chức năng quản lý danh sách, tạo, xem chi tiết, sửa, xóa, in hồ sơ bệnh án. Danh sách thay đổi theo vai trò: bệnh nhân xem hồ sơ của mình, bác sĩ xem hồ sơ mình tạo hoặc hồ sơ bệnh nhân cụ thể, admin xem tất cả. Tạo hồ sơ dùng transaction để tạo `MedicalRecord`, sinh `record_code`, lưu chỉ số sinh tồn, dị ứng, chẩn đoán, đơn thuốc, chỉ định xét nghiệm/hình ảnh, file đính kèm; nếu có `appointment_id`, lịch hẹn phù hợp được cập nhật trạng thái `Đã Khám`. Xem chi tiết kiểm tra quyền bằng `canViewRecord`: admin xem tất cả, bác sĩ chỉ xem hồ sơ mình phụ trách, bệnh nhân chỉ xem hồ sơ của mình. Có API cập nhật/xóa kết quả xét nghiệm và upload/xóa file đính kèm. |
| Định danh | Hồ sơ định danh bằng `record_id`. Người dùng định danh bằng `Auth::user()`, `role_id` và `user_id`. File đính kèm định danh bằng `attachment_id`; chỉ định xét nghiệm định danh bằng `order_id`. |
| Ngoại lệ / Xử lý lỗi | Nếu chưa đăng nhập redirect/login hoặc JSON 401. Nếu không có quyền redirect hoặc JSON 403. Không tìm thấy record/order/attachment trả redirect lỗi hoặc JSON 500/404 tùy luồng. Tạo/cập nhật validate bắt buộc bệnh nhân, bác sĩ, ngày khám, loại khám, lý do khám, chỉ số sinh tồn, ít nhất 1 chẩn đoán; file đính kèm tối đa 10MB và chỉ nhận pdf/jpg/jpeg/png/doc/docx. |

### Chức năng 7. Thư viện bài tập phục hồi `rehab_exercises`

| Mục | Nội dung |
|---|---|
| Chức năng 7 | Thư viện bài tập phục hồi |
| File thực thi | `app/Http/Controllers/RehabExerciseController.php`; model `app/Models/RehabExercise.php`; migration `database/migrations/2026_05_20_000001_create_rehab_exercises_table.php`; views `resources/views/patient/rehab_exercises.blade.php`, `rehab_exercise_detail.blade.php` |
| Mô tả chi tiết | Trang danh sách lấy các bài tập có `status = published`, có thể lọc theo `category`, sắp xếp mới nhất và phân trang 9 bài/trang. Danh mục đang có: tất cả bài tập, cơ-xương-khớp, thần kinh-đột quỵ, chấn thương thể thao, hô hấp-tim mạch. Trang chi tiết chỉ cho xem bài published, tăng `view_count`, lấy tối đa 3 bài liên quan cùng category và khác bài hiện tại. Model cung cấp nhãn category, nhãn phase, nhãn status, URL thumbnail hoặc ảnh placeholder. |
| Định danh | Route `rehab.show` dùng route model binding `RehabExercise $exercise`; danh sách dùng query `category`. |
| Ngoại lệ / Xử lý lỗi | Nếu bài chi tiết không phải `published`, controller `abort(404)`. Nếu danh sách không có bài published, view hiển thị "Chua co bai tap cong khai." |

### Chức năng 8. Chế độ dinh dưỡng

| Mục | Nội dung |
|---|---|
| Chức năng 8 | Chế độ dinh dưỡng |
| File thực thi | Không có class đúng tên `NutritionController`; mã nguồn hiện tách thành `AdminNutritionController.php` và `PatientNutritionController.php`; route `routes/nutrition.php`; views `resources/views/nutrition/*`; models `NutritionArticle`, `DiseaseNutritionRule`, `Food`, `MealLog` |
| Mô tả chi tiết | Phân hệ admin/bác sĩ quản lý bài viết dinh dưỡng, quy tắc gợi ý theo bệnh và danh mục thực phẩm/calo. Bài viết có title, content, target disease, status và slug duy nhất. Quy tắc dinh dưỡng map bệnh hoặc mã ICD với thực phẩm, loại gợi ý `should_eat` hoặc `should_avoid`, có kiểm tra trùng bệnh-thực phẩm. Danh mục thực phẩm lưu tên, calo/100g, mô tả, trạng thái. Phân hệ bệnh nhân lấy tối đa 3 chẩn đoán gần nhất từ `diagnoses` join `medical_records`, tìm rule theo tên bệnh/ICD để tạo danh sách thực phẩm nên dùng/nên tránh, lấy nhật ký ăn hôm nay, tính tổng calo, tỷ lệ mục tiêu 2000 kcal/ngày, calo theo bữa, danh sách thực phẩm active và bài viết lời khuyên theo bệnh; nếu không có bài phù hợp thì lấy bài published mới nhất. |
| Định danh | Admin/bác sĩ truy cập qua middleware `auth`, `role:1,2`. Bệnh nhân dùng `Auth::id()` để lấy chẩn đoán và meal log. Bài viết dùng `article_id`, rule dùng `rule_id`, food dùng `food_id`, meal log dùng `log_id`. |
| Ngoại lệ / Xử lý lỗi | Admin validate bài viết bắt buộc title/content/status. Rule validate tên bệnh, food tồn tại, loại gợi ý hợp lệ và chống trùng. Food validate tên unique, calo integer 0-5000, status 0/1. Meal log validate food tồn tại, meal type thuộc breakfast/lunch/dinner/snack, gram 1-5000; xóa meal log kiểm tra bản ghi thuộc user hiện tại, sai quyền `abort(403)`. |

### Chức năng 9. Nhật ký sức khỏe chủ động

| Mục | Nội dung |
|---|---|
| Chức năng 9 | Nhật ký sức khỏe chủ động |
| File thực thi | `app/Http/Controllers/HealthTrackingController.php`; service `HealthRiskService.php`; request `HealthTrackingRequest.php`; policy `HealthTrackingPolicy.php`; views `resources/views/health-tracking/*`; model `HealthTracking.php` |
| Mô tả chi tiết | Danh sách nhật ký sức khỏe cho phép bệnh nhân xem bản ghi của mình, bác sĩ/admin xem theo policy. Controller tạo query `HealthTracking::with('patient')`, nếu user là bệnh nhân thì lọc `patient_id`, có filter `risk_level`, `date_from`, `date_to`. Summary đếm tổng, normal, warning, danger; danh sách phân trang 10. Bệnh nhân được tạo/sửa/xóa. Khi lưu, request validate chỉ số, service phân tích cảnh báo theo huyết áp, SpO2, nhịp tim, đường huyết, sau đó lưu `risk_level`, `risk_warnings`, `version = 1`. Khi sửa dùng transaction, lock bản ghi, kiểm tra optimistic locking qua `version`, phân tích lại risk và tăng version. API realtime `checkRisk` nhận các chỉ số và trả warnings JSON. |
| Định danh | Bản ghi định danh bằng model binding `HealthTracking $healthTracking`. Bệnh nhân ghi dữ liệu theo `patient_id = auth()->user()->user_id`. |
| Ngoại lệ / Xử lý lỗi | Policy: bệnh nhân/bác sĩ/admin được xem danh sách; bác sĩ/admin xem bản ghi; bệnh nhân chỉ sửa/xóa bản ghi của chính mình. Request bắt buộc systolic, diastolic, heart_rate, spo2, weight, blood_sugar theo khoảng min/max; PUT bắt buộc version. Nếu version lệch, trả về form với `conflict` và thông báo thời điểm cập nhật mới nhất. Lỗi lưu/cập nhật được log và trả flash error. |

### Chức năng 10. Tài liệu y khoa `MedicalDocument`

| Mục | Nội dung |
|---|---|
| Chức năng 10 | Lưu trữ và tra cứu tài liệu y khoa cá nhân |
| File thực thi | `app/Http/Controllers/Documentcontroller.php` class `DocumentController`; model `app/Models/MedicalDocument.php`; migration `2024_01_01_000017_create_medical_documents_table.php`; views `resources/views/documents/index.blade.php`, `edit.blade.php` |
| Mô tả chi tiết | Trang tài liệu lấy tài liệu của user hiện tại, sắp xếp theo `uploaded_at`, tìm kiếm theo tên file, lọc category và period, phân trang 6. Hệ thống tính tổng số tài liệu, tổng dung lượng file trên disk public và số lượng theo từng category. Upload validate file jpg/jpeg/png/pdf tối đa 20MB và category bắt buộc, lưu file vào `storage/app/public/documents`, tạo `MedicalDocument` với `user_id`, `record_id = 1`, `doc_type`, `doc_name`, `file_path`, `uploaded_at`. Có xem inline file, tải xuống, edit category/ngày tài liệu và xóa file khỏi disk rồi xóa record. Bác sĩ/admin có route xem tài liệu của bệnh nhân cụ thể qua `indexPatient`. |
| Định danh | Tài liệu định danh bằng route model binding `MedicalDocument $document`, khóa chính `doc_id`, bảng model khai báo `medicaldocuments`. User hiện tại qua `Auth::id()` / `Auth::user()`. |
| Ngoại lệ / Xử lý lỗi | Nếu file không tồn tại trên disk khi xem/tải, `abort(404)`. `authorizeDocument` cho admin/bác sĩ role `[1,2]` xem tất cả; bệnh nhân chỉ xem tài liệu có `user_id` trùng `user_id` hiện tại, sai quyền `abort(403)`. Edit validate category thuộc `xet_nghiem,hinh_anh,don_thuoc,chuyen_vien,khac`, ngày tài liệu nullable/date. |

## Phần 2. Mô tả màn hình thiết kế giao diện

### Màn hình 1. Quản lý Tiền Sử & Dị Ứng

| STT | Phần tử | Mô tả | Yêu cầu | Thông tin lỗi |
|---:|---|---|---|---|
| 1 | Thông báo thành công | Hiển thị `session('success')` sau khi lưu. | Có flash success. | Không có flash thì không hiển thị. |
| 2 | Cảnh báo dị ứng | Khối cảnh báo yêu cầu khai báo đầy đủ và chính xác, nhấn mạnh thông tin dị ứng cho bác sĩ. | Luôn hiển thị trên đầu form. | Không có xử lý lỗi riêng. |
| 3 | Nhóm máu | Select các giá trị `O+`, `O-`, `A+`, `A-`, `B+`, `B-`, `AB+`, `AB-`. | Gửi field `nhommau`. | View hiện có cú pháp `$$healthData` trong selected value. |
| 4 | Yếu tố Rh | Select `positive` và `negative`. | Gửi field `yeuto_rh`. | View hiện có cú pháp `$$healthData` trong selected value. |
| 5 | Chiều cao / Cân nặng | Input number nhập cm và kg, dùng để tính BMI khi lưu. | `height`, `weight` nullable, numeric, min 0. | Laravel validation nếu nhập sai kiểu hoặc số âm. |
| 6 | BMI hiện tại | Hiển thị nhóm máu và BMI đang lưu. | Dữ liệu từ `$healthData`. | Nếu chưa có dữ liệu, BMI hiển thị 0. |
| 7 | Dị ứng thực phẩm / thuốc | Input text cho `food_allergies`, `drug_allergies`. | Không có rule bắt buộc trong controller. | Không có lỗi riêng trong view. |
| 8 | Bệnh mãn tính | Checkbox 12 bệnh mãn tính và textarea bệnh khác. | Gửi mảng `chronic_diseases[]` và `other_chronic_diseases`. | Không có validation riêng trong controller. |
| 9 | Nút Lưu thông tin | Submit POST `health.store`. | Có CSRF. | Nếu validate lỗi, Laravel trả về form kèm errors mặc định. |
| 10 | Nút Quay lại | Link về `profile.show`. | Dùng để rời màn hình. | Không có xử lý lỗi. |

### Màn hình 2. Hồ sơ cá nhân

| STT | Phần tử | Mô tả | Yêu cầu | Thông tin lỗi |
|---:|---|---|---|---|
| 1 | Trang xem hồ sơ | Hiển thị avatar, trạng thái, họ tên, email và chip vai trò theo `role_id`. | User đang đăng nhập. | Không có user thì controller/auth middleware xử lý. |
| 2 | Thông tin cá nhân | Hiển thị họ tên, email, số điện thoại, giới tính, ngày sinh, địa chỉ. | Dữ liệu từ `$user`. | Field trống hiển thị dấu gạch ngang. |
| 3 | Tiện ích tài khoản | Link tiền sử & dị ứng, thẻ thành viên, liên hệ khẩn cấp, nhật ký sức khỏe, kho tài liệu. | Click link theo route trong view. | Link tiền sử hiện dùng `route('health.store')` trong view show, trong route hiện `health.store` là POST. |
| 4 | Form chỉnh sửa avatar | Preview avatar, upload ảnh mới, nút xóa ảnh hiện tại nếu có. | Avatar jpg/jpeg/png/webp, tối đa 2MB. | Hiển thị `@error('avatar')`. |
| 5 | Form thông tin cá nhân | Họ tên, email, phone, ngày sinh, giới tính radio, địa chỉ. | Họ tên và email bắt buộc; ngày sinh trước hôm nay; gender trong danh sách. | Hiển thị `@error(...)` dưới từng field. |
| 6 | Nút Hủy / Lưu thay đổi | Hủy về `profile.show`, lưu PUT `profile.update`. | Có CSRF và method PUT. | Validation lỗi giữ old input. |
| 7 | Form đổi mật khẩu | Nhập mật khẩu hiện tại, mật khẩu mới, xác nhận mật khẩu mới. | Mật khẩu mới tối thiểu 8 ký tự, confirmed, khác mật khẩu hiện tại. | Lỗi hiện dưới field; nếu mật khẩu hiện tại sai trả lỗi `current_password`. |
| 8 | Toggle xem mật khẩu | Nút icon đổi input password/text. | Chạy JS `togglePw`. | Không có lỗi riêng. |
| 9 | Thanh độ mạnh mật khẩu | Tính theo độ dài, chữ hoa, số, ký tự đặc biệt. | Chạy JS `checkStrength`. | Chỉ là cảnh báo UI, không thay thế validation server. |

### Màn hình 3. Thẻ Thành Viên - 4PM CLINIC

| STT | Phần tử | Mô tả | Yêu cầu | Thông tin lỗi |
|---:|---|---|---|---|
| 1 | Thẻ hội viên | Hiển thị thương hiệu 4PM CLINIC, hạng thẻ, số thẻ, tên user, thời gian thành viên. | Có `$user` và `$membership`. | Nếu thiếu user hoặc membership, khối chính không hiển thị. |
| 2 | Điểm tích lũy | Hiển thị `points` đã format. | Dữ liệu từ `MembershipCard`. | Không có lỗi riêng. |
| 3 | Tổng chi tiêu | Hiển thị `total_spent`. | Dữ liệu từ DB/model. | Không có lỗi riêng. |
| 4 | Tiến trình thăng hạng | Hiển thị các mốc Đồng, Bạc, Vàng, K.Cương và progress bar. | Dùng accessor `progress_percent`. | Không có lỗi riêng. |
| 5 | Thông báo hạng tiếp theo | Hiển thị số tiền còn thiếu hoặc thông báo đạt hạng cao nhất. | Dùng accessor `next_tier`, `remaining_to_next_tier`. | Không có lỗi riêng. |
| 6 | Thống kê phụ | Hiển thị lần khám, chờ duyệt, voucher còn, tiết kiệm. | Dữ liệu từ `$extraData`. | Đây là dữ liệu giả lập trong controller. |

### Màn hình 4. Hệ Thống Nhắc Nhở Tuân Thủ Điều Trị

| STT | Phần tử | Mô tả | Yêu cầu | Thông tin lỗi |
|---:|---|---|---|---|
| 1 | Tiêu đề màn hình | Hiển thị tên hệ thống và mô tả lịch uống thuốc, hướng dẫn, báo cáo. | Có layout user. | Không có lỗi riêng. |
| 2 | 4 thẻ thống kê | Tuân thủ tháng này, nhắc nhở hôm nay, đã hoàn thành, ngày còn lại trong tháng. | Dữ liệu từ `$monthStats`. | Nếu service không trả key sẽ lỗi view. |
| 3 | Lịch uống thuốc hôm nay | Danh sách reminder theo giờ, message thuốc/hướng dẫn. | Dữ liệu `$todayReminders`. | Nếu rỗng hiển thị "Không có lịch uống thuốc hôm nay." |
| 4 | Nhãn nguy hiểm | Reminder nguy hiểm hiển thị nền cảnh báo và nút nhãn "NGUY HIỂM". | Dựa vào `$reminder->isDangerous()`. | Không cho bấm xác nhận trong UI khi nguy hiểm. |
| 5 | Nút đánh dấu đã uống | Gọi POST `/treatment/confirm/{id}` bằng fetch. | CSRF token, reminder thuộc user. | Nếu request lỗi, nút bật lại và text quay về "Đánh dấu đã uống". |
| 6 | Báo cáo tuân thủ tháng | Hiển thị tổng quan, uống thuốc, vận động, tái khám. | Dữ liệu `$monthStats`. | Không có lỗi riêng. |
| 7 | Hướng dẫn điều trị tại nhà | Danh sách instruction kèm checkbox hoàn thành hôm nay. | Dữ liệu `$instructions`. | Nếu rỗng hiển thị "Chưa có hướng dẫn điều trị." |
| 8 | Checkbox hướng dẫn | Gọi POST `/treatment/instruction/toggle`. | `instruction_id` tồn tại. | Nếu API lỗi rollback checkbox. |
| 9 | Biểu đồ 7 ngày | Cột theo ngày CN-T7, trạng thái đã hoàn thành/chưa xong/không có lịch. | Dữ liệu `$weeklyStats`. | Không có lỗi riêng. |
| 10 | Ghi chú tái khám | Hiển thị ngày lịch hẹn tiếp theo nếu có. | Có `$nextAppointment`. | Không có lịch thì không hiển thị. |

### Màn hình 5. Lịch Sử Khám Của Tôi

| STT | Phần tử | Mô tả | Yêu cầu | Thông tin lỗi |
|---:|---|---|---|---|
| 1 | Header | Tiêu đề "Lịch Sử Khám Của Tôi" và nút Trang chủ. | User bệnh nhân đăng nhập. | Không đúng role bị redirect ở controller. |
| 2 | Flash message | Hiển thị success/error từ session. | Có session tương ứng. | Không có thì không hiển thị. |
| 3 | Bộ lọc tìm kiếm | Input search placeholder mã phiếu, tên bác sĩ. | Query `search`. | Không có lỗi riêng. |
| 4 | Bộ lọc loại khám | Select tất cả hoặc các loại khám từ service. | Query `visit_type`. | Không có lỗi riêng. |
| 5 | Bộ lọc trạng thái | Select trạng thái từ service. | Query `status`. | Không có lỗi riêng. |
| 6 | Bộ lọc ngày | Date từ ngày/đến ngày. | Query `date_from`, `date_to`. | Không validate riêng trong controller. |
| 7 | Nút Tìm kiếm / Xóa bộ lọc | Submit GET hoặc về route index. | Form GET. | Không có lỗi riêng. |
| 8 | Sắp xếp | Select sort by ngày khám/mã phiếu và thứ tự mới nhất/cũ nhất. | Query `sort_by`, `sort_order`. | Service chỉ chấp nhận sort trong whitelist. |
| 9 | Bảng lịch sử | Cột mã phiếu, bác sĩ, ngày khám, loại khám, chẩn đoán chính, trạng thái, thao tác. | Dữ liệu `$records`. | Nếu rỗng hiển thị trạng thái rỗng. |
| 10 | Phân trang và per_page | Hiển thị khoảng bản ghi, link phân trang, chọn 10/20/50/100 mỗi trang. | `$records` là paginator. | Không có lỗi riêng. |

### Màn hình 6. Hồ sơ bệnh án / phiếu khám

| STT | Phần tử | Mô tả | Yêu cầu | Thông tin lỗi |
|---:|---|---|---|---|
| 1 | Danh sách phiếu khám | Header "Danh Sách Phiếu Khám", nút trang chủ, filter, bảng record. | Auth; dữ liệu theo role. | Flash success/error nếu có. |
| 2 | Filter danh sách | Search mã phiếu/tên bệnh nhân/bác sĩ, loại khám, trạng thái, từ ngày, đến ngày. | Query GET. | Không có validation riêng. |
| 3 | Bảng danh sách | Cột mã phiếu, bệnh nhân, bác sĩ, ngày khám, loại khám, chẩn đoán chính, trạng thái, thao tác xem/sửa. | `$records` paginator. | Nếu rỗng có nút xóa bộ lọc. |
| 4 | Form tạo hồ sơ | Thông tin chung: bệnh nhân, mã bệnh nhân, ngày khám, giờ khám, bác sĩ, loại khám, lý do/triệu chứng. | Request bắt buộc bệnh nhân, bác sĩ, ngày khám, loại khám, lý do. | View hiển thị alert danh sách lỗi validation. |
| 5 | Chỉ số sinh tồn | Huyết áp, nhịp tim, nhiệt độ, SpO2, cân nặng, đường huyết. | Các chỉ số chính bắt buộc theo request; có min/max. | Lỗi từ request hoặc JS alert kiểm tra form. |
| 6 | Dị ứng | Dòng động chọn/chèn chất gây dị ứng, mức độ, phản ứng, nút thêm/xóa. | Không bắt buộc; dòng rỗng bị lọc. | Không có lỗi riêng nếu bỏ trống. |
| 7 | Chẩn đoán | Dòng động chẩn đoán, mã ICD, loại chẩn đoán, ghi chú. | Bắt buộc ít nhất 1 chẩn đoán có tên; loại thuộc primary/secondary/complication. | Request báo lỗi nếu thiếu. |
| 8 | Đơn thuốc | Dòng động thuốc, liều dùng, số lượng, số ngày, hướng dẫn. | Không bắt buộc; dòng rỗng bị lọc. | Không có lỗi riêng nếu bỏ trống. |
| 9 | Chỉ định xét nghiệm/hình ảnh | Dòng động loại chỉ định, tên chỉ định, mô tả. | Không bắt buộc; dòng rỗng bị lọc. | Không có lỗi riêng nếu bỏ trống. |
| 10 | Chi tiết hồ sơ | Tab/link danh sách phiếu khám, tiền sử sức khỏe, tài liệu y khoa; topbar quay lại, in, sửa, xóa. | Có quyền xem record. | Không có quyền bị redirect ở controller. |
| 11 | Khối chi tiết | Thông tin chung, sinh tồn, chẩn đoán, đơn thuốc, chỉ định, tập đính kèm. | Record load các quan hệ. | Nếu thiếu dữ liệu, từng khối hiển thị theo điều kiện trong view. |
| 12 | Kết quả xét nghiệm | Nút nhập/sửa/xóa kết quả trong trang chi tiết. | Chỉ doctor/admin theo controller. | JSON trả lỗi 401/403/422/500; UI hiển thị lỗi inline/alert tùy đoạn JS. |
| 13 | Tệp đính kèm | Upload file, xem file, xóa file. | File pdf/jpg/jpeg/png/doc/docx tối đa 10MB; doctor/admin, bác sĩ chỉ file hồ sơ của mình. | JS alert nếu upload/xóa lỗi. |
| 14 | Màn hình in | Trang in hiển thị tiêu đề "Hồ sơ bệnh án chi tiết", thông tin chung, chẩn đoán, đơn thuốc, chỉ định. | Có quyền xem record. | Không tìm thấy/không quyền bị redirect trước khi vào view. |

### Màn hình 7. Bài tập phục hồi

| STT | Phần tử | Mô tả | Yêu cầu | Thông tin lỗi |
|---:|---|---|---|---|
| 1 | Danh sách bài tập | Tiêu đề "Bai tap phuc hoi" và mô tả chọn bài tập phù hợp tình trạng/giai đoạn điều trị. | Auth. | Không có lỗi riêng. |
| 2 | Bộ lọc category | Các nút category từ controller, active theo query hiện tại. | Query `category`. | Category không có bản ghi thì danh sách rỗng. |
| 3 | Card bài tập | Thumbnail, category label, phase label, title, excerpt nội dung, thời lượng hoặc "Linh hoat". | Bài có `status = published`. | Thumbnail fallback qua accessor nếu thiếu. |
| 4 | Nút Xem | Link sang chi tiết bài tập. | Route model binding. | Nếu bài không published thì chi tiết abort 404. |
| 5 | Empty state | Alert "Chua co bai tap cong khai." | Khi paginator rỗng. | Không có lỗi riêng. |
| 6 | Chi tiết bài tập | Nút quay lại, ảnh, category/phase/view_count, title, thời lượng, nội dung. | Bài published. | Không published abort 404 ở controller. |
| 7 | Bài tập liên quan | Hiển thị tối đa 3 bài cùng category. | `$related->isNotEmpty()`. | Không có liên quan thì khối không hiển thị. |

### Màn hình 8. Dashboard Dinh dưỡng

| STT | Phần tử | Mô tả | Yêu cầu | Thông tin lỗi |
|---:|---|---|---|---|
| 1 | Dashboard bệnh nhân | Tiêu đề dashboard dinh dưỡng. | Auth. | Không có lỗi riêng. |
| 2 | Gợi ý thực đơn cho bạn | Hiển thị chẩn đoán gần nhất và hai nhóm thực phẩm nên dùng/nên tránh. | Có diagnoses và rules phù hợp thì có dữ liệu. | Nếu không có rule, danh sách tương ứng rỗng theo view. |
| 3 | Lời khuyên chuyên gia | Danh sách tối đa 4 bài viết published phù hợp bệnh hoặc bài mới nhất. | Dữ liệu `$expertArticles`. | Nếu không có bài published thì danh sách rỗng. |
| 4 | Lượng Calo hôm nay | Hiển thị tổng calo, mục tiêu 2000 kcal và phần trăm. | Dữ liệu `$totalCaloriesToday`, `$calorieGoal`, `$caloriePercent`. | Không có lỗi riêng. |
| 5 | Calo theo buổi | Hiển thị Sáng, Trưa, Tối, Phụ. | Group theo `meal_type`. | Không có log thì giá trị về 0/theo default view. |
| 6 | Form thêm bữa ăn | Select món ăn, select buổi ăn, input gram, nút ghi nhận. | Food active; meal type hợp lệ; gram 1-5000. | Validation lỗi hiển thị theo Laravel nếu view có `@error`; controller trả lỗi messages. |
| 7 | Nhật ký hôm nay | Danh sách meal log hôm nay, tên món, nhãn bữa, gram/calo. | Log thuộc user hôm nay. | Nếu rỗng view hiển thị trạng thái theo code. |
| 8 | Nút xóa meal log | Form DELETE từng log. | Log phải thuộc Auth::id(). | Sai quyền `abort(403)`. |
| 9 | Admin bài viết dinh dưỡng | Bảng bài viết: #, tiêu đề, bệnh mục tiêu, tác giả, trạng thái, ngày tạo, thao tác; form tạo/sửa title, tác giả, bệnh mục tiêu, nội dung, trạng thái. | Role 1 hoặc 2. | Validate title/content/status. |
| 10 | Admin quy tắc dinh dưỡng | Bảng tên bệnh, mã ICD, thực phẩm, loại gợi ý, lý do, thao tác; form nhập rule. | Role 1 hoặc 2. | Validate tên bệnh, food, recommendation type; lỗi trùng rule. |
| 11 | Admin danh mục thực phẩm | Bảng tên thực phẩm, calo/100g, mô tả, trạng thái, thao tác; form tạo/sửa food. | Role 1 hoặc 2. | Validate tên unique, calo integer 0-5000, status. |

### Màn hình 9. Nhật ký sức khỏe chủ động

| STT | Phần tử | Mô tả | Yêu cầu | Thông tin lỗi |
|---:|---|---|---|---|
| 1 | Topbar Health Tracker | Link về danh sách, nút hồ sơ, nút thêm chỉ số nếu user là bệnh nhân. | Auth. | Không có lỗi riêng. |
| 2 | Header danh sách | Eyebrow theo dõi sức khỏe, tiêu đề "Nhật ký sức khỏe chủ động", mô tả các chỉ số. | Dữ liệu summary/list. | Không có lỗi riêng. |
| 3 | Thẻ tổng quan | Tổng bản ghi, bình thường, cảnh báo, nguy hiểm. | Summary từ controller. | Không có lỗi riêng. |
| 4 | Bộ lọc | Select mức độ, date từ ngày/đến ngày, nút lọc, nút xóa filter. | Query `risk_level`, `date_from`, `date_to`. | Không validate riêng trong controller. |
| 5 | Bảng ghi nhận | Cột ngày, bệnh nhân nếu không phải patient, huyết áp, nhịp tim, SpO2, cân nặng, đường huyết, mức độ, thao tác. | `$trackings` paginator. | Nếu rỗng hiển thị empty state và nút tạo nhật ký đầu tiên cho patient. |
| 6 | Thao tác xem/sửa/xóa | Nút icon xem; sửa/xóa chỉ hiện khi policy cho phép. | Policy `update`, `delete`. | Xóa mở modal xác nhận. |
| 7 | Form thêm/sửa | Các input huyết áp tâm thu/tâm trương, nhịp tim, SpO2, cân nặng, đường huyết, triệu chứng. | Các chỉ số bắt buộc và đúng min/max; PUT có hidden version. | Client validate hiển thị invalid/warn; server validate trả errors. |
| 8 | Cảnh báo realtime | Gọi API `health-tracking.check-risk`, hiển thị cảnh báo theo từng field và alert tổng. | Có CSRF và giá trị numeric. | Nếu API lỗi, JS bỏ qua catch rỗng. |
| 9 | Chi tiết nhật ký | Alert mức độ risk, danh sách warning, 6 metric card, triệu chứng, ngày tạo/cập nhật/version. | Có quyền xem bản ghi. | Không quyền bị policy chặn. |
| 10 | Xung đột khi sửa | Alert conflict và nút tải lại nếu version không khớp. | PUT kèm version hiện tại. | Controller rollback và trả `conflict_message`. |

### Màn hình 10. Lưu Trữ & Tra Cứu Tài Liệu Y Khoa Cá Nhân

| STT | Phần tử | Mô tả | Yêu cầu | Thông tin lỗi |
|---:|---|---|---|---|
| 1 | Header tài liệu | Tiêu đề "Lưu Trữ & Tra Cứu Tài Liệu Y Khoa Cá Nhân". | Auth. | Không có lỗi riêng. |
| 2 | Flash message | Alert success/error, có nút đóng. | Session success/error. | Không có thì không hiển thị. |
| 3 | Upload tài liệu | Nút chọn tệp, input file ẩn, nút tải lên. | File jpg/jpeg/png/pdf tối đa 20MB. | Controller validation trả lỗi nếu sai định dạng/kích thước. |
| 4 | Phân loại tài liệu | Các nút category từ `MedicalDocument::categories()`: xét nghiệm, chẩn đoán hình ảnh, đơn thuốc, giấy chuyển viện, khác. | Category bắt buộc khi upload. | Upload thiếu category bị validation lỗi. |
| 5 | Tài liệu của bạn | Danh sách card tài liệu, meta category/ngày upload, badge category. | `$documents` paginator. | Nếu rỗng view hiển thị theo điều kiện trong code. |
| 6 | Hành động tài liệu | Xem inline, tải xuống, chỉnh sửa, xóa. | Tài liệu phải thuộc user hoặc user là bác sĩ/admin. | File không tồn tại khi xem/tải thì 404; sai quyền 403. |
| 7 | Phân trang tài liệu | Nút Trước/Tiếp. | Paginator 6/trang. | Không có trang trước/sau thì nút disabled. |
| 8 | Bộ lọc sidebar | Search tên file, category, period, nút áp dụng. | Query `search`, `category`, `period`. | Không có lỗi riêng. |
| 9 | Thống kê sidebar | Tổng tệp, dung lượng, số lượng theo category. | Controller tính từ DB và disk public. | Bác sĩ/admin xem bệnh nhân thì `total_size` đang là dấu gạch ngang. |
| 10 | Màn hình chỉnh sửa | Link quay lại, xem file, form ngày tài liệu, phân loại, ghi chú/mô tả, nút hủy/lưu. | PUT `documents.update`; category hợp lệ; ngày nullable/date. | Controller hiện chỉ update `uploaded_at` và `doc_type`; các field hospital/note có trong view nhưng không nằm trong update/fillable hiện tại. |

## Phần 3. Bảng trường dữ liệu của 10 chức năng

Các bảng dưới đây lấy theo migration và model hiện có. Một số bảng trong migration dùng chữ hoa/thường khác model; tài liệu ghi theo tên bảng đang được controller/model sử dụng khi có khác biệt.

### Chức năng 1. HealthBackgroundController

#### Bảng `health_backgrounds`

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| id | BIGINT UNSIGNED | Khóa chính, tự động tăng do `$table->id()`. |
| user_id | INT UNSIGNED | Mã người dùng, khóa ngoại tới `users.user_id`, xóa user thì xóa theo. |
| blood_group | VARCHAR(5) | Nhóm máu. |
| yeuto_rh | VARCHAR(20) | Yếu tố Rh. |
| height | DOUBLE | Chiều cao. |
| weight | DOUBLE | Cân nặng. |
| bmi | DOUBLE | Chỉ số BMI được controller tính từ chiều cao/cân nặng. |
| food_allergies | TEXT | Dị ứng thực phẩm. |
| drug_allergies | TEXT | Dị ứng thuốc. |
| chronic_diseases | JSON | Danh sách bệnh mãn tính, model cast sang array. |
| other_chronic_diseases | TEXT | Bệnh mãn tính khác. |
| created_at | TIMESTAMP | Thời điểm tạo bản ghi do `$table->timestamps()`. |
| updated_at | TIMESTAMP | Thời điểm cập nhật bản ghi do `$table->timestamps()`. |

### Chức năng 2. ProfileController

#### Bảng `users`

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| user_id | INT UNSIGNED | Khóa chính, tự động tăng. |
| full_name | VARCHAR(100) | Họ tên người dùng, bắt buộc trong cập nhật hồ sơ. |
| email | VARCHAR(100) | Email đăng nhập, unique. |
| password | VARCHAR(255) | Mật khẩu đã hash. |
| phone | VARCHAR(15) | Số điện thoại, nullable. |
| address | VARCHAR(255) | Địa chỉ, nullable. |
| date_of_birth | DATE | Ngày sinh, nullable. |
| gender | VARCHAR(10) | Giới tính, nullable. |
| role_id | INT UNSIGNED | Mã vai trò, khóa ngoại tới `roles.role_id`. |
| avatar_url | VARCHAR(500) | Đường dẫn avatar trong disk public, nullable. |
| status | BOOLEAN | Trạng thái tài khoản, mặc định true. |
| created_at | DATETIME | Thời điểm tạo tài khoản, mặc định thời gian hiện tại. |

### Chức năng 3. MembershipController

#### Bảng `membershipcards`

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| card_id | INT UNSIGNED | Khóa chính, tự động tăng. |
| user_id | INT UNSIGNED | Mã người dùng, unique, khóa ngoại tới `Users.user_id`. |
| card_number | VARCHAR(50) | Số thẻ thành viên, unique. |
| tier | VARCHAR(30) | Hạng thẻ; model tự tính theo `total_spent`. |
| points | INT | Điểm tích lũy, model/controller tính từ tổng chi tiêu. |
| total_spent | DECIMAL(12,2) | Tổng chi tiêu, mặc định 0. |
| discount_pct | DECIMAL(5,2) | Phần trăm giảm giá, mặc định 0. |
| issue_date | DATE | Ngày phát hành, mặc định ngày hiện tại. |
| expiry_date | DATE | Ngày hết hạn, nullable. |
| status | BOOLEAN | Trạng thái thẻ, mặc định true. |

### Chức năng 4. TreatmentReminderController

#### Bảng `treatmentreminders`

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| reminder_id | INT UNSIGNED | Khóa chính, tự động tăng. |
| user_id | INT UNSIGNED | Mã bệnh nhân, khóa ngoại tới `Users.user_id`. |
| record_id | INT UNSIGNED | Mã hồ sơ bệnh án, nullable, khóa ngoại tới `MedicalRecords.record_id` trong migration. |
| reminder_type | VARCHAR(50) | Loại nhắc nhở, ví dụ `medicine` hoặc `instruction`. |
| remind_at | DATETIME | Thời điểm nhắc nhở. |
| message | VARCHAR(500) | Nội dung nhắc nhở. |
| is_sent | BOOLEAN | Trạng thái đã gửi nhắc nhở, mặc định false. |
| created_at | DATETIME | Thời điểm tạo, mặc định hiện tại. |

#### Bảng `treatment_home_instructions`

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| id | BIGINT UNSIGNED | Khóa chính, tự động tăng. |
| record_id | BIGINT UNSIGNED | Mã hồ sơ liên quan, có index. |
| user_id | INT UNSIGNED | Mã bệnh nhân, có index. |
| instruction_text | VARCHAR(300) | Nội dung hướng dẫn điều trị tại nhà. |
| detail | VARCHAR(200) | Chi tiết hướng dẫn, nullable. |
| icon | VARCHAR(50) | Tên icon hiển thị, mặc định `activity`. |
| sort_order | TINYINT UNSIGNED | Thứ tự hiển thị, mặc định 0. |
| is_active | BOOLEAN | Trạng thái còn hiệu lực, mặc định true. |
| created_at | TIMESTAMP | Thời điểm tạo. |
| updated_at | TIMESTAMP | Thời điểm cập nhật. |

#### Bảng `treatment_confirmations`

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| id | BIGINT UNSIGNED | Khóa chính, tự động tăng. |
| reminder_id | INT UNSIGNED | Mã nhắc nhở đã xác nhận, có index. |
| user_id | INT UNSIGNED | Mã bệnh nhân. |
| confirmed_at | DATETIME | Thời điểm xác nhận. |
| confirm_type | ENUM | Loại xác nhận: `medicine` hoặc `instruction`, mặc định `medicine`. |
| note | VARCHAR(255) | Ghi chú, nullable. |
| created_at | TIMESTAMP | Thời điểm tạo. |
| updated_at | TIMESTAMP | Thời điểm cập nhật. |

#### Bảng `instruction_daily_checks`

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| id | BIGINT UNSIGNED | Khóa chính, tự động tăng. |
| instruction_id | BIGINT UNSIGNED | Mã hướng dẫn điều trị tại nhà. |
| user_id | INT UNSIGNED | Mã bệnh nhân. |
| checked_date | DATE | Ngày đánh dấu thực hiện. |
| is_done | BOOLEAN | Trạng thái đã hoàn thành, mặc định false. |
| checked_at | DATETIME | Thời điểm tick hoàn thành, nullable. |
| created_at | TIMESTAMP | Thời điểm tạo. |
| updated_at | TIMESTAMP | Thời điểm cập nhật. |

### Chức năng 5. `medical_history`

Chức năng này không có bảng riêng; controller đọc danh sách từ `medical_records` và lấy chẩn đoán chính qua quan hệ `diagnoses`.

#### Bảng `medical_records` dùng cho danh sách lịch sử khám

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| record_id | INT UNSIGNED | Khóa chính hồ sơ/phiếu khám, tự động tăng. |
| record_code | VARCHAR(20) | Mã phiếu khám, unique, nullable. |
| patient_id | INT UNSIGNED | Mã bệnh nhân, khóa ngoại tới `users.user_id`. |
| patient_name | VARCHAR(100) | Tên bệnh nhân. |
| patient_code | VARCHAR(20) | Mã bệnh nhân, nullable. |
| doctor_id | INT UNSIGNED | Mã bác sĩ, nullable, khóa ngoại tới `users.user_id`. |
| doctor_name | VARCHAR(100) | Tên bác sĩ. |
| appointment_id | INT UNSIGNED | Mã lịch hẹn liên kết, nullable. |
| exam_date | DATE | Ngày khám. |
| exam_time | TIME | Giờ khám. |
| visit_type | VARCHAR(50) | Loại khám. |
| chief_complaint | TEXT | Lý do đến khám / triệu chứng chính. |
| status | VARCHAR(50) | Trạng thái phiếu khám, mặc định `pending`. |
| status_note | TEXT | Ghi chú trạng thái, nullable. |
| created_at | TIMESTAMP | Thời điểm tạo. |
| updated_at | TIMESTAMP | Thời điểm cập nhật. |

#### Bảng `diagnoses` dùng để hiển thị chẩn đoán chính

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| diagnosis_id | BIGINT UNSIGNED | Khóa chính, tự động tăng. |
| record_id | BIGINT UNSIGNED | Mã hồ sơ bệnh án, có index. |
| diagnosis_name | VARCHAR(300) | Tên chẩn đoán. |
| icd_code | VARCHAR(20) | Mã ICD, nullable. |
| diagnosis_type | ENUM | Loại chẩn đoán: `primary`, `secondary`, `complication`; mặc định `primary`. |
| note | TEXT | Ghi chú chẩn đoán, nullable. |
| created_at | TIMESTAMP | Thời điểm tạo, nullable, mặc định hiện tại. |
| updated_at | TIMESTAMP | Thời điểm cập nhật, nullable, tự cập nhật. |

### Chức năng 6. MedicalRecordController

#### Bảng `vital_signs`

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| vital_id | BIGINT UNSIGNED | Khóa chính, tự động tăng. |
| record_id | BIGINT UNSIGNED | Mã hồ sơ bệnh án, unique. |
| blood_pressure | VARCHAR(20) | Huyết áp. |
| bp_status | ENUM | Trạng thái huyết áp: `normal`, `high`, `low`; mặc định `normal`. |
| heart_rate | DECIMAL(5,1) | Nhịp tim. |
| hr_status | ENUM | Trạng thái nhịp tim: `normal`, `high`, `low`; mặc định `normal`. |
| temperature | DECIMAL(4,1) | Nhiệt độ. |
| temp_status | ENUM | Trạng thái nhiệt độ: `normal`, `high`, `low`; mặc định `normal`. |
| spo2 | DECIMAL(5,1) | Chỉ số SpO2. |
| spo2_status | ENUM | Trạng thái SpO2: `normal`, `high`, `low`; mặc định `normal`. |
| weight | DECIMAL(5,1) | Cân nặng. |
| blood_sugar | DECIMAL(5,2) | Đường huyết. |
| sugar_status | ENUM | Trạng thái đường huyết: `normal`, `high`, `low`; mặc định `normal`. |
| created_at | TIMESTAMP | Thời điểm tạo, nullable, mặc định hiện tại. |
| updated_at | TIMESTAMP | Thời điểm cập nhật, nullable, tự cập nhật. |

#### Bảng `record_allergies`

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| id | BIGINT UNSIGNED | Khóa chính, tự động tăng. |
| record_id | BIGINT UNSIGNED | Mã hồ sơ bệnh án, có index. |
| allergen | VARCHAR(200) | Tên chất gây dị ứng. |
| severity | VARCHAR(50) | Mức độ dị ứng, nullable. |
| reaction | TEXT | Phản ứng dị ứng, nullable. |
| created_at | TIMESTAMP | Thời điểm tạo, nullable, mặc định hiện tại. |
| updated_at | TIMESTAMP | Thời điểm cập nhật, nullable, tự cập nhật. |

#### Bảng `prescriptions`

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| prescription_id | BIGINT UNSIGNED | Khóa chính, tự động tăng. |
| record_id | BIGINT UNSIGNED | Mã hồ sơ bệnh án, có index. |
| drug_name | VARCHAR(200) | Tên thuốc. |
| dosage | VARCHAR(100) | Liều dùng, nullable. |
| instructions | VARCHAR(500) | Hướng dẫn dùng thuốc, nullable. |
| duration_days | INT UNSIGNED | Số ngày dùng thuốc, mặc định 30. |
| quantity | INT UNSIGNED | Số lượng, nullable. |
| unit | VARCHAR(30) | Đơn vị thuốc, nullable. |
| created_at | TIMESTAMP | Thời điểm tạo, nullable, mặc định hiện tại. |
| updated_at | TIMESTAMP | Thời điểm cập nhật, nullable, tự cập nhật. |

#### Bảng `medical_orders`

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| order_id | BIGINT UNSIGNED | Khóa chính, tự động tăng. |
| record_id | BIGINT UNSIGNED | Mã hồ sơ bệnh án, có index. |
| order_type | ENUM | Loại chỉ định: `lab`, `imaging`, `other`; mặc định `lab`. |
| order_name | VARCHAR(300) | Tên chỉ định xét nghiệm / hình ảnh. |
| description | TEXT | Mô tả chỉ định, nullable. |
| result_status | VARCHAR(50) | Trạng thái kết quả, mặc định `Chờ kết quả`. |
| result_note | TEXT | Ghi chú / nội dung kết quả, nullable. |
| created_at | TIMESTAMP | Thời điểm tạo, nullable, mặc định hiện tại. |
| updated_at | TIMESTAMP | Thời điểm cập nhật, nullable, tự cập nhật. |

#### Bảng `medical_attachments`

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| attachment_id | BIGINT UNSIGNED | Khóa chính, tự động tăng. |
| record_id | BIGINT UNSIGNED | Mã hồ sơ bệnh án, có index. |
| file_name | VARCHAR(255) | Tên file gốc. |
| file_path | VARCHAR(500) | Đường dẫn file trong storage. |
| file_type | VARCHAR(50) | Loại file / phần mở rộng, nullable. |
| file_size | BIGINT UNSIGNED | Kích thước file, nullable. |
| attachment_category | ENUM | Nhóm file: `result`, `image`, `document`, `other`; mặc định `document`. |
| created_at | TIMESTAMP | Thời điểm tạo, nullable, mặc định hiện tại. |
| updated_at | TIMESTAMP | Thời điểm cập nhật, nullable, tự cập nhật. |

### Chức năng 7. `rehab_exercises.php`

#### Bảng `rehab_exercises`

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| id | BIGINT UNSIGNED | Khóa chính, tự động tăng. |
| title | VARCHAR(255) | Tiêu đề bài tập phục hồi. |
| content | LONGTEXT | Nội dung hướng dẫn bài tập. |
| category | VARCHAR(80) | Nhóm bài tập. |
| phase | VARCHAR(40) | Giai đoạn điều trị. |
| thumbnail | VARCHAR(255) | Đường dẫn ảnh thumbnail, nullable. |
| status | VARCHAR(20) | Trạng thái bài tập, mặc định `draft`, có index. |
| view_count | INT UNSIGNED | Số lượt xem, mặc định 0. |
| duration_minutes | SMALLINT UNSIGNED | Thời lượng phút, nullable. |
| created_by | INT UNSIGNED | Người tạo, nullable, khóa ngoại tới `users.user_id`. |
| created_at | TIMESTAMP | Thời điểm tạo. |
| updated_at | TIMESTAMP | Thời điểm cập nhật. |

### Chức năng 8. Dinh dưỡng

#### Bảng `foods`

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| food_id | INT UNSIGNED | Khóa chính, tự động tăng. |
| food_name | VARCHAR(150) | Tên thực phẩm, unique. |
| calories_per_100g | SMALLINT UNSIGNED | Lượng calo trên 100g. |
| description | TEXT | Mô tả thực phẩm, nullable. |
| status | TINYINT | Trạng thái hiển thị, mặc định 1; comment `1=active, 0=hidden`. |
| created_at | TIMESTAMP | Thời điểm tạo. |
| updated_at | TIMESTAMP | Thời điểm cập nhật. |

#### Bảng `disease_nutrition_rules`

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| rule_id | INT UNSIGNED | Khóa chính, tự động tăng. |
| disease_name | VARCHAR(200) | Tên bệnh dùng để khớp với `diagnoses.diagnosis_name`, nullable. |
| icd_code | VARCHAR(20) | Mã ICD dùng để khớp với `diagnoses.icd_code`, nullable. |
| food_id | INT UNSIGNED | Mã thực phẩm, khóa ngoại tới `foods.food_id`. |
| recommendation_type | ENUM | Loại gợi ý: `should_eat` hoặc `should_avoid`. |
| reason | TEXT | Lý do khuyến nghị, nullable. |
| created_at | TIMESTAMP | Thời điểm tạo. |
| updated_at | TIMESTAMP | Thời điểm cập nhật. |

#### Bảng `meal_logs`

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| log_id | INT UNSIGNED | Khóa chính, tự động tăng. |
| user_id | INT UNSIGNED | Mã người dùng; migration đang khai báo khóa ngoại tới `users.id`, trong model quan hệ dùng `users.user_id`. |
| food_id | INT UNSIGNED | Mã thực phẩm, khóa ngoại tới `foods.food_id`. |
| meal_type | ENUM | Buổi ăn: `breakfast`, `lunch`, `dinner`, `snack`. |
| weight_gram | SMALLINT UNSIGNED | Khối lượng thực phẩm đã ăn theo gram. |
| total_calories_intake | SMALLINT UNSIGNED | Tổng calo nạp vào, tính từ calo/100g và gram. |
| logged_date | DATE | Ngày ghi nhận bữa ăn. |
| created_at | TIMESTAMP | Thời điểm tạo. |
| updated_at | TIMESTAMP | Thời điểm cập nhật. |

#### Bảng `nutrition_articles`

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| article_id | INT UNSIGNED | Khóa chính, tự động tăng. |
| doctor_id | INT UNSIGNED | Mã bác sĩ tác giả, nullable, khóa ngoại tới `doctors.doctor_id`. |
| title | VARCHAR(255) | Tiêu đề bài viết. |
| slug | VARCHAR(191) | Slug bài viết, unique. |
| content | LONGTEXT | Nội dung bài viết. |
| target_disease | VARCHAR(200) | Tên bệnh mục tiêu để lọc bài cho bệnh nhân, nullable. |
| status | TINYINT | Trạng thái bài viết, mặc định 0; comment `0=Nháp, 1=Xuất bản`. |
| created_at | TIMESTAMP | Thời điểm tạo. |
| updated_at | TIMESTAMP | Thời điểm cập nhật. |

### Chức năng 9. HealthTrackingController

#### Bảng `health_trackings`

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| id | BIGINT UNSIGNED | Khóa chính, tự động tăng. |
| patient_id | INT UNSIGNED | Mã bệnh nhân, khóa ngoại tới `users.user_id`. |
| systolic | INT | Huyết áp tâm thu. |
| diastolic | INT | Huyết áp tâm trương. |
| heart_rate | INT | Nhịp tim. |
| spo2 | INT | Chỉ số SpO2. |
| weight | DECIMAL(5,2) | Cân nặng. |
| blood_sugar | INT | Đường huyết. |
| symptoms | TEXT | Triệu chứng, nullable. |
| risk_level | ENUM | Mức rủi ro: `normal`, `warning`, `danger`; mặc định `normal`. |
| risk_warnings | JSON | Danh sách cảnh báo rủi ro, nullable. |
| version | BIGINT UNSIGNED | Phiên bản bản ghi dùng cho optimistic locking, mặc định 1. |
| created_at | TIMESTAMP | Thời điểm tạo. |
| updated_at | TIMESTAMP | Thời điểm cập nhật. |
| deleted_at | TIMESTAMP | Thời điểm xóa mềm do `softDeletes()`, nullable. |

### Chức năng 10. MedicalDocument

#### Bảng `medicaldocuments`

| Tên Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|
| doc_id | INT UNSIGNED | Khóa chính, tự động tăng. |
| user_id | INT UNSIGNED | Mã người dùng sở hữu tài liệu, khóa ngoại tới `Users.user_id`. |
| record_id | INT UNSIGNED | Mã hồ sơ bệnh án liên kết, nullable, khóa ngoại tới `MedicalRecords.record_id` trong migration. |
| doc_type | VARCHAR(50) | Loại tài liệu: xét nghiệm, hình ảnh, đơn thuốc, chuyển viện hoặc khác. |
| doc_name | VARCHAR(200) | Tên tài liệu / tên file gốc. |
| file_path | VARCHAR(500) | Đường dẫn file trong storage public. |
| uploaded_at | DATETIME | Thời điểm tải lên, mặc định hiện tại. |
