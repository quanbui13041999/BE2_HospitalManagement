<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quản lý lịch làm việc - MediBook</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f9fafb;
            color: #111827;
        }

        .nav-link.active {
            border-bottom: 2px solid #2563eb;
            color: #2563eb;
        }

        .nav-link:not(.active) {
            border-bottom: 2px solid transparent;
            color: #6b7280;
        }

        .nav-link:not(.active):hover {
            color: #111827;
            border-bottom-color: #d1d5db;
        }

        .tab-btn.active {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }

        .day-btn input:checked~span {
            background: #2563eb !important;
            color: white !important;
            border-color: #2563eb !important;
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 14px 20px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .12);
            animation: slideIn .3s ease-out;
            z-index: 1000;
            max-width: 360px;
        }

        @keyframes slideIn {
            from {
                transform: translateX(120%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .toast.success {
            border-left: 4px solid #10b981;
        }

        .toast.error {
            border-left: 4px solid #ef4444;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn .2s ease-out;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: 80px repeat(7, 1fr);
            gap: 1px;
            font-size: 11px;
        }

        .p-cell {
            padding: 4px 3px;
            text-align: center;
        }

        .slot-pill {
            background: #dbeafe;
            color: #1e40af;
            border-radius: 4px;
            padding: 2px 4px;
            font-size: 10px;
            display: block;
            margin: 1px 0;
            line-height: 1.4;
        }

        .dayoff-type-selected>div {
            border-width: 2px !important;
        }
    </style>
</head>

<body>

    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <a href="{{ route('home') }}" class="active">
                        <h1 style="font-size:1.25rem;font-weight:700;color:#111827;margin:0"> MediCore<sup>®</sup></h1>
                    </a>
                        <p class="text-xs text-gray-500">Quản lý bác sĩ</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                   {{ auth()->user()->doctor->full_name ?? auth()->user()->full_name ?? 'Bác sĩ' }}
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-red-600 transition">
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex gap-1 overflow-x-auto">
                <a href="{{ route('doctor.dashboard') }}"
                    class="nav-link flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                   Dashboard {{ auth()->user()->doctor->full_name ?? auth()->user()->full_name ?? 'Bác sĩ' }}
                </a>
                <a href="{{ route('doctor.schedule') }}"
                    class="nav-link active flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Lịch làm việc
                </a>
                @auth
                @if(auth()->user()->is_admin ?? false)
                <a href="{{ route('admin.dashboard') }}" class="nav-link flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                    </svg>
                    Thống kê
                </a>
                @endif
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold mb-1">Quản lý lịch làm việc</h1>
            <p class="text-gray-500 text-sm">Thiết lập lịch lặp lại và quản lý ngày nghỉ cho bác sĩ</p>
        </div>

        @php
            $user = auth()->user();
            if ($user?->isDoctor && $user->doctor) {
                $doctors = collect([$user->doctor->load('department')]);
            } else {
                $doctors = \App\Models\Doctor::where('status', 1)->orderBy('full_name')->get();
            }
            $hasDoctors = $doctors->isNotEmpty();
        @endphp

        <!-- Doctor selector -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6 flex items-center gap-4">
            <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Bác sĩ:</span>
            <select id="doctor-select" onchange="onDoctorChange()"
                class="flex-1 max-w-sm px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                {{ $hasDoctors ? '' : 'disabled' }}>
                @if($hasDoctors)
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->doctor_id }}">{{ $doctor->full_name }}</option>
                    @endforeach
                @else
                    <option value="">Không có bác sĩ</option>
                @endif
            </select>
        </div>

        <!-- Tabs -->
        <div class="flex gap-2 mb-6">
            <button id="tab-btn-recurring"
                class="tab-btn active px-5 py-2.5 rounded-xl border text-sm font-medium transition-all"
                onclick="switchTab('recurring')">
                🔁 Lịch lặp lại
            </button>
            <button id="tab-btn-dayoff"
                class="tab-btn px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-sm font-medium transition-all"
                onclick="switchTab('dayoff')">
                🏖️ Quản lý ngày nghỉ
            </button>
        </div>

        <!-- ====== TAB: RECURRING ====== -->
        <div id="tab-recurring" class="fade-in">
            <div class="grid lg:grid-cols-2 gap-6">

                <!-- Config -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="font-bold text-lg mb-5 flex items-center gap-2">
                        <span
                            class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-sm font-bold flex items-center justify-center">1</span>
                        Cấu hình lịch lặp lại
                    </h2>

                    <!-- Days of week -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Ngày làm việc trong tuần</label>
                        <div class="flex gap-2 flex-wrap">
                            <label class="day-btn cursor-pointer"><input type="checkbox" value="1" class="hidden"
                                    checked onchange="updatePreview()"><span
                                    class="w-10 h-10 rounded-full border-2 border-blue-600 bg-blue-600 text-white font-semibold text-sm flex items-center justify-center select-none">T2</span></label>
                            <label class="day-btn cursor-pointer"><input type="checkbox" value="2" class="hidden"
                                    checked onchange="updatePreview()"><span
                                    class="w-10 h-10 rounded-full border-2 border-blue-600 bg-blue-600 text-white font-semibold text-sm flex items-center justify-center select-none">T3</span></label>
                            <label class="day-btn cursor-pointer"><input type="checkbox" value="3" class="hidden"
                                    checked onchange="updatePreview()"><span
                                    class="w-10 h-10 rounded-full border-2 border-blue-600 bg-blue-600 text-white font-semibold text-sm flex items-center justify-center select-none">T4</span></label>
                            <label class="day-btn cursor-pointer"><input type="checkbox" value="4" class="hidden"
                                    checked onchange="updatePreview()"><span
                                    class="w-10 h-10 rounded-full border-2 border-blue-600 bg-blue-600 text-white font-semibold text-sm flex items-center justify-center select-none">T5</span></label>
                            <label class="day-btn cursor-pointer"><input type="checkbox" value="5" class="hidden"
                                    checked onchange="updatePreview()"><span
                                    class="w-10 h-10 rounded-full border-2 border-blue-600 bg-blue-600 text-white font-semibold text-sm flex items-center justify-center select-none">T6</span></label>
                            <label class="day-btn cursor-pointer"><input type="checkbox" value="6" class="hidden"
                                    onchange="updatePreview()"><span
                                    class="w-10 h-10 rounded-full border-2 border-gray-300 text-gray-500 font-semibold text-sm flex items-center justify-center select-none">T7</span></label>
                            <label class="day-btn cursor-pointer"><input type="checkbox" value="0" class="hidden"
                                    onchange="updatePreview()"><span
                                    class="w-10 h-10 rounded-full border-2 border-gray-300 text-gray-500 font-semibold text-sm flex items-center justify-center select-none">CN</span></label>
                        </div>
                    </div>

                    <!-- Morning -->
                    <div class="mb-3 p-4 bg-amber-50 border border-amber-100 rounded-xl">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-sm font-medium text-amber-800">☀️ Buổi sáng</span>
                            <label class="ml-auto flex items-center gap-1.5 text-xs text-gray-500 cursor-pointer">
                                <input type="checkbox" id="morning-enabled" checked onchange="updatePreview()"
                                    class="accent-amber-500 w-3.5 h-3.5"> Bật
                            </label>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div><label class="text-xs text-gray-500 block mb-1">Từ</label><input type="time"
                                    id="morning-start" value="08:00" onchange="updatePreview()"
                                    class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                            </div>
                            <div><label class="text-xs text-gray-500 block mb-1">Đến</label><input type="time"
                                    id="morning-end" value="12:00" onchange="updatePreview()"
                                    class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                            </div>
                        </div>
                    </div>

                    <!-- Afternoon -->
                    <div class="mb-4 p-4 bg-sky-50 border border-sky-100 rounded-xl">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-sm font-medium text-sky-800">🌤️ Buổi chiều</span>
                            <label class="ml-auto flex items-center gap-1.5 text-xs text-gray-500 cursor-pointer">
                                <input type="checkbox" id="afternoon-enabled" checked onchange="updatePreview()"
                                    class="accent-blue-500 w-3.5 h-3.5"> Bật
                            </label>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div><label class="text-xs text-gray-500 block mb-1">Từ</label><input type="time"
                                    id="afternoon-start" value="13:30" onchange="updatePreview()"
                                    class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-sky-400">
                            </div>
                            <div><label class="text-xs text-gray-500 block mb-1">Đến</label><input type="time"
                                    id="afternoon-end" value="17:00" onchange="updatePreview()"
                                    class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-sky-400">
                            </div>
                        </div>
                    </div>

                    <!-- Slot duration & max patients -->
                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <div>
                            <label class="text-sm font-medium text-gray-700 block mb-1">Thời lượng/slot <span
                                    class="text-xs text-gray-400 font-normal">(nội bộ)</span></label>
                            <select id="slot-duration" onchange="updatePreview()"
                                class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="15">15 phút</option>
                                <option value="20">20 phút</option>
                                <option value="30" selected>30 phút</option>
                                <option value="45">45 phút</option>
                                <option value="60">60 phút</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 block mb-1">Bệnh nhân tối đa/slot</label>
                            <input type="number" id="max-slot" value="10" min="1" max="50" onchange="updatePreview()"
                                class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Apply range -->
                    <div class="mb-5">
                        <label class="text-sm font-medium text-gray-700 block mb-2">Áp dụng cho bao nhiêu tuần
                            tới?</label>
                        <div class="flex gap-2">
                            <button
                                class="apply-btn flex-1 py-2 rounded-lg text-sm font-medium border-2 border-blue-600 bg-blue-600 text-white"
                                data-weeks="2" onclick="selectWeeks(this)">2 tuần</button>
                            <button
                                class="apply-btn flex-1 py-2 rounded-lg text-sm font-medium border-2 border-gray-200 text-gray-700"
                                data-weeks="4" onclick="selectWeeks(this)">4 tuần</button>
                            <button
                                class="apply-btn flex-1 py-2 rounded-lg text-sm font-medium border-2 border-gray-200 text-gray-700"
                                data-weeks="8" onclick="selectWeeks(this)">8 tuần</button>
                            <button
                                class="apply-btn flex-1 py-2 rounded-lg text-sm font-medium border-2 border-gray-200 text-gray-700"
                                data-weeks="12" onclick="selectWeeks(this)">3 tháng</button>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Lịch áp dụng đến: <strong id="range-end-date">—</strong>
                        </p>
                    </div>

                    <button onclick="saveRecurring()"
                        class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Lưu & áp dụng lịch
                    </button>
                </div>

                <!-- Preview -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="font-bold text-lg mb-4 flex items-center gap-2">
                        <span
                            class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-sm font-bold flex items-center justify-center">2</span>
                        Xem trước lịch tuần
                    </h2>
                    <div id="week-preview" class="overflow-x-auto min-h-32"></div>
                    <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-500 leading-relaxed" id="preview-summary">Đang tính...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ====== TAB: DAY OFF ====== -->
        <div id="tab-dayoff" class="hidden fade-in">
            <div class="grid lg:grid-cols-2 gap-6">

                <!-- Form -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="font-bold text-lg mb-5 flex items-center gap-2">
                        <span
                            class="w-7 h-7 rounded-full bg-orange-100 text-orange-700 text-sm font-bold flex items-center justify-center">+</span>
                        Đăng ký ngày nghỉ
                    </h2>

                    <!-- Type -->
                    <div class="mb-4">
                        <label class="text-sm font-medium text-gray-700 block mb-2">Loại nghỉ</label>
                        <div class="grid grid-cols-3 gap-2" id="type-cards">
                            <label class="cursor-pointer" onclick="selectDayOffType('sick', this)">
                                <input type="radio" name="dayoff-type" value="sick" class="hidden" checked>
                                <div
                                    class="p-3 rounded-xl border-2 border-red-400 bg-red-50 text-center transition-all">
                                    <div class="text-2xl mb-1">🤒</div>
                                    <div class="text-xs font-medium text-red-700">Bệnh / đột xuất</div>
                                </div>
                            </label>
                            <label class="cursor-pointer" onclick="selectDayOffType('leave', this)">
                                <input type="radio" name="dayoff-type" value="leave" class="hidden">
                                <div
                                    class="p-3 rounded-xl border-2 border-gray-200 bg-gray-50 text-center transition-all">
                                    <div class="text-2xl mb-1">🏖️</div>
                                    <div class="text-xs font-medium text-gray-600">Nghỉ phép</div>
                                </div>
                            </label>
                            <label class="cursor-pointer" onclick="selectDayOffType('conference', this)">
                                <input type="radio" name="dayoff-type" value="conference" class="hidden">
                                <div
                                    class="p-3 rounded-xl border-2 border-gray-200 bg-gray-50 text-center transition-all">
                                    <div class="text-2xl mb-1">🎓</div>
                                    <div class="text-xs font-medium text-gray-600">Hội nghị / đào tạo</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Single vs range -->
                    <div class="mb-4">
                        <label class="text-sm font-medium text-gray-700 block mb-2">Kiểu nghỉ</label>
                        <div class="flex gap-2">
                            <button id="single-btn"
                                class="flex-1 py-2 text-sm rounded-lg border-2 border-blue-600 bg-blue-600 text-white font-medium"
                                onclick="toggleRange(false)">Nghỉ 1 ngày</button>
                            <button id="range-btn"
                                class="flex-1 py-2 text-sm rounded-lg border-2 border-gray-200 text-gray-700 font-medium"
                                onclick="toggleRange(true)">Nghỉ nhiều ngày</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <label class="text-xs text-gray-500 block mb-1" id="start-lbl">Ngày nghỉ</label>
                            <input type="date" id="dayoff-start"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div id="end-wrap">
                            <label class="text-xs text-gray-500 block mb-1">Đến ngày</label>
                            <input type="date" id="dayoff-end" disabled
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm opacity-40 cursor-not-allowed bg-gray-50">
                        </div>
                    </div>

                    <!-- Session -->
                    <div class="mb-4">
                        <label class="text-sm font-medium text-gray-700 block mb-2">Buổi nghỉ</label>
                        <div class="grid grid-cols-3 gap-2" id="session-btns">
                            <button
                                class="session-btn py-2 text-sm rounded-lg border-2 border-blue-600 bg-blue-600 text-white font-medium"
                                data-v="all" onclick="selectSession(this)">Cả ngày</button>
                            <button
                                class="session-btn py-2 text-sm rounded-lg border-2 border-gray-200 text-gray-700 font-medium"
                                data-v="morning" onclick="selectSession(this)">Buổi sáng</button>
                            <button
                                class="session-btn py-2 text-sm rounded-lg border-2 border-gray-200 text-gray-700 font-medium"
                                data-v="afternoon" onclick="selectSession(this)">Buổi chiều</button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-sm font-medium text-gray-700 block mb-1">Lý do <span
                                class="font-normal text-gray-400">(tuỳ chọn)</span></label>
                        <textarea id="dayoff-reason" rows="2" placeholder="Mô tả lý do nghỉ..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>

                    <div class="mb-5 p-3 bg-orange-50 border border-orange-200 rounded-xl flex gap-2.5">
                        <svg class="w-4 h-4 text-orange-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                        </svg>
                        <p class="text-xs text-orange-700">Khi lưu, hệ thống tự động <strong>ẩn toàn bộ slot</strong>
                            ngày nghỉ và gửi email gợi ý lịch mới đến <strong id="affected-count">0</strong> bệnh nhân
                            đã đặt.</p>
                    </div>

                    <button onclick="saveDayOff()"
                        class="w-full bg-orange-500 text-white py-3 rounded-xl font-semibold hover:bg-orange-600 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        Block lịch & thông báo bệnh nhân
                    </button>
                </div>

                <!-- Day-off list -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-bold text-lg">Lịch nghỉ đã đăng ký</h2>
                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-full"
                            id="dayoff-count">—</span>
                    </div>

                    <div class="flex gap-2 mb-4 flex-wrap">
                        <button class="filter-btn text-xs px-3 py-1.5 rounded-full bg-gray-800 text-white font-medium"
                            data-f="all" onclick="setFilter('all', this)">Tất cả</button>
                        <button
                            class="filter-btn text-xs px-3 py-1.5 rounded-full bg-gray-100 text-gray-500 font-medium"
                            data-f="sick" onclick="setFilter('sick', this)">🤒 Bệnh</button>
                        <button
                            class="filter-btn text-xs px-3 py-1.5 rounded-full bg-gray-100 text-gray-500 font-medium"
                            data-f="leave" onclick="setFilter('leave', this)">🏖️ Phép</button>
                        <button
                            class="filter-btn text-xs px-3 py-1.5 rounded-full bg-gray-100 text-gray-500 font-medium"
                            data-f="conference" onclick="setFilter('conference', this)">🎓 Hội nghị</button>
                    </div>

                    <div id="dayoff-list" class="space-y-3"></div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // ===== API CONFIG =====
        const API_BASE = '/api/v1/schedules';
        const getAuthToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

        async function apiCall(method, endpoint, data = null) {
            const url = `${API_BASE}${endpoint}`;
            const options = {
                method,
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getAuthToken(),
                }
            };
            if (data) options.body = JSON.stringify(data);
            try {
                const res = await fetch(url, options);
                const text = await res.text();
                let json = null;
                if (text) {
                    try {
                        json = JSON.parse(text);
                    } catch (parseErr) {
                        if (!res.ok) {
                            showToast(`Lỗi máy chủ: ${res.status}`, 'error');
                            return null;
                        }
                        throw parseErr;
                    }
                }

                if (!res.ok) {
                    showToast(json?.message || `Lỗi ${res.status}`, 'error');
                    return null;
                }
                return json;
            } catch (err) {
                showToast(`Lỗi kết nối: ${err.message}`, 'error');
                return null;
            }
        }
        let applyWeeks = 2;
        let rangeOn = false;
        let selectedDayOffType = 'sick';
        let selectedSession = 'all';
        let currentFilter = 'all';
        let nextId = 100;
        let dayOffs = [
            { id: 1, type: 'sick', icon: '🤒', label: 'Bệnh / đột xuất', color: 'red', date: '2026-04-18', endDate: null, session: 'all', reason: 'Cảm cúm đột xuất', affected: 2 },
            { id: 2, type: 'leave', icon: '🏖️', label: 'Nghỉ phép', color: 'green', date: '2026-04-25', endDate: '2026-04-26', session: 'all', reason: 'Nghỉ lễ 30/4 – 1/5', affected: 8 },
            { id: 3, type: 'conference', icon: '🎓', label: 'Hội nghị', color: 'purple', date: '2026-05-08', endDate: null, session: 'morning', reason: 'Hội nghị tim mạch TPHCM', affected: 0 },
        ];

        // ===== TABS =====
        function switchTab(t) {
            ['recurring', 'dayoff'].forEach(id => {
                document.getElementById('tab-' + id).classList.toggle('hidden', id !== t);
                const btn = document.getElementById('tab-btn-' + id);
                btn.classList.toggle('active', id === t);
                btn.classList.toggle('bg-blue-600', id === t);
                btn.classList.toggle('text-white', id === t);
                btn.classList.toggle('border-blue-600', id === t);
                btn.classList.toggle('border-gray-300', id !== t);
                btn.classList.toggle('text-gray-700', id !== t);
            });
            if (t === 'dayoff') renderDayOffList();
        }

        function onDoctorChange() {
            updatePreview();
            const doctorId = parseInt(document.getElementById('doctor-select').value, 10);
            if (!doctorId || Number.isNaN(doctorId)) {
                showToast('Vui lòng chọn bác sĩ hợp lệ.', 'error');
                return;
            }
            loadDayOffs();
            loadRecurringSchedules();
        }

        // ===== RECURRING =====
        function selectWeeks(btn) {
            applyWeeks = parseInt(btn.dataset.weeks);
            document.querySelectorAll('.apply-btn').forEach(b => {
                b.classList.remove('border-blue-600', 'bg-blue-600', 'text-white');
                b.classList.add('border-gray-200', 'text-gray-700');
            });
            btn.classList.remove('border-gray-200', 'text-gray-700');
            btn.classList.add('border-blue-600', 'bg-blue-600', 'text-white');
            updatePreview();
        }

        function getCheckedDays() {
            return [...document.querySelectorAll('.day-btn input')].filter(i => i.checked).map(i => parseInt(i.value));
        }

        function countSlots(start, end, dur) {
            if (!start || !end) return 0;
            const [sh, sm] = start.split(':').map(Number);
            const [eh, em] = end.split(':').map(Number);
            return Math.max(0, Math.floor(((eh * 60 + em) - (sh * 60 + sm)) / dur));
        }

        function updatePreview() {
            const days = getCheckedDays();
            const mOn = document.getElementById('morning-enabled').checked;
            const aOn = document.getElementById('afternoon-enabled').checked;
            const mS = document.getElementById('morning-start').value;
            const mE = document.getElementById('morning-end').value;
            const aS = document.getElementById('afternoon-start').value;
            const aE = document.getElementById('afternoon-end').value;
            const dur = parseInt(document.getElementById('slot-duration').value);
            const maxPt = parseInt(document.getElementById('max-slot').value) || 1;
            const mCount = mOn ? countSlots(mS, mE, dur) : 0;
            const aCount = aOn ? countSlots(aS, aE, dur) : 0;

            const endDate = new Date();
            endDate.setDate(endDate.getDate() + applyWeeks * 7);
            document.getElementById('range-end-date').textContent = endDate.toLocaleDateString('vi-VN');

            const dayNames = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
            const today = new Date();
            const week = Array.from({ length: 7 }, (_, i) => { const d = new Date(today); d.setDate(today.getDate() + i); return d; });

            let html = `<div class="preview-grid border-b border-gray-100 pb-1 mb-1"><div class="p-cell text-xs text-gray-400">Ca khám</div>`;
            week.forEach(d => {
                const active = days.includes(d.getDay());
                html += `<div class="p-cell font-medium ${active ? 'text-blue-700' : 'text-gray-300'}">${dayNames[d.getDay()]}<br><span class="text-xs ${active ? 'text-gray-500' : 'text-gray-200'}">${d.getDate()}/${d.getMonth() + 1}</span></div>`;
            });
            html += '</div>';

            const rows = [];
            if (mOn && mCount > 0) rows.push({ label: `☀️ ${mS}–${mE}`, count: mCount });
            if (aOn && aCount > 0) rows.push({ label: `🌤️ ${aS}–${aE}`, count: aCount });

            if (rows.length === 0) {
                html += `<div class="text-center text-gray-400 text-sm py-6">Không có ca khám nào được bật</div>`;
            } else {
                rows.forEach(row => {
                    html += `<div class="preview-grid mb-1">
                <div class="p-cell text-xs text-gray-500 text-left leading-tight flex items-center">${row.label}</div>`;
                    week.forEach(d => {
                        const active = days.includes(d.getDay());
                        html += active
                            ? `<div class="p-cell"><span class="slot-pill">${row.count} slot<br>${dur}p/${maxPt}BN</span></div>`
                            : `<div class="p-cell text-gray-200 text-center text-sm">—</div>`;
                    });
                    html += '</div>';
                });
            }

            document.getElementById('week-preview').innerHTML = html;
            const totalDays = applyWeeks * days.length;
            const totalSlots = totalDays * (mCount + aCount);
            document.getElementById('preview-summary').innerHTML =
                `<strong>${totalDays} ngày làm việc</strong> · <strong>${totalSlots} slot khám</strong> trong ${applyWeeks} tuần · Mỗi slot ${dur} phút · Tối đa ${maxPt} bệnh nhân/slot`;
        }

        async function saveRecurring() {
            const days = getCheckedDays();
            if (!days.length) return showToast('Chọn ít nhất 1 ngày làm việc', 'error');
            const dur = parseInt(document.getElementById('slot-duration').value);
            const maxSlot = parseInt(document.getElementById('max-slot').value);
            const mOn = document.getElementById('morning-enabled').checked;
            const aOn = document.getElementById('afternoon-enabled').checked;
            if (!mOn && !aOn) return showToast('Bật ít nhất 1 ca khám (sáng hoặc chiều)', 'error');

            const doctorId = parseInt(document.getElementById('doctor-select').value, 10);
            if (!doctorId || Number.isNaN(doctorId)) {
                return showToast('Vui lòng chọn bác sĩ hợp lệ trước khi lưu.', 'error');
            }
            const data = {
                doctor_id: doctorId,
                room_id: 3,
                days_of_week: days,
                morning_enabled: mOn,
                morning_start: mOn ? document.getElementById('morning-start').value : '08:00',
                morning_end: mOn ? document.getElementById('morning-end').value : '12:00',
                afternoon_enabled: aOn,
                afternoon_start: aOn ? document.getElementById('afternoon-start').value : '13:30',
                afternoon_end: aOn ? document.getElementById('afternoon-end').value : '17:00',
                slot_duration: dur,
                max_slot: maxSlot,
                apply_weeks: applyWeeks
            };

            const res = await apiCall('POST', '/recurring', data);
            if (res?.success) {
                showToast(`✅ ${res.message}`, 'success');
                setTimeout(() => loadRecurringSchedules(), 500);
            }
        }

        // ===== DAY OFF FORM =====
        const typeStyle = {
            sick: { border: 'border-red-400', bg: 'bg-red-50', text: 'text-red-700' },
            leave: { border: 'border-green-400', bg: 'bg-green-50', text: 'text-green-700' },
            conference: { border: 'border-purple-400', bg: 'bg-purple-50', text: 'text-purple-700' },
        };

        function selectDayOffType(type, lbl) {
            selectedDayOffType = type;
            document.querySelectorAll('#type-cards label > div').forEach(d => {
                d.className = 'p-3 rounded-xl border-2 border-gray-200 bg-gray-50 text-center transition-all';
                d.querySelector('div:last-child').className = 'text-xs font-medium text-gray-600';
            });
            const s = typeStyle[type];
            const div = lbl.querySelector('div');
            div.className = `p-3 rounded-xl border-2 ${s.border} ${s.bg} text-center transition-all`;
            div.querySelector('div:last-child').className = `text-xs font-medium ${s.text}`;
            lbl.querySelector('input').checked = true;
        }

        function toggleRange(on) {
            rangeOn = on;
            ['single-btn', 'range-btn'].forEach((id, i) => {
                const active = on ? i === 1 : i === 0;
                document.getElementById(id).classList.toggle('border-blue-600', active);
                document.getElementById(id).classList.toggle('bg-blue-600', active);
                document.getElementById(id).classList.toggle('text-white', active);
                document.getElementById(id).classList.toggle('border-gray-200', !active);
                document.getElementById(id).classList.toggle('text-gray-700', !active);
            });
            const endInput = document.getElementById('dayoff-end');
            endInput.disabled = !on;
            endInput.className = on
                ? 'w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500'
                : 'w-full px-3 py-2 border border-gray-200 rounded-lg text-sm opacity-40 cursor-not-allowed bg-gray-50';
            document.getElementById('start-lbl').textContent = on ? 'Từ ngày' : 'Ngày nghỉ';
        }

        function selectSession(btn) {
            selectedSession = btn.dataset.v;
            document.querySelectorAll('.session-btn').forEach(b => {
                b.classList.remove('border-blue-600', 'bg-blue-600', 'text-white');
                b.classList.add('border-gray-200', 'text-gray-700');
            });
            btn.classList.remove('border-gray-200', 'text-gray-700');
            btn.classList.add('border-blue-600', 'bg-blue-600', 'text-white');
        }

        function saveDayOff() {
            const start = document.getElementById('dayoff-start').value;
            if (!start) return showToast('Vui lòng chọn ngày nghỉ', 'error');
            const end = rangeOn ? document.getElementById('dayoff-end').value : null;
            const reason = document.getElementById('dayoff-reason').value;
            const doctorId = parseInt(document.getElementById('doctor-select').value, 10);
            if (!doctorId || Number.isNaN(doctorId)) {
                return showToast('Vui lòng chọn bác sĩ hợp lệ trước khi lưu ngày nghỉ.', 'error');
            }

            const data = {
                doctor_id: doctorId,
                type: selectedDayOffType,
                date: start,
                end_date: end,
                session: selectedSession,
                reason: reason
            };

            apiCall('POST', '/day-off', data).then(res => {
                if (res?.success) {
                    showToast(`✅ ${res.message}`, 'success');
                    document.getElementById('dayoff-start').value = '';
                    document.getElementById('dayoff-end').value = '';
                    document.getElementById('dayoff-reason').value = '';
                    setTimeout(() => loadDayOffs(), 500);
                }
            });
        }

        // ===== DAY-OFF LIST =====
        function setFilter(f, btn) {
            currentFilter = f;
            document.querySelectorAll('.filter-btn').forEach(b => {
                b.classList.remove('bg-gray-800', 'text-white');
                b.classList.add('bg-gray-100', 'text-gray-500');
            });
            btn.classList.remove('bg-gray-100', 'text-gray-500');
            btn.classList.add('bg-gray-800', 'text-white');
            renderDayOffList();
        }

        function deleteDayOff(id) {
            if (!confirm('Mở lại lịch này? Bệnh nhân sẽ có thể đặt lại.')) return;
            apiCall('DELETE', `/day-off/${id}`).then(res => {
                if (res?.success) {
                    showToast('✅ Đã mở lại lịch', 'success');
                    setTimeout(() => loadDayOffs(), 300);
                }
            });
        }

        async function loadDayOffs() {
            const doctorId = parseInt(document.getElementById('doctor-select').value, 10);
            if (!doctorId || Number.isNaN(doctorId)) {
                return;
            }
            const res = await apiCall('GET', `/day-off/${doctorId}`);
            if (res?.success) {
                dayOffs = (res.data || []).flatMap(group =>
                    group.sessions.map(sess => ({
                        id: sess.schedule_id,
                        type: sess.note?.match(/\[(.*?)\]/)?.[1] || 'leave',
                        date: group.date,
                        reason: sess.note?.replace(/\[.*?\]\s*/, '') || '',
                        affected: 0,
                        session: sess.start_time < '12:00:00' ? 'morning' : (sess.start_time >= '12:00:00' && sess.start_time < '17:00:00' ? 'afternoon' : 'all'),
                        icon: { sick: '🤒', leave: '🏖️', conference: '🎓' }[sess.note?.match(/\[(.*?)\]/)?.[1] || 'leave'] || '📋',
                        label: { sick: 'Bệnh / đột xuất', leave: 'Nghỉ phép', conference: 'Hội nghị' }[sess.note?.match(/\[(.*?)\]/)?.[1] || 'leave'] || 'Nghỉ',
                        color: { sick: 'red', leave: 'green', conference: 'purple' }[sess.note?.match(/\[(.*?)\]/)?.[1] || 'leave'] || 'gray',
                    }))
                );
                renderDayOffList();
            }
        }

        async function loadRecurringSchedules() {
            const doctorId = parseInt(document.getElementById('doctor-select').value, 10);
            if (!doctorId || Number.isNaN(doctorId)) {
                return;
            }
            await apiCall('GET', `/recurring/${doctorId}?per_page=100`);
        }

        function renderDayOffList() {
            const list = currentFilter === 'all' ? dayOffs : dayOffs.filter(d => d.type === currentFilter);
            const colorBg = { red: 'bg-red-50', green: 'bg-green-50', purple: 'bg-purple-50' };
            const colorText = { red: 'text-red-600', green: 'text-green-600', purple: 'text-purple-600' };
            const colorBadge = { red: 'bg-red-100 text-red-700 border-red-200', green: 'bg-green-100 text-green-700 border-green-200', purple: 'bg-purple-100 text-purple-700 border-purple-200' };
            const sessionLabel = { all: 'Cả ngày', morning: 'Buổi sáng', afternoon: 'Buổi chiều' };

            document.getElementById('dayoff-count').textContent = `${list.length} lịch sắp tới`;

            if (!list.length) {
                document.getElementById('dayoff-list').innerHTML = '<p class="text-gray-400 text-sm text-center py-10">Không có lịch nghỉ nào</p>';
                return;
            }

            document.getElementById('dayoff-list').innerHTML = list.map(d => `
        <div class="flex items-start gap-3 p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition-colors">
            <div class="w-10 h-10 rounded-xl ${colorBg[d.color] || 'bg-gray-100'} flex items-center justify-center text-xl flex-shrink-0">${d.icon}</div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="font-semibold text-sm">${d.endDate ? d.date + ' → ' + d.endDate : d.date}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full border ${colorBadge[d.color] || ''}">${sessionLabel[d.session]}</span>
                </div>
                ${d.reason ? `<p class="text-xs text-gray-500 truncate">${d.reason}</p>` : ''}
                ${d.affected > 0 ? `<p class="text-xs ${colorText[d.color] || ''} mt-1">⚠️ ${d.affected} bệnh nhân đã được thông báo</p>` : '<p class="text-xs text-gray-400 mt-1">Chưa có lịch hẹn bị ảnh hưởng</p>'}
            </div>
            <button onclick="deleteDayOff(${d.id})" class="text-gray-300 hover:text-red-500 transition-colors p-1 flex-shrink-0" title="Xoá">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </div>`).join('');
        }

        function showToast(msg, type = 'success') {
            const t = document.createElement('div');
            t.className = `toast ${type}`;
            t.innerHTML = `<p class="text-sm font-medium">${msg}</p>`;
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 3500);
        }

        // ===== INIT =====
        document.addEventListener('DOMContentLoaded', () => {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('dayoff-start').min = today;
            document.getElementById('dayoff-end').min = today;
            document.getElementById('dayoff-start').addEventListener('change', e => {
                document.getElementById('dayoff-end').min = e.target.value;
            });
            updatePreview();
            loadDayOffs();
        });
    </script>
</body>

</html>