@component('mail::message')
# Xác Nhận Đăng Ký Nghỉ

Bác sĩ **{{ $doctor->full_name }}**,

Yêu cầu nghỉ của bạn đã được ghi nhận:

@component('mail::panel')
**Loại nghỉ:** {{ $type }}
**Ngày:** {{ $date }}@if($end_date !== $date) đến {{ $end_date }}@endif
**Buổi:** {{ $session === 'all' ? 'Cả ngày' : ($session === 'morning' ? 'Sáng' : 'Chiều') }}
**Lý do:** {{ $reason }}
@endcomponent

## Tác Động

- **{{ $blocked_schedules }} ca khám** đã bị khóa
- **{{ $affected_appointments }} bệnh nhân** bị ảnh hưởng
- Email gợi ý lịch mới đã được gửi cho bệnh nhân

@component('mail::button', ['url' => config('app.url')])
Quản Lý Lịch
@endcomponent

Nếu bạn cần hỗ trợ, vui lòng liên hệ ban quản lý.

Trân trọng,
{{ config('app.name') }}
@endcomponent
