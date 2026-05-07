<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lịch Khám Của Tôi – HospitalBooking</title>
    <!-- Tailwind CSS + Google Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        /* Chỉ giữ lại các animation & tùy chỉnh nhỏ mà Tailwind không hỗ trợ sẵn */
        @keyframes modalIn {
            from { transform: scale(0.96); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .modal-animation {
            animation: modalIn 0.2s ease;
        }
        .schedule-option.selected {
            @apply border-blue-600 bg-blue-50 ring-2 ring-blue-200;
        }
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Tùy chỉnh thanh cuộn nhẹ */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50/30 antialiased">

{{-- TOPBAR HIỆN ĐẠI (Tailwind) --}}
<nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-200 shadow-sm px-4 md:px-8 py-3 flex items-center justify-between flex-wrap gap-3">
    <a href="{{ route('home') }}" class="flex items-center gap-3 group">
        <div class="w-9 h-9 bg-gradient-to-br from-blue-700 to-indigo-600 rounded-xl flex items-center justify-center shadow-md shadow-blue-200">
            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
            </svg>
        </div>
        <div>
            <div class="font-extrabold text-gray-800 tracking-tight text-lg leading-5">HospitalBooking</div>
            <div class="text-[11px] font-medium text-gray-400">Đặt lịch thông minh</div>
        </div>
    </a>

    <div class="hidden md:flex gap-1 bg-gray-100/80 p-1 rounded-full">
        <a href="{{ route('home') }}" class="px-4 py-1.5 text-sm font-semibold text-gray-600 rounded-full hover:bg-white hover:text-blue-700 transition">🏠 Trang chủ</a>
        <a href="{{ route('appointments.index') }}" class="px-4 py-1.5 text-sm font-semibold text-blue-700 bg-white shadow-sm rounded-full">📋 Lịch hẹn</a>
        <a href="{{ route('appointments.create') }}" class="px-4 py-1.5 text-sm font-semibold text-gray-600 rounded-full hover:bg-white hover:text-blue-700 transition">✨ Đặt lịch mới</a>
    </div>

    <div class="flex items-center gap-3">
        @auth
        <div class="flex items-center gap-2 bg-gray-100/90 rounded-full pr-3 pl-1 py-1">
            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-600 to-blue-500 flex items-center justify-center text-white font-bold text-sm shadow-sm">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
            <span class="text-sm font-semibold text-gray-700 max-w-[120px] truncate">{{ auth()->user()->name ?? 'Người dùng' }}</span>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-xs font-semibold bg-transparent border border-gray-300 rounded-full px-4 py-1.5 text-gray-600 hover:bg-gray-100 transition">Đăng xuất</button>
        </form>
        @endauth
    </div>
</nav>

{{-- BREADCRUMB --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 pt-6 pb-2 text-sm text-gray-500">
    <a href="{{ route('home') }}" class="text-blue-700 font-medium hover:underline">Trang chủ</a>
    <span class="mx-2 text-gray-300">›</span>
    <span class="font-semibold text-gray-700">Lịch hẹn của tôi</span>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    {{-- Header + nút đặt lịch --}}
    <div class="flex flex-wrap justify-between items-center gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-blue-600 to-indigo-500 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-200">
                <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Lịch Khám Của Tôi</h1>
                <p class="text-sm text-gray-500">Quản lý và theo dõi các lịch hẹn khám bệnh</p>
            </div>
        </div>
        <a href="{{ route('appointments.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-700 to-indigo-600 hover:from-blue-800 hover:to-indigo-700 text-white font-bold py-2.5 px-5 rounded-full shadow-md shadow-blue-200 transition text-sm">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19" />
                <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            Đặt lịch mới
        </a>
    </div>

    {{-- Alert thành công --}}
    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-xl text-green-700 text-sm flex items-center gap-3">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
            <polyline points="22 4 12 14.01 9 11.01" />
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @php
        $total     = $appointments->count();
        $pending   = $appointments->where('status','Chờ xác nhận')->count();
        $confirmed = $appointments->where('status','Đã xác nhận')->count();
        $done      = $appointments->where('status','Hoàn thành')->count();
    @endphp

    {{-- Thống kê nhanh --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
            <div class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Tổng lịch hẹn</div>
            <div class="text-3xl font-extrabold text-blue-700 mt-2">{{ $total }}</div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
            <div class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Chờ xác nhận</div>
            <div class="text-3xl font-extrabold text-amber-500 mt-2">{{ $pending }}</div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
            <div class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Đã xác nhận</div>
            <div class="text-3xl font-extrabold text-emerald-600 mt-2">{{ $confirmed }}</div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
            <div class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Hoàn thành</div>
            <div class="text-3xl font-extrabold text-gray-500 mt-2">{{ $done }}</div>
        </div>
    </div>

    {{-- Bảng danh sách lịch hẹn --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center flex-wrap gap-2">
            <div>
                <h2 class="font-bold text-gray-800">Danh sách lịch hẹn</h2>
                <p class="text-xs text-gray-400">Hiển thị {{ $total }} lịch hẹn của bạn</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-4 text-left text-[11px] font-bold uppercase tracking-wide text-gray-500">Người đặt</th>
                        <th class="px-5 py-4 text-left text-[11px] font-bold uppercase tracking-wide text-gray-500">Bác sĩ</th>
                        <th class="px-5 py-4 text-left text-[11px] font-bold uppercase tracking-wide text-gray-500">Dịch vụ</th>
                        <th class="px-5 py-4 text-left text-[11px] font-bold uppercase tracking-wide text-gray-500">Ngày khám</th>
                        <th class="px-5 py-4 text-left text-[11px] font-bold uppercase tracking-wide text-gray-500">Trạng thái</th>
                        <th class="px-5 py-4 text-left text-[11px] font-bold uppercase tracking-wide text-gray-500">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $item)
                    <tr class="border-b border-gray-100 hover:bg-blue-50/30 transition">
                        <td class="px-5 py-4">
                            <div class="font-semibold text-gray-800">{{ $item->user_full_name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">{{ $item->user_phone ?? 'Không có' }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                                    {{ strtoupper(substr($item->doctor_name, -2)) }}
                                </div>
                                <div class="font-bold text-gray-800">BS. {{ $item->doctor_name }}</div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-gray-700">{{ $item->service_name ?? '—' }}</td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-gray-800">{{ $item->work_date }}</div>
                            <div class="flex items-center gap-1 text-xs text-gray-500 mt-1">
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                                {{ $item->start_time }}
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $statusMap = [
                                    'Chờ xác nhận' => 'bg-amber-100 text-amber-800 border-amber-200',
                                    'Đã xác nhận'  => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'Đã hủy'       => 'bg-red-100 text-red-800 border-red-200',
                                    'Hoàn thành'   => 'bg-gray-100 text-gray-600 border-gray-200',
                                ];
                                $badgeClass = $statusMap[$item->status] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $badgeClass }}">
                                <span class="w-1.5 h-1.5 rounded-full 
                                    {{ $item->status == 'Chờ xác nhận' ? 'bg-amber-500' : ($item->status == 'Đã xác nhận' ? 'bg-emerald-500' : ($item->status == 'Đã hủy' ? 'bg-red-500' : 'bg-gray-400')) }}">
                                </span>
                                {{ $item->status }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            @if($item->status != 'Đã hủy' && $item->status != 'Hoàn thành')
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('appointments.edit', $item->appointment_id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-full text-xs font-semibold hover:bg-blue-600 hover:text-white transition border border-blue-100">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <rect x="3" y="4" width="18" height="18" rx="2" />
                                        <line x1="16" y1="2" x2="16" y2="6" />
                                        <line x1="8" y1="2" x2="8" y2="6" />
                                        <line x1="3" y1="10" x2="21" y2="10" />
                                    </svg>
                                    Dời lịch
                                </a>
                                <button type="button" onclick="openModal(this)" data-action="{{ route('appointments.cancel', $item->appointment_id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-600 rounded-full text-xs font-semibold hover:bg-red-600 hover:text-white transition border border-red-100">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <circle cx="12" cy="12" r="10" />
                                        <line x1="15" y1="9" x2="9" y2="15" />
                                        <line x1="9" y1="9" x2="15" y2="15" />
                                    </svg>
                                    Hủy
                                </button>
                            </div>
                            @else
                            <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-gray-400">
                                <svg class="w-16 h-16 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                                <p class="text-gray-500">Bạn chưa có lịch khám nào.</p>
                                <a href="{{ route('appointments.create') }}" class="mt-2 inline-flex items-center gap-2 bg-gradient-to-r from-blue-700 to-indigo-600 text-white px-5 py-2 rounded-full text-sm font-bold shadow-md">Đặt lịch ngay</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<footer class="text-center py-8 text-xs text-gray-400 border-t border-gray-100 mt-10 bg-white/50">
    © {{ date('Y') }} HospitalBooking · Nền tảng đặt lịch khám hiện đại · <a href="#" class="text-blue-600 hover:underline">Chính sách bảo mật</a> &nbsp;·&nbsp; <a href="#" class="text-blue-600 hover:underline">Hỗ trợ</a>
</footer>

{{-- Modal hủy lịch --}}
<div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 hidden items-center justify-center" id="cancelModal">
    <div class="bg-white rounded-2xl max-w-md w-full mx-4 p-6 shadow-2xl modal-animation">
        <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
        </div>
        <h3 class="text-xl font-extrabold text-center text-gray-800 mb-2">Xác nhận hủy lịch</h3>
        <p class="text-center text-gray-500 text-sm mb-5">Bạn có chắc muốn hủy lịch khám này không? Hành động này không thể hoàn tác.</p>
        <form id="cancelForm" method="POST">
            @csrf
            <textarea name="cancel_reason" placeholder="Nhập lý do hủy (tùy chọn)" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition resize-none min-h-[80px] mb-5"></textarea>
            <div class="flex gap-3">
                <button type="button" onclick="closeModal()" class="flex-1 py-2.5 rounded-full bg-gray-100 text-gray-700 font-bold text-sm hover:bg-gray-200 transition">Không, giữ lại</button>
                <button type="submit" class="flex-1 py-2.5 rounded-full bg-gradient-to-r from-red-600 to-red-700 text-white font-bold text-sm hover:shadow-md transition">Xác nhận hủy</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(button) {
        const action = button.getAttribute('data-action');
        document.getElementById('cancelForm').action = action;
        document.getElementById('cancelModal').classList.remove('hidden');
        document.getElementById('cancelModal').classList.add('flex');
    }
    function closeModal() {
        document.getElementById('cancelModal').classList.add('hidden');
        document.getElementById('cancelModal').classList.remove('flex');
    }
    document.getElementById('cancelModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
</script>
</body>
</html>