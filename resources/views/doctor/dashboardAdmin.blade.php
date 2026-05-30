<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê & Dashboard - MediBook</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
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
        .stat-card { animation: fadeIn 0.5s ease-in; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .chart-container { position: relative; height: 300px; }
        .time-range-btn.active {
            background-color: #2563eb;
            color: white;
        }
        .time-range-btn:not(.active) {
            background-color: white;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        /* FIX: badge thay đổi so kỳ trước */
        .change-badge-up   { background:#dcfce7; color:#15803d; }
        .change-badge-down { background:#fee2e2; color:#b91c1c; }
        .change-badge-flat { background:#f3f4f6; color:#6b7280; }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <a href="{{ route('home') }}">
                            <h1 class="text-xl font-bold text-gray-900">HospitalC</h1>
                        </a>
                        <p class="text-xs text-gray-500">Quản lý bác sĩ</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex gap-1 overflow-x-auto">
                <a href="{{ route('doctor.dashboard') }}"
                    class="nav-link flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Bác sĩ
                </a>
                <a href="{{ route('doctor.schedule') }}"
                    class="nav-link flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Lịch làm việc
                </a>
                @auth
                    @if(auth()->user()->is_admin ?? false)
                        <a href="{{ route('admin.dashboard') }}"
                            class="nav-link active flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors whitespace-nowrap">
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

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold mb-2">Thống kê & Báo cáo</h1>
            <p class="text-gray-600">Tổng quan hiệu suất và hoạt động hệ thống</p>
        </div>

        {{-- FIX: Active button dựa vào $timeRange từ server, không hardcode --}}
        <div class="mb-6 flex gap-2">
            <button class="time-range-btn {{ $timeRange === 'week' ? 'active' : '' }} px-4 py-2 rounded-lg font-medium transition-colors"
                onclick="setTimeRange('week')">
                7 ngày
            </button>
            <button class="time-range-btn {{ $timeRange === 'month' ? 'active' : '' }} px-4 py-2 rounded-lg font-medium transition-colors"
                onclick="setTimeRange('month')">
                30 ngày
            </button>
            <button class="time-range-btn {{ $timeRange === 'year' ? 'active' : '' }} px-4 py-2 rounded-lg font-medium transition-colors"
                onclick="setTimeRange('year')">
                1 năm
            </button>
        </div>

        {{-- Lịch hẹn --}}
        <div class="mb-6">
            <h2 class="text-xl font-bold mb-4">📅 Tổng quan lịch hẹn</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        {{-- FIX: badge từ dữ liệu thực, so với kỳ trước --}}
                        @php $tc = $appointmentStats['total_change'] ?? null; @endphp
                        @if($tc !== null)
                            <span class="text-sm font-medium px-2 py-1 rounded
                                {{ $tc > 0 ? 'change-badge-up' : ($tc < 0 ? 'change-badge-down' : 'change-badge-flat') }}">
                                {{ $tc > 0 ? '+' : '' }}{{ $tc }}%
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Tổng lịch hẹn</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $appointmentStats['total'] }}</p>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        @php $cc = $appointmentStats['completed_change'] ?? null; @endphp
                        @if($cc !== null)
                            <span class="text-sm font-medium px-2 py-1 rounded
                                {{ $cc > 0 ? 'change-badge-up' : ($cc < 0 ? 'change-badge-down' : 'change-badge-flat') }}">
                                {{ $cc > 0 ? '+' : '' }}{{ $cc }}%
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Hoàn thành</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $appointmentStats['completed'] }}</p>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-red-600 bg-red-50 px-2 py-1 rounded">
                            {{ $appointmentStats['cancellation_rate'] }}% hủy
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Đã hủy</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $appointmentStats['cancelled'] }}</p>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded">
                            {{ count($topDoctors) }} bác sĩ
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Đang hoạt động</p>
                    <p class="text-3xl font-bold text-gray-900">{{ count($topDoctors) }}</p>
                </div>
            </div>
        </div>

        {{-- Bệnh nhân --}}
        <div class="mb-6">
            <h2 class="text-xl font-bold mb-4">👥 Thống kê bệnh nhân</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-indigo-600 bg-indigo-50 px-2 py-1 rounded">
                            Tổng cộng
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Tổng bệnh nhân</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($patientStats['total']) }}</p>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded">
                            Tháng này
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Bệnh nhân mới</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $patientStats['new'] }}</p>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-cyan-600 bg-cyan-50 px-2 py-1 rounded">
                            {{ $patientStats['returning_rate'] }}%
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Bệnh nhân quay lại</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $patientStats['returning'] }}</p>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        @php
                            $maleRate   = $patientStats['total'] > 0 ? round(($patientStats['male']   / $patientStats['total']) * 100) : 0;
                            $femaleRate = $patientStats['total'] > 0 ? round(($patientStats['female'] / $patientStats['total']) * 100) : 0;
                        @endphp
                        <span class="text-sm font-medium text-gray-600 bg-gray-50 px-2 py-1 rounded">
                            {{ $maleRate }}% / {{ $femaleRate }}%
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Nam / Nữ</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $patientStats['male'] }} / {{ $patientStats['female'] }}</p>
                </div>
            </div>
        </div>

        {{-- Hiệu suất --}}
        <div class="mb-8">
            <h2 class="text-xl font-bold mb-4">⚡ Hiệu suất hệ thống</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-orange-600 bg-orange-50 px-2 py-1 rounded">Ước tính</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Thời gian chờ TB</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $performanceStats['avg_wait_time'] }}
                        <span class="text-lg text-gray-500">phút</span>
                    </p>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded">Mỗi slot</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Thời gian khám TB</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $performanceStats['avg_examination_time'] }}
                        <span class="text-lg text-gray-500">phút</span>
                    </p>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-yellow-600 bg-yellow-50 px-2 py-1 rounded">⭐ Cao</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Mức độ hài lòng</p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ $performanceStats['avg_rating'] > 0 ? $performanceStats['avg_rating'] : 'N/A' }}
                        @if($performanceStats['avg_rating'] > 0)
                            <span class="text-lg text-gray-500">/5.0</span>
                        @endif
                    </p>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-violet-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-violet-600 bg-violet-50 px-2 py-1 rounded">
                            {{ $performanceStats['review_count'] }} đánh giá
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Tỷ lệ phản hồi</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $performanceStats['review_rate'] }}
                        <span class="text-lg text-gray-500">%</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Số lượng lịch hẹn theo ngày</h2>
                <div class="chart-container">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Xu hướng lịch hẹn</h2>
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid lg:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Phân bố theo chuyên khoa</h2>
                <div class="chart-container">
                    <canvas id="specialtyChart"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Trạng thái lịch hẹn</h2>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Phân bố độ tuổi bệnh nhân</h2>
                <div class="chart-container">
                    <canvas id="ageChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Charts Row 3 -->
        <div class="grid lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Xu hướng bệnh nhân mới vs quay lại</h2>
                <div class="chart-container">
                    <canvas id="patientTypeChart"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Xu hướng mức độ hài lòng</h2>
                <div class="chart-container">
                    <canvas id="satisfactionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="grid lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Thời gian chờ theo chuyên khoa <span class="text-xs font-normal text-gray-400">(ước tính theo slot)</span></h2>
                <div class="space-y-3">
                    @forelse($waitTimeData['specialties'] as $key => $specialty)
                        @php $maxWait = max(array_merge($waitTimeData['wait_times'], [1])); @endphp
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">{{ $specialty }}</span>
                                <span class="text-sm text-gray-600">{{ $waitTimeData['wait_times'][$key] }} phút</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full"
                                    style="width: {{ $maxWait > 0 ? min(($waitTimeData['wait_times'][$key] / $maxWait) * 100, 100) : 0 }}%">
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">Chưa có dữ liệu</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Mức độ hài lòng theo bác sĩ</h2>
                <div class="space-y-3">
                    @forelse($satisfactionByDoctor as $doctor)
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">{{ $doctor['full_name'] }}</span>
                                <span class="text-sm text-yellow-600 font-semibold">{{ $doctor['rating'] }} ⭐</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-yellow-500 h-2 rounded-full"
                                    style="width: {{ ($doctor['rating'] / 5) * 100 }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">Chưa có dữ liệu</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Top Doctors -->
        <div class="grid lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                        Top Bác sĩ (Đánh giá cao nhất)
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Thứ hạng</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Bác sĩ</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Chuyên khoa</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Rating</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Số đánh giá</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Kinh nghiệm</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topDoctors as $index => $doctor)
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-3 px-4">
                                            <span class="text-2xl">
                                                @if($index === 0) 🥇
                                                @elseif($index === 1) 🥈
                                                @elseif($index === 2) 🥉
                                                @else #{{ $index + 1 }}
                                                @endif
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-3">
                                                <img src="{{ $doctor['avatar_url'] ?? 'https://via.placeholder.com/40' }}"
                                                    alt="Doctor" class="w-10 h-10 rounded-full object-cover">
                                                <span class="font-medium">{{ $doctor['full_name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-gray-600">{{ $doctor['department_name'] }}</td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-1">
                                                <span class="font-semibold text-yellow-600">{{ $doctor['rating'] }}</span>
                                                <span class="text-yellow-400">⭐</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-gray-600">{{ $doctor['review_count'] }}</td>
                                        <td class="py-3 px-4 text-gray-600">{{ $doctor['experience'] }} năm</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 px-4 text-center text-gray-500">Chưa có dữ liệu</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    🔥 Bác sĩ Hot Tuần Này
                </h2>

                @if(!empty($topDoctorWeek))
                    <div class="text-center py-4">
                        <img src="{{ $topDoctorWeek['avatar_url'] ?? 'https://via.placeholder.com/100' }}"
                            alt="Doctor" class="w-20 h-20 rounded-full object-cover mx-auto mb-3">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $topDoctorWeek['full_name'] }}</h3>
                        <div class="mb-4">
                            <p class="text-3xl font-bold text-blue-600">{{ $topDoctorWeek['appointment_count'] }}</p>
                            <p class="text-sm text-gray-500">lượt đặt tuần này</p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-3">
                            <p class="text-sm text-gray-600">
                                Được yêu thích nhất tuần này với
                                <span class="font-bold text-blue-600">{{ $topDoctorWeek['appointment_count'] }}</span> lượt đặt.
                            </p>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <p>Chưa có dữ liệu</p>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <script>
        // ── Helpers ──────────────────────────────────────────────────
        const CHART_COLORS = {
            blue:   '#3b82f6',
            green:  '#10b981',
            red:    '#ef4444',
            yellow: '#f59e0b',
            purple: '#8b5cf6',
            cyan:   '#06b6d4',
            orange: '#f97316',
        };

        const defaultOptions = (extra = {}) => ({
            responsive: true,
            maintainAspectRatio: false,
            ...extra,
        });

        // ── Daily appointments bar chart ─────────────────────────────
        new Chart(document.getElementById('dailyChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($dailyData['labels']) !!},
                datasets: [
                    {
                        label: 'Tổng',
                        data: {!! json_encode(array_map(fn($d) => $d['total'], $dailyData['data'])) !!},
                        backgroundColor: CHART_COLORS.blue,
                    },
                    {
                        label: 'Hoàn thành',
                        data: {!! json_encode(array_map(fn($d) => $d['completed'], $dailyData['data'])) !!},
                        backgroundColor: CHART_COLORS.green,
                    },
                    {
                        label: 'Hủy',
                        data: {!! json_encode(array_map(fn($d) => $d['cancelled'], $dailyData['data'])) !!},
                        backgroundColor: CHART_COLORS.red,
                    },
                ],
            },
            options: defaultOptions({ scales: { y: { beginAtZero: true } } }),
        });

        // ── Trend line chart ─────────────────────────────────────────
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($dailyData['labels']) !!},
                datasets: [
                    {
                        label: 'Tổng',
                        data: {!! json_encode(array_map(fn($d) => $d['total'], $dailyData['data'])) !!},
                        borderColor: CHART_COLORS.blue,
                        tension: 0.4,
                        fill: false,
                    },
                    {
                        label: 'Hoàn thành',
                        data: {!! json_encode(array_map(fn($d) => $d['completed'], $dailyData['data'])) !!},
                        borderColor: CHART_COLORS.green,
                        tension: 0.4,
                        fill: false,
                    },
                ],
            },
            options: defaultOptions({ scales: { y: { beginAtZero: true } } }),
        });

        // ── Specialty pie chart ──────────────────────────────────────
        new Chart(document.getElementById('specialtyChart'), {
            type: 'pie',
            data: {
                labels: {!! json_encode($specialtyData['labels']) !!},
                datasets: [{
                    data: {!! json_encode($specialtyData['data']) !!},
                    backgroundColor: Object.values(CHART_COLORS),
                }],
            },
            options: defaultOptions({ plugins: { legend: { position: 'bottom' } } }),
        });

        // ── Status doughnut — FIX: đủ 5 màu cho 5 trạng thái ────────
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($statusData['labels']) !!},
                datasets: [{
                    data: {!! json_encode($statusData['data']) !!},
                    backgroundColor: [
                        CHART_COLORS.blue,    // Chờ xác nhận
                        CHART_COLORS.cyan,    // Đã xác nhận
                        CHART_COLORS.yellow,  // Đang khám
                        CHART_COLORS.green,   // Hoàn thành
                        CHART_COLORS.red,     // Đã hủy
                    ],
                }],
            },
            options: defaultOptions({ plugins: { legend: { position: 'bottom' } } }),
        });

        // ── Age doughnut ─────────────────────────────────────────────
        new Chart(document.getElementById('ageChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($ageData['labels']) !!},
                datasets: [{
                    data: {!! json_encode($ageData['data']) !!},
                    backgroundColor: [
                        CHART_COLORS.purple,
                        CHART_COLORS.blue,
                        CHART_COLORS.green,
                        CHART_COLORS.yellow,
                        CHART_COLORS.red,
                    ],
                }],
            },
            options: defaultOptions({ plugins: { legend: { position: 'bottom' } } }),
        });

        // ── Patient type trend ───────────────────────────────────────
        new Chart(document.getElementById('patientTypeChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($patientTrendData['labels']) !!},
                datasets: [
                    {
                        label: 'Bệnh nhân mới',
                        data: {!! json_encode(array_map(fn($d) => $d['new'], $patientTrendData['data'])) !!},
                        borderColor: CHART_COLORS.green,
                        backgroundColor: 'rgba(16,185,129,0.1)',
                        tension: 0.4,
                        fill: true,
                    },
                    {
                        label: 'Bệnh nhân quay lại',
                        data: {!! json_encode(array_map(fn($d) => $d['returning'], $patientTrendData['data'])) !!},
                        borderColor: CHART_COLORS.cyan,
                        backgroundColor: 'rgba(6,182,212,0.1)',
                        tension: 0.4,
                        fill: true,
                    },
                ],
            },
            options: defaultOptions({ scales: { y: { beginAtZero: true } } }),
        });

        // ── Satisfaction trend — FIX: không cứng min/max ─────────────
        @php
            $hasRatings = collect($satisfactionTrendData['data'])->filter(fn($v) => $v > 0)->count() > 0;
        @endphp
        new Chart(document.getElementById('satisfactionChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($satisfactionTrendData['labels']) !!},
                datasets: [{
                    label: 'Mức độ hài lòng',
                    data: {!! json_encode($satisfactionTrendData['data']) !!},
                    borderColor: CHART_COLORS.yellow,
                    backgroundColor: 'rgba(245,158,11,0.1)',
                    tension: 0.4,
                    fill: true,
                }],
            },
            options: defaultOptions({
                scales: {
                    y: {
                        beginAtZero: !{{ $hasRatings ? 'true' : 'false' }},
                        @if($hasRatings)
                        min: 0,
                        max: 5,
                        @endif
                    },
                },
            }),
        });

        // ── Time range switcher — FIX: chỉ navigate, active class do server render ──
        function setTimeRange(range) {
            window.location.href = '{{ route("admin.dashboard") }}?time_range=' + range;
        }
    </script>
</body>
</html>
