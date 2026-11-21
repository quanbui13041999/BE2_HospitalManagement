@component('mail::message')
# Nhắc nhở điều trị

Chào **{{ $patient?->full_name ?? 'bạn' }}**,

Đây là nhắc nhở lịch điều trị của bạn:

**Chi tiết:**
- **Nội dung:** {{ $reminder->message }}
- **Thời gian:** {{ $reminder->remind_at->format('H:i d/m/Y') }}

Vui lòng tuân thủ đúng lịch trình để đảm bảo hiệu quả điều trị tốt nhất.

@component('mail::button', ['url' => config('app.url') . '/treatment'])
Xem lịch trình của tôi
@endcomponent

Trân trọng,<br>
{{ config('app.name') }}
@endcomponent
