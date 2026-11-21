# HƯỚNG DẪN SỬ DỤNG HỆ THỐNG HÀNG ĐỢI KHÁM BỆNH (MEDICAL QUEUE SYSTEM)

Hệ thống hàng đợi khám bệnh (Queue System) phục vụ tiếp tiếp đón, xếp hàng và điều phối bệnh nhân vào các phòng khám theo các đối tượng ưu tiên thời gian thực. Dưới đây là các đường dẫn truy cập và kịch bản vận hành chi tiết.

---

## 📍 DANH SÁCH ĐƯỜNG DẪN TRUY CẬP (URLs)

Để truy cập vào các chức năng, bạn hãy mở trình duyệt và truy cập các địa chỉ tương ứng sau (sau khi đã đăng nhập tài khoản có quyền phù hợp):

### 1. Dành cho Ban Quản Trị (Admin)
* **Trang chủ quản lý hàng đợi Admin (Tổng quan tất cả các ca trực):**
  👉 `http://127.0.0.1:8000/admin/queue`
* **Chi tiết hàng đợi của một ca khám cụ thể:**
  👉 `http://127.0.0.1:8000/admin/queue/{schedule_id}` *(Ví dụ: http://127.0.0.1:8000/admin/queue/1)*
* **Trang Báo cáo - Thống kê hiệu suất hàng đợi:**
  👉 `http://127.0.0.1:8000/admin/queue/report`

### 2. Dành cho Bộ phận Tiếp đón / Lễ tân (Receptionist)
*(Yêu cầu đăng nhập tài khoản Lễ tân - `role_id = 4` hoặc Admin - `role_id = 1`)*
* **Danh sách ca khám đang hoạt động hôm nay:**
  👉 `http://127.0.0.1:8000/queue/manage`
* **Trang điều phối hàng đợi (Xem hàng đợi, gọi nhỡ, bỏ qua):**
  👉 `http://127.0.0.1:8000/queue/manage/schedule/{schedule_id}` *(Ví dụ: http://127.0.0.1:8000/queue/manage/schedule/1)*
* **Trang Check-in / Tiếp đón bệnh nhân:**
  👉 `http://127.0.0.1:8000/queue/manage/checkin`

### 3. Dành cho Bác sĩ (Doctor)
*(Yêu cầu đăng nhập tài khoản Bác sĩ - `role_id = 2` hoặc Admin - `role_id = 1`)*
* **Bàn làm việc của Bác sĩ (Gọi số, Bắt đầu khám, Hoàn thành ca khám):**
  👉 `http://127.0.0.1:8000/queue/doctor`

### 4. Màn hình TV hiển thị tại sảnh chờ (Public Display)
*(Giao diện công cộng dành cho người bệnh - **KHÔNG cần đăng nhập**)*
* **Màn hình hiển thị TV phòng khám:**
  👉 `http://127.0.0.1:8000/queue/display/{schedule_id}` *(Ví dụ: http://127.0.0.1:8000/queue/display/1)*

---

## 🔄 KỊCH BẢN VẬN HÀNH QUY TRÌNH KHÁM BỆNH

### Bước 1: Tiếp đón & Check-in (Lễ tân)
1. Lễ tân đăng nhập tài khoản lễ tân và truy cập: `http://127.0.0.1:8000/queue/manage/checkin`.
2. **Đối với bệnh nhân đã đặt lịch trước trực tuyến:** Nhập *Số điện thoại / Email / Mã lịch hẹn* vào ô tìm kiếm ➜ Chọn đúng lịch hẹn phù hợp ➜ Chọn đối tượng ưu tiên ➜ Nhấn **"Xác nhận Check-in"**.
3. **Đối với bệnh nhân vãng lai (khám tự do):** Điền trực tiếp các thông tin: *Tên bệnh nhân, Số điện thoại, Email, Ca khám mong muốn, Chọn đối tượng ưu tiên* ➜ Nhấn **"Xác nhận Check-in"**.
4. Hệ thống sẽ tự động cấp **Số thứ tự (STT)** và tính toán **Thời gian chờ dự kiến (Phút)**.

> [!TIP]
> **Thuật toán tự động sắp xếp theo độ ưu tiên:**
> * `Cấp cứu (emergency)` ➜ Được xếp ngay lên vị trí đầu tiên của hàng đợi chờ khám.
> * `Khuyết tật (disabled)` ➜ Ưu tiên tiếp theo (xếp sau Cấp cứu nhưng trước Cao tuổi & Thường).
> * `Cao tuổi (elderly)` ➜ Ưu tiên kế tiếp (xếp trước Thường).
> * `Thường (normal)` ➜ Xếp theo thứ tự check-in trước sau.

---

### Bước 2: Hiển thị sảnh chờ (Màn hình TV)
1. Tại khu vực chờ trước cửa phòng khám, bật màn hình TV lớn truy cập đường dẫn: `http://127.0.0.1:8000/queue/display/{schedule_id}` (Chọn đúng ca khám của phòng đó).
2. Màn hình sẽ hiển thị rõ ràng:
   * **Số đang khám** (Giữa màn hình, nền xanh dịu).
   * **Danh sách bệnh nhân tiếp theo đang chờ** (Bên phải).
   * Khi Bác sĩ gọi số mới, màn hình sẽ **nhấp nháy viền đỏ rực rỡ** và phát **âm thanh chuông chuông gọi số (chime)** để nhắc nhở bệnh nhân vào phòng.

---

### Bước 3: Gọi số & Khám bệnh (Bác sĩ)
1. Bác sĩ đăng nhập tài khoản bác sĩ và truy cập: `http://127.0.0.1:8000/queue/doctor`.
2. Bác sĩ sẽ thấy ca trực hôm nay của mình. Nhấn vào ca trực để vào bàn làm việc.
3. Khi sẵn sàng khám: Nhấn **"Gọi số tiếp theo"**. 
   * Trạng thái số đó trên TV và hệ thống sẽ chuyển sang `Đang gọi (calling)`.
4. Khi bệnh nhân đã bước vào phòng khám: Nhấn **"Bắt đầu khám bệnh"**.
   * Trạng thái chuyển sang `Đang khám (in_progress)`.
5. Sau khi khám xong: Nhấn **"Hoàn thành khám bệnh"**.
   * Trạng thái chuyển sang `Đã xong (completed)`. 
   * Hệ thống sẽ tự động đồng bộ cập nhật trạng thái lịch hẹn online tương ứng thành `Đã khám`.

---

### Bước 4: Xử lý các trường hợp đặc biệt (Lễ tân / Bác sĩ)
* **Bệnh nhân gọi quá 3 lần không có mặt:** 
  Tại màn hình lễ tân hoặc bác sĩ, nhấn **"Bỏ qua số (Skip)"** ➜ Nhập lý do (ví dụ: *Không có mặt*) ➜ Số thứ tự đó sẽ chuyển sang trạng thái `Gọi nhỡ (skipped)` để bác sĩ gọi số tiếp theo.
* **Hủy lượt khám:**
  Lễ tân có thể hủy lượt của bệnh nhân nếu họ yêu cầu hủy khám trực tiếp.

---

## ⚙️ CÁC LỆNH KỸ THUẬT CẦN THIẾT
Để khởi chạy toàn bộ hệ thống cục bộ:

```bash
# 1. Chạy Laravel Server
php artisan serve

# 2. Biên dịch & Khởi động Vite Assets (CSS Tailwind & JS)
npm run dev

# 3. Chạy kịch bản test hàng đợi tự động
vendor/bin/phpunit tests/Feature/QueueSystemSmokeTest.php
```
