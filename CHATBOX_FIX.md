# 🔧 FIX CHATBOX - Hướng dẫn sửa chức năng Chat

## ❌ Vấn đề chính
**API Key của Gemini AI không hợp lệ (Invalid API Key)**

Khi người dùng gửi tin nhắn, hệ thống cố gắng gọi API Gemini nhưng bị từ chối vì API key sai.

## ✅ Giải pháp

### Bước 1: Lấy API Key hợp lệ từ Google Cloud

1. Truy cập: https://aistudio.google.com/apikey
2. Đăng nhập bằng tài khoản Google của bạn
3. Nhấp "Create API Key"
4. Chọn project hoặc tạo project mới
5. Copy API Key vừa tạo

### Bước 2: Cập nhật API Key trong file `.env`

Mở file `.env` trong thư mục gốc:

```bash
# Tìm dòng này (hoặc thêm nếu chưa có):
GEMINI_API_KEY=YOUR_API_KEY_HERE
```

Thay `YOUR_API_KEY_HERE` bằng API key bạn vừa lấy. Ví dụ:

```bash
GEMINI_API_KEY=AIzaSyXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXx
```

### Bước 3: Cập nhật config/services.php (nếu chưa có)

Mở file `config/services.php` và thêm:

```php
'gemini' => [
    'api_key' => env('GEMINI_API_KEY', ''),
],
```

### Bước 4: Xóa cache và restart server

```bash
php artisan config:cache
php artisan cache:clear
```

Restart Laravel:
```bash
php artisan serve
```

## 🔄 Luồng Chat hoạt động như thế nào?

```
1. Bệnh nhân gửi tin nhắn
   ↓
2. Hệ thống lưu tin nhắn vào database
   ↓
3. Nếu không có staff đang xử lý:
   - Gọi Gemini AI để tạo phản hồi tự động
   - AI trả lời liên quan đến: đặt lịch, bác sĩ, khoa, dịch vụ, vaccine, etc.
   ↓
4. Bệnh nhân nhận phản hồi (từ AI hoặc staff)
   ↓
5. Khi staff online → Tự động gán phòng cho staff
   → Staff trả lời trực tiếp, AI tắt
```

## 📋 Những cải tiến đã thực hiện

✅ **Xử lý lỗi tốt hơn**: Hiển thị thông báo rõ ràng khi API key sai
✅ **Fallback khi AI fail**: Chat vẫn hoạt động, staff có thể trả lời trực tiếp
✅ **Lưu tin nhắn AI với sender_id = 0**: Phân biệt rõ ràng giữa AI và staff
✅ **Khớp lịch sử tin nhắn**: Lấy tin nhắn theo thứ tự đúng để gọi API

## 🐛 Các lỗi có thể gặp

### "API key not valid"
→ Kiểm tra API key trong `.env` đã chính xác chưa

### "Hệ thống AI tạm thời gián đoạn"
→ Có thể do:
- API key hết hạn
- Quota vượt quá
- Mạng bị cắt kết nối
→ Kiểm tra logs: `storage/logs/laravel.log`

### Chat không gửi được tin nhắn
→ Kiểm tra:
- Đã đăng nhập chưa?
- Mở chatbox chưa?
- Có lỗi gì trong Console (F12)?

## 📞 Support Gemini API

- Documentation: https://ai.google.dev/
- Pricing: https://ai.google.dev/pricing
- Status: https://status.cloud.google.com/

---

**Lưu ý**: API key của Google là công khai trong `.env` local, nhưng trong production cần sử dụng environment variables an toàn hơn.
