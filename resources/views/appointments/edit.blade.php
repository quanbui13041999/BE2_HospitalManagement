<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dời Lịch Khám – HospitalC</title>
    <!-- Tailwind CSS + Font -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <x-typography-base />
    <style>
        /* Giữ lại animation cần thiết cho spinner, không ảnh hưởng logic */
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner { display: none; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.4); border-top-color: white; border-radius: 50%; animation: spin 0.6s linear infinite; }
        /* Class selected cho schedule-option (JS dùng) */
        .schedule-option.selected {
            border-color: #2563eb;
            background-color: #eff6ff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            outline: 2px solid #bfdbfe;
            outline-offset: 2px;
        }
        body { font-family: 'Inter', sans-serif; }
        .schedule-option { transition: all 0.2s; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50/30 antialiased">

<x-site-nav />

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-6">
    {{-- Breadcrumb --}}
    <div class="text-sm text-gray-500 mb-6 flex flex-wrap gap-1">
        <a href="{{ route('home') }}" class="text-blue-700 font-medium hover:underline">Trang chủ</a> <span>/</span>
        <a href="{{ route('appointments.index') }}" class="text-blue-700 font-medium hover:underline">Lịch hẹn của tôi</a> <span>/</span>
        <span class="text-gray-700 font-semibold">Dời lịch</span>
    </div>

    {{-- Header --}}
    <div class="flex flex-wrap gap-5 items-center mb-8">
        <div class="w-14 h-14 bg-gradient-to-br from-blue-600 to-indigo-500 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-200">
            <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="1 4 1 10 7 10" />
                <path d="M3.51 15a9 9 0 1 0 .49-4.95" />
            </svg>
        </div>
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Dời lịch khám</h1>
            <p class="text-sm text-gray-500">Thay đổi khung giờ với cùng bác sĩ – nhanh chóng, tiện lợi</p>
        </div>
    </div>

    {{-- Alert errors --}}
    @if($errors->has('msg'))
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl text-red-700 text-sm flex items-center gap-3">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ $errors->first('msg') }}
    </div>
    @endif
    @if(session('warning'))
    <div class="mb-6 p-4 bg-orange-50 border-l-4 border-orange-500 rounded-xl text-orange-700 text-sm flex items-center gap-3">
        {{ session('warning') }}
    </div>
    @endif

    @if($availableSchedules->isEmpty())
    <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-xl text-blue-800 text-sm flex items-center gap-3">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Không có lịch trống trong 14 ngày tới của BS. {{ $appointment->doctor_name }}. Vui lòng liên hệ phòng khám.
    </div>
    @endif

    <form action="{{ route('appointments.update', $appointment->appointment_id) }}" method="POST" id="reschedule-form" data-disable-submit>
        @csrf
        @method('PUT')
        <input type="hidden" name="new_appointment_time" id="new_appointment_time">
        <input type="hidden" name="version" value="{{ $appointment->version_token }}"> {{-- fixed: optimistic lock bang token vi bang appointments khong co updated_at --}}

        {{-- Thông tin người đặt lịch --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3 bg-gray-50/50">
                <div class="p-2 bg-blue-100 rounded-xl text-blue-700">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <h2 class="font-bold text-gray-800">Thông tin người đặt lịch</h2>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="space-y-1">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-blue-600">Họ tên</div>
                    <div class="font-semibold text-gray-800">{{ $appointment->user_full_name ?? '—' }}</div>
                </div>
                <div class="space-y-1">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-blue-600">Số điện thoại</div>
                    <div class="font-semibold text-gray-800">{{ $appointment->user_phone ?? '—' }}</div>
                </div>
                <div class="space-y-1">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-blue-600">Địa chỉ</div>
                    <div class="font-semibold text-gray-800">{{ $appointment->user_address ?? '—' }}</div>
                </div>
                <div class="space-y-1">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-blue-600">Email</div>
                    <div class="font-semibold text-gray-800">{{ $appointment->user_email ?? '—' }}</div>
                </div>
                <div class="space-y-1">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-blue-600">Ngày sinh</div>
                    <div class="font-semibold text-gray-800">{{ $appointment->date_of_birth ? \Carbon\Carbon::parse($appointment->date_of_birth)->format('d/m/Y') : '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Lịch hẹn hiện tại --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3 bg-amber-50/30">
                <div class="p-2 bg-amber-100 rounded-xl text-amber-700">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div>
                    <h2 class="font-bold text-gray-800">Lịch hẹn hiện tại</h2>
                    <p class="text-xs text-gray-500">Thông tin lịch khám bạn muốn thay đổi</p>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div><div class="text-[11px] font-bold uppercase text-amber-600">Bác sĩ</div><div class="font-semibold text-amber-800">BS. {{ $appointment->doctor_name }}</div></div>
                <div><div class="text-[11px] font-bold uppercase text-gray-500">Chuyên khoa</div><div class="font-medium text-gray-800">{{ $appointment->department_name }}</div></div>
                <div><div class="text-[11px] font-bold uppercase text-gray-500">Ngày khám</div><div class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($appointment->work_date)->format('d/m/Y') }}</div></div>
                <div><div class="text-[11px] font-bold uppercase text-gray-500">Giờ khám</div><div class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }}</div></div>
                @if($appointment->service_name)
                <div><div class="text-[11px] font-bold uppercase text-gray-500">Dịch vụ</div><div class="font-medium text-gray-800">{{ $appointment->service_name }}</div></div>
                @endif
                <div><div class="text-[11px] font-bold uppercase text-gray-500">Trạng thái</div>
                    @php $statusClass = $appointment->status === 'Đã xác nhận' ? 'bg-green-100 text-green-800 border-green-200' : 'bg-yellow-100 text-yellow-800 border-yellow-200'; @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $statusClass }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $appointment->status === 'Đã xác nhận' ? 'bg-green-500' : 'bg-yellow-500' }}"></span>
                        {{ $appointment->status }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Chọn lịch mới --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3 bg-indigo-50/30">
                <div class="p-2 bg-indigo-100 rounded-xl text-indigo-700">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div>
                    <h2 class="font-bold text-gray-800">Chọn lịch mới</h2>
                    <p class="text-xs text-gray-500">Khung giờ còn trống của BS. {{ $appointment->doctor_name }} trong 14 ngày tới</p>
                </div>
            </div>
            <div class="p-6">
                @if($availableSchedules->isEmpty())
                <div class="text-center py-12 bg-gray-50 rounded-2xl">
                    <svg class="w-12 h-12 mx-auto text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <p class="mt-3 text-gray-500 font-medium">Hiện tại chưa có lịch trống</p>
                    <p class="text-xs text-gray-400">Vui lòng thử lại sau hoặc liên hệ tổng đài</p>
                </div>
                @else
                <div class="space-y-3">
                    @foreach($availableSchedules as $sch)
                    @php
                        $remaining = $sch->max_slot - $sch->booked_count;
                        $isLow = $remaining <= 2;
                    @endphp
                    <label class="schedule-option flex items-start gap-3 p-4 border border-gray-200 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition {{ old('new_schedule_id') == $sch->schedule_id ? 'selected' : '' }}">
                        <input type="radio" name="new_schedule_id" value="{{ $sch->schedule_id }}" data-time="{{ \Carbon\Carbon::parse($sch->start_time)->format('H:i') }}" class="mt-1 w-4 h-4 text-blue-600 focus:ring-blue-500" required onchange="onScheduleSelect(this)">
                        <div class="flex-1">
                            <div class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($sch->work_date)->format('d/m/Y') }} <span class="text-sm font-normal text-gray-500 ml-1">{{ \Carbon\Carbon::parse($sch->work_date)->isoFormat('dddd') }}</span></div>
                            <div class="text-sm text-gray-600 mt-0.5">{{ \Carbon\Carbon::parse($sch->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($sch->end_time)->format('H:i') }}</div>
                        </div>
                        <div class="text-right">
                            <span class="inline-block text-xs font-bold px-3 py-1 rounded-full {{ $isLow ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }}">Còn {{ $remaining }} chỗ</span>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('new_schedule_id')
                <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                @enderror
                @endif

                <div class="mt-6">
                    <label class="text-[11px] font-bold uppercase tracking-wide text-gray-500 block mb-2">Lý do dời lịch <span class="normal-case font-normal">(tùy chọn)</span></label>
                    <textarea name="reschedule_reason" id="reschedule_reason" maxlength="255" class="w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition" placeholder="VD: bận công việc đột xuất, trùng lịch khám khác, sức khỏe chưa ổn…">{{ old('reschedule_reason') }}</textarea>
                </div>

                <div class="flex flex-col sm:flex-row justify-between gap-4 mt-8 pt-4 border-t border-gray-100">
                    <a href="{{ route('appointments.index') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-full transition text-sm">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                        Quay lại
                    </a>
                    <button type="submit" id="submit-btn" class="btn-primary inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-700 to-indigo-600 hover:from-blue-800 hover:to-indigo-700 text-white font-bold rounded-full shadow-md shadow-blue-200 transition disabled:opacity-60 disabled:cursor-not-allowed text-sm" {{ $availableSchedules->isEmpty() ? 'disabled' : '' }}>
                        <span class="spinner" id="spinner"></span>
                        <svg id="submit-icon" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.95"/></svg>
                        Xác nhận dời lịch
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<footer class="text-center py-8 text-xs text-gray-400 border-t border-gray-100 mt-10 bg-white/50">
    © {{ date('Y') }} HospitalC · Nền tảng đặt lịch khám hiện đại · <a href="#" class="text-blue-600 hover:underline">Chính sách bảo mật</a>
</footer>

<script>
    function onScheduleSelect(radio) {
        const time = radio.getAttribute('data-time');
        document.getElementById('new_appointment_time').value = time || '';
        document.querySelectorAll('.schedule-option').forEach(el => el.classList.remove('selected'));
        radio.closest('.schedule-option').classList.add('selected');
    }

    window.addEventListener('DOMContentLoaded', () => {
        const radios = document.querySelectorAll('input[name="new_schedule_id"]');
        if (radios.length === 1) {
            radios[0].checked = true;
            onScheduleSelect(radios[0]);
        }
        const checkedRadio = document.querySelector('input[name="new_schedule_id"]:checked');
        if (checkedRadio) onScheduleSelect(checkedRadio);
    });

    const rescheduleForm = document.getElementById('reschedule-form');
    if (rescheduleForm) {
        rescheduleForm.addEventListener('submit', function(e) {
        const btn = document.getElementById('submit-btn');
        const spinner = document.getElementById('spinner');
        const icon = document.getElementById('submit-icon');
        if (btn.disabled) return;
        btn.disabled = true;
        spinner.style.display = 'inline-block';
        icon.style.display = 'none';
        });
    }

    window.showAppNotification = window.showAppNotification || function(message, type = 'error', options = {}) {
        let stack = document.getElementById('app-notification-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.id = 'app-notification-stack';
            stack.style.cssText = 'position:fixed;top:18px;right:18px;z-index:20000;width:min(420px,calc(100vw - 32px));display:flex;flex-direction:column;gap:10px';
            document.body.appendChild(stack);
        }

        const notice = document.createElement('div');
        notice.textContent = message || 'Đã xảy ra lỗi, vui lòng thử lại sau.';
        notice.style.cssText = 'padding:12px 14px;border-radius:10px;border:1px solid #fecaca;background:#fef2f2;color:#991b1b;box-shadow:0 16px 40px rgba(15,23,42,.16);font-size:14px;line-height:1.45';
        if (type === 'warning') {
            notice.style.borderColor = '#fde68a';
            notice.style.background = '#fffbeb';
            notice.style.color = '#92400e';
        }
        stack.appendChild(notice);
        const timeout = typeof options.timeout === 'number' ? options.timeout : 5000;
        setTimeout(() => notice.remove(), timeout);
    };

    window.alert = function(message) {
        window.showAppNotification(message, 'error');
    };

    function bindStandaloneInputLimitWarnings() {
        document.querySelectorAll('input[maxlength], textarea[maxlength]').forEach(function(field) {
            field.addEventListener('input', function() {
                if (field.maxLength <= 0 || field.value.length < field.maxLength || field.dataset.limitNotified === '1') {
                    return;
                }

                field.dataset.limitNotified = '1';
                window.showAppNotification('Trường này tối đa ' + field.maxLength + ' ký tự. Vui lòng rút ngắn nội dung.', 'warning'); /* fixed: bao loi textbox qua dai tren man hinh */
            });

            field.addEventListener('blur', function() {
                field.dataset.limitNotified = '';
            });
        });
    }

    bindStandaloneInputLimitWarnings();

    document.querySelectorAll('form[data-disable-submit]').forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (form.dataset.submitLocked === '1') {
                event.preventDefault();
                window.showAppNotification('Yêu cầu đang được xử lý, vui lòng không bấm lưu nhiều lần.', 'warning');
                return;
            }

            form.dataset.submitLocked = '1';
            form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function(button) {
                button.disabled = true;
            });
        });
    }); /* fixed: chan double submit doi lich */
</script>
@if(session('reload_page'))
<div id="appointment-reload-message"
     data-message="{{ e(session('warning') ?? 'Lịch hẹn đã thay đổi, trang sẽ được tải lại.') }}"
     hidden></div>
<script>
    window.showAppNotification(document.getElementById('appointment-reload-message').dataset.message, 'warning', { timeout: 2500 });
    setTimeout(() => window.location.replace(window.location.href), 1800);
</script>
@endif
@include('components.back-to-previous')
</body>
</html>
