Hệ thống hàng đợi khám bệnh (Queue System) của bệnh viện được thiết kế để tự động hóa quy trình đón tiếp, phân loại ưu tiên, gọi số tại phòng khám và hiển thị công khai trên màn hình sảnh chờ.

Dưới đây là sơ đồ và giải thích chi tiết toàn bộ cơ chế hoạt động của hệ thống:

1. Cơ sở dữ liệu và Các trạng thái của Phiếu khám (QueueTicket)
Mỗi ca khám của bác sĩ trong ngày (DoctorSchedule) có một danh sách hàng đợi độc lập. Mỗi bệnh nhân khi check-in sẽ nhận được một phiếu khám (QueueTicket) với các thông tin cốt lõi:

Số thứ tự khám (queue_number): Được cấp tăng dần từ 1 cho mỗi ca khám hàng ngày.
Mức độ ưu tiên (priority): Được chia làm 4 cấp độ với trọng số sắp xếp tương ứng (priority_sort):
emergency (Cấp cứu - Trọng số 1)
disabled (Khuyết tật - Trọng số 2)
elderly (Cao tuổi $\ge 60$ - Trọng số 3)
normal (Thường - Trọng số 4)
Trạng thái phiếu khám (status):
waiting: Đang chờ khám.
calling: Đang được gọi tên vào phòng khám.
in_progress: Đang trong quá trình khám bệnh.
completed: Đã khám xong.
skipped: Bị bỏ qua (khi gọi quá lượt mà không có mặt).
cancelled: Đã hủy.
2. Sơ đồ quy trình hoạt động (Workflow)
mermaid
graph TD
    A[Bệnh nhân đến khám] --> B1{Đặt lịch trực tuyến?}
    
    %% Quy trình check-in
    B1 -- Có --> C1[Check-in tại quầy/Tự động check-in hôm nay]
    C1 --> D1[Tạo QueueTicket liên kết với AppointmentID]
    D1 --> E[Cập nhật trạng thái Lịch hẹn: 'Đã xác nhận']
    
    B1 -- Không --> C2[Lễ tân nhập thông tin tại quầy - Khám trực tiếp]
    C2 --> D2[Tạo QueueTicket với AppointmentID = null]
    
    %% Xếp hàng đợi
    D2 --> F[Xếp vào danh sách chờ khám 'waiting']
    E --> F
    
    F --> G[Tính thời gian chờ ước tính = Số người chờ trước x 15 phút]
    
    %% Quy trình của Bác sĩ
    G --> H[Bác sĩ ấn 'Gọi số tiếp theo' callNext]
    H --> I[Thuật toán sắp xếp ưu tiên: Sắp xếp theo Trọng số Ưu tiên tăng dần, sau đó theo Số thứ tự tăng dần]
    I --> J[Chuyển trạng thái phiếu sang 'calling' & Phát tín hiệu Realtime]
    
    %% Màn hình hiển thị TV
    J --> K[Màn hình TV cửa phòng nhấp nháy đỏ, hiển thị Tên + Số thứ tự & Phát tiếng bíp thông báo]
    
    %% Quá trình khám
    K --> L[Bệnh nhân vào phòng khám]
    L --> M[Bác sĩ ấn 'Bắt đầu khám' -> Trạng thái 'in_progress']
    M --> N[Bác sĩ khám xong, ấn 'Hoàn thành khám' -> Trạng thái 'completed']
    
    %% Kết thúc quy trình
    N --> O1[Nếu có AppointmentID -> Cập nhật trạng thái lịch hẹn: 'Đã khám']
    N --> O2[Tự động tính toán lại thời gian chờ cho các bệnh nhân còn lại]
3. Chi tiết hoạt động của các cấu phần
A. Tiếp đón & Check-in (Receptionist / Auto Check-in)
Bệnh nhân đặt trước trực tuyến: Khi đặt lịch khám thành công cho ngày hôm nay, hệ thống sẽ tự động check-in (tạo QueueTicket) ngay lập tức. Nếu đặt lịch cho tương lai, hệ thống chỉ giữ ở trạng thái Chờ xác nhận, khi đến ngày khám lễ tân mới check-in để đưa vào hàng đợi.
Bệnh nhân trực tiếp (Walk-in): Lễ tân tạo phiếu trực tiếp. Hệ thống tính toán Thời gian chờ ước tính (est_wait_minutes) bằng cách đếm số người có độ ưu tiên cao hơn hoặc cùng độ ưu tiên nhưng check-in trước đang ở trạng thái waiting, sau đó nhân với thời gian khám trung bình (15 phút/người).
B. Thuật toán gọi số của Bác sĩ (Doctor Calling Logic)
Khi bác sĩ nhấn nút "Gọi số tiếp theo", hệ thống thực hiện nghiệp vụ trong một Transaction:

Tự động chuyển bệnh nhân đang ở trạng thái calling trước đó thành skipped (Bỏ qua) và ghi nhận thời gian kết thúc.
Tìm kiếm bệnh nhân tiếp theo trong hàng đợi dựa trên cơ chế ưu tiên kết hợp thời gian đến:
php
public function scopeOrdered($q) {
    return $q->orderBy('priority_sort')->orderBy('queue_number');
}
Ví dụ thực tế: Dù một người thường nhận số thứ tự #5 check-in trước, nhưng nếu có bệnh nhân cao tuổi nhận số #8 hoặc ca cấp cứu nhận số #12 check-in sau, hệ thống vẫn sẽ đưa ca cấp cứu lên đầu hàng, tiếp đến là người cao tuổi, rồi mới đến người thường.
Cập nhật trạng thái của bệnh nhân được chọn sang calling.
Cập nhật bảng queue_counters ghi nhận số thứ tự hiện tại của phòng khám.
C. Đồng bộ hóa thời gian thực (Realtime Synchronization)
Hệ thống sử dụng cơ chế truyền tin thời gian thực song song để đảm bảo dữ liệu hiển thị tức thời:

Pusher (WebSockets): Mỗi khi có sự thay đổi (check-in mới, gọi số, hoàn thành khám), hệ thống sẽ broadcast các sự kiện QueueUpdated và TicketCalled tới kênh (channel) riêng của phòng khám đó (ví dụ: queue.385).
Màn hình TV sảnh chờ: Lắng nghe sự kiện qua Javascript. Khi nhận được tín hiệu TicketCalled:
Màn hình lập tức cập nhật thông tin bệnh nhân mới lên bảng lớn.
Trình duyệt tự động kích hoạt Web Audio API phát ra âm thanh thông báo (chuỗi tiếng bíp đôi) để báo hiệu bệnh nhân di chuyển vào phòng khám.
Cơ chế dự phòng (Polling Fallback): Nhằm tránh việc mất kết nối WebSocket làm sai lệch thông tin hiển thị trên TV, màn hình tự động gửi request API mỗi 5 giây để lấy snapshot hàng đợi mới nhất từ server.