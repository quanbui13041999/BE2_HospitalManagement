<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; background: #f5f5f5; }
        .container { max-width: 600px; margin: 20px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #ddd; }
        th { background: #3498db; color: white; }
        tr:hover { background: #f9f9f9; }
        .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px; }
        .button { display: inline-block; background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin: 10px 0; }
        .note { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Xác Nhận Lịch Hẹn Khám</h2>
        
        <p>Kính gửi <strong>{{ $user->full_name }}</strong>,</p>
        
        <p>Lịch hẹn khám bệnh của bạn đã được <strong>đăng ký thành công</strong>. Dưới đây là thông tin chi tiết:</p>
        
        <h3>📋 Thông Tin Lịch Hẹn</h3>
        <table>
            <tr>
                <th>Thông Tin</th>
                <th>Chi Tiết</th>
            </tr>
            <tr>
                <td><strong>Bác Sĩ</strong></td>
                <td>BS. {{ $appointment->doctor_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Chuyên Khoa</strong></td>
                <td>{{ $appointment->department_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Ngày Khám</strong></td>
                <td>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Giờ Khám</strong></td>
                <td>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</td>
            </tr>
            <tr>
                <td><strong>Số Thứ Tự</strong></td>
                <td>#{{ $appointment->queue_number }}</td>
            </tr>
            <tr>
                <td><strong>Trạng Thái</strong></td>
                <td><span style="color: #f59e0b; font-weight: bold;">{{ $appointment->status }}</span></td>
            </tr>
        </table>
        
        <h3>📝 Ghi Chú</h3>
        <p>{{ $appointment->note ?? 'Không có ghi chú' }}</p>
        
        <h3>⏰ Lưu Ý Quan Trọng</h3>
        <div class="note">
            <ul style="margin: 0; padding-left: 20px;">
                <li>Thời gian khám: Vui lòng đến khám đúng giờ đã đặt (tối thiểu 10 phút trước)</li>
                <li>Hủy lịch: Nếu cần hủy, vui lòng hủy trước giờ khám ít nhất 2 tiếng</li>
                <li>Dời lịch: Bạn có thể dời lịch khám sang thời gian khác nếu cần thiết</li>
                <li>Liên hệ: Nếu có thắc mắc, vui lòng liên hệ phòng khám</li>
            </ul>
        </div>
        
        <center>
            <a href="{{ route('appointments.index') }}" class="button">Xem Lịch Hẹn Của Tôi</a>
        </center>
        
        <h3>⚕️ Chuẩn Bị Cho Buổi Khám</h3>
        <ul>
            <li>Mang theo CMND/CCCD và bảo hiểm y tế (nếu có)</li>
            <li>Chuẩn bị các triệu chứng/vấn đề sức khỏe muốn trao đổi</li>
            <li>Nếu khám lần thứ nhất, hãy đến sớm để làm thủ tục</li>
        </ul>
        
        <div class="footer">
            <p>Cảm ơn bạn đã tin tưởng HospitalC. Chúng tôi mong sớm được phục vụ bạn!</p>
            <p><strong>Bệnh Viện HospitalC</strong></p>
        </div>
    </div>
</body>
</html>
