<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê & Dashboard - MediBook</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        .stat-card {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .time-range-btn.active {
            background-color: #2563eb;
            color: white;
        }

        .time-range-btn:not(.active) {
            background-color: white;
            color: #374151;
            border: 1px solid #d1d5db;
        }
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">MediBook</h1>
                        <p class="text-xs text-gray-500">Hệ thống đặt lịch thông minh</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex gap-1 overflow-x-auto">
                <a href="{{ route('doctor.dashboard') }}" class="nav-link flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Bác sĩ
                </a>
                <a href="{{ route('doctor.schedule') }}" class="nav-link flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Lịch làm việc
                </a>
                 @auth
                @if(auth()->user()->is_admin ?? false)
                <a href="{{ route('admin.dashboard') }}" class="nav-link active flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 0１１ １v6a１ １ ０ ０１－１ １h－２a１ １ ０ ０１－１－１v－６z"/>
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

        <!-- Time range selector -->
        <div class="mb-6 flex gap-2">
            <button class="time-range-btn active px-4 py-2 rounded-lg font-medium transition-colors" onclick="setTimeRange('week')">
                7 ngày
            </button>
            <button class="time-range-btn px-4 py-2 rounded-lg font-medium transition-colors" onclick="setTimeRange('month')">
                30 ngày
            </button>
            <button class="time-range-btn px-4 py-2 rounded-lg font-medium transition-colors" onclick="setTimeRange('year')">
                1 năm
            </button>
        </div>

        <!-- Stats Overview: Lịch hẹn -->
        <div class="mb-6">
            <h2 class="text-xl font-bold mb-4">📅 Tổng quan lịch hẹn</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-green-600 bg-green-50 px-2 py-1 rounded">+12%</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Tổng lịch hẹn</p>
                    <p class="text-3xl font-bold text-gray-900">247</p>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-green-600 bg-green-50 px-2 py-1 rounded">+8%</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Hoàn thành</p>
                    <p class="text-3xl font-bold text-gray-900">189</p>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-red-600 bg-red-50 px-2 py-1 rounded">23.5%</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Tỷ lệ hủy</p>
                    <p class="text-3xl font-bold text-gray-900">58</p>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded">5</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Bác sĩ hoạt động</p>
                    <p class="text-3xl font-bold text-gray-900">5</p>
                </div>
            </div>
        </div>

        <!-- Stats Overview: Bệnh nhân -->
        <div class="mb-6">
            <h2 class="text-xl font-bold mb-4">👥 Thống kê bệnh nhân</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-indigo-600 bg-indigo-50 px-2 py-1 rounded">+15%</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Tổng bệnh nhân</p>
                    <p class="text-3xl font-bold text-gray-900">1,234</p>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded">+32%</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Bệnh nhân mới</p>
                    <p class="text-3xl font-bold text-gray-900">156</p>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-cyan-600 bg-cyan-50 px-2 py-1 rounded">68%</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Bệnh nhân quay lại</p>
                    <p class="text-3xl font-bold text-gray-900">91</p>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-600 bg-gray-50 px-2 py-1 rounded">54% / 46%</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Nam / Nữ</p>
                    <p class="text-3xl font-bold text-gray-900">667 / 567</p>
                </div>
            </div>
        </div>

        <!-- Stats Overview: Hiệu suất -->
        <div class="mb-8">
            <h2 class="text-xl font-bold mb-4">⚡ Hiệu suất hệ thống</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-green-600 bg-green-50 px-2 py-1 rounded">-5 phút</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Thời gian chờ TB</p>
                    <p class="text-3xl font-bold text-gray-900">18 <span class="text-lg text-gray-500">phút</span></p>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded">Ổn định</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Thời gian khám TB</p>
                    <p class="text-3xl font-bold text-gray-900">22 <span class="text-lg text-gray-500">phút</span></p>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-yellow-600 bg-yellow-50 px-2 py-1 rounded">⭐ Cao</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Mức độ hài lòng</p>
                    <p class="text-3xl font-bold text-gray-900">4.6 <span class="text-lg text-gray-500">/5.0</span></p>
                </div>

                <div class="stat-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-violet-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-violet-600 bg-violet-50 px-2 py-1 rounded">234 đánh giá</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Tỷ lệ phản hồi</p>
                    <p class="text-3xl font-bold text-gray-900">76 <span class="text-lg text-gray-500">%</span></p>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid lg:grid-cols-2 gap-6 mb-6">
            <!-- Daily appointments chart -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Số lượng lịch hẹn theo ngày</h2>
                <div class="chart-container">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>

            <!-- Trend chart -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Xu hướng lịch hẹn</h2>
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid lg:grid-cols-3 gap-6 mb-6">
            <!-- Specialty distribution -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Phân bố theo chuyên khoa</h2>
                <div class="chart-container">
                    <canvas id="specialtyChart"></canvas>
                </div>
            </div>

            <!-- Status distribution -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Trạng thái lịch hẹn</h2>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <!-- Patient Age Distribution -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Phân bố độ tuổi bệnh nhân</h2>
                <div class="chart-container">
                    <canvas id="ageChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Charts Row 3 -->
        <div class="grid lg:grid-cols-2 gap-6 mb-6">
            <!-- Patient Type Trend -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Xu hướng bệnh nhân mới vs quay lại</h2>
                <div class="chart-container">
                    <canvas id="patientTypeChart"></canvas>
                </div>
            </div>

            <!-- Satisfaction Trend -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Xu hướng mức độ hài lòng</h2>
                <div class="chart-container">
                    <canvas id="satisfactionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="grid lg:grid-cols-2 gap-6 mb-6">
            <!-- Wait Time by Specialty -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Thời gian chờ theo chuyên khoa</h2>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">Tim mạch</span>
                            <span class="text-sm text-gray-600">15 phút</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: 75%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">Nội tiết</span>
                            <span class="text-sm text-gray-600">18 phút</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: 65%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">Da liễu</span>
                            <span class="text-sm text-gray-600">22 phút</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-orange-500 h-2 rounded-full" style="width: 55%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">Nhi khoa</span>
                            <span class="text-sm text-gray-600">20 phút</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: 60%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Satisfaction by Doctor -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4">Mức độ hài lòng theo bác sĩ</h2>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">TS.BS. Phạm Thị Dung</span>
                            <span class="text-sm text-yellow-600 font-semibold">4.9 ⭐</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: 98%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">BS. Nguyễn Văn An</span>
                            <span class="text-sm text-yellow-600 font-semibold">4.9 ⭐</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: 98%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">TS.BS. Trần Thị Bình</span>
                            <span class="text-sm text-yellow-600 font-semibold">4.8 ⭐</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: 96%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">BS. Võ Minh Em</span>
                            <span class="text-sm text-yellow-600 font-semibold">4.8 ⭐</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: 96%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">BS. Lê Hoàng Cường</span>
                            <span class="text-sm text-yellow-600 font-semibold">4.7 ⭐</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: 94%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Doctors Table -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
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
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <span class="text-2xl">🥇</span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=400" alt="Doctor" class="w-10 h-10 rounded-full object-cover">
                                    <span class="font-medium">TS.BS. Phạm Thị Dung</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-gray-600">Tim mạch</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-1">
                                    <span class="font-semibold text-yellow-600">4.9</span>
                                    <span class="text-yellow-400">⭐</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-gray-600">312</td>
                            <td class="py-3 px-4 text-gray-600">20 năm</td>
                        </tr>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <span class="text-2xl">🥈</span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=400" alt="Doctor" class="w-10 h-10 rounded-full object-cover">
                                    <span class="font-medium">BS. Nguyễn Văn An</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-gray-600">Tim mạch</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-1">
                                    <span class="font-semibold text-yellow-600">4.9</span>
                                    <span class="text-yellow-400">⭐</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-gray-600">234</td>
                            <td class="py-3 px-4 text-gray-600">15 năm</td>
                        </tr>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <span class="text-2xl">🥉</span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://images.unsplash.com/photo-1594824476967-48c8b964273f?w=400" alt="Doctor" class="w-10 h-10 rounded-full object-cover">
                                    <span class="font-medium">TS.BS. Trần Thị Bình</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-gray-600">Nội tiết</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-1">
                                    <span class="font-semibold text-yellow-600">4.8</span>
                                    <span class="text-yellow-400">⭐</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-gray-600">189</td>
                            <td class="py-3 px-4 text-gray-600">12 năm</td>
                        </tr>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <span class="text-gray-500">#4</span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://images.unsplash.com/photo-1537368910025-700350fe46c7?w=400" alt="Doctor" class="w-10 h-10 rounded-full object-cover">
                                    <span class="font-medium">BS. Võ Minh Em</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-gray-600">Nhi khoa</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-1">
                                    <span class="font-semibold text-yellow-600">4.8</span>
                                    <span class="text-yellow-400">⭐</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-gray-600">201</td>
                            <td class="py-3 px-4 text-gray-600">10 năm</td>
                        </tr>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <span class="text-gray-500">#5</span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://images.unsplash.com/photo-1622253692010-333f2da6031d?w=400" alt="Doctor" class="w-10 h-10 rounded-full object-cover">
                                    <span class="font-medium">BS. Lê Hoàng Cường</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-gray-600">Da liễu</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-1">
                                    <span class="font-semibold text-yellow-600">4.7</span>
                                    <span class="text-yellow-400">⭐</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-gray-600">156</td>
                            <td class="py-3 px-4 text-gray-600">8 năm</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // Daily appointments chart
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        const dailyChart = new Chart(dailyCtx, {
            type: 'bar',
            data: {
                labels: ['14/04', '15/04', '16/04', '17/04', '18/04', '19/04', '20/04'],
                datasets: [
                    {
                        label: 'Tổng',
                        data: [12, 15, 8, 18, 14, 10, 16],
                        backgroundColor: '#3b82f6'
                    },
                    {
                        label: 'Hoàn thành',
                        data: [10, 12, 7, 15, 12, 9, 14],
                        backgroundColor: '#10b981'
                    },
                    {
                        label: 'Hủy',
                        data: [2, 3, 1, 3, 2, 1, 2],
                        backgroundColor: '#ef4444'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Trend chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: ['14/04', '15/04', '16/04', '17/04', '18/04', '19/04', '20/04'],
                datasets: [
                    {
                        label: 'Tổng',
                        data: [12, 15, 8, 18, 14, 10, 16],
                        borderColor: '#3b82f6',
                        tension: 0.4,
                        fill: false
                    },
                    {
                        label: 'Hoàn thành',
                        data: [10, 12, 7, 15, 12, 9, 14],
                        borderColor: '#10b981',
                        tension: 0.4,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Specialty chart
        const specialtyCtx = document.getElementById('specialtyChart').getContext('2d');
        const specialtyChart = new Chart(specialtyCtx, {
            type: 'pie',
            data: {
                labels: ['Tim mạch', 'Nội tiết', 'Da liễu', 'Nhi khoa'],
                datasets: [{
                    data: [2, 1, 1, 1],
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Status chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Đã đặt', 'Hoàn thành', 'Đã hủy'],
                datasets: [{
                    data: [45, 189, 58],
                    backgroundColor: ['#3b82f6', '#10b981', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Age Distribution chart
        const ageCtx = document.getElementById('ageChart').getContext('2d');
        const ageChart = new Chart(ageCtx, {
            type: 'doughnut',
            data: {
                labels: ['0-18', '19-35', '36-50', '51-65', '65+'],
                datasets: [{
                    data: [15, 28, 32, 18, 7],
                    backgroundColor: ['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Patient Type Trend chart
        const patientTypeCtx = document.getElementById('patientTypeChart').getContext('2d');
        const patientTypeChart = new Chart(patientTypeCtx, {
            type: 'line',
            data: {
                labels: ['14/04', '15/04', '16/04', '17/04', '18/04', '19/04', '20/04'],
                datasets: [
                    {
                        label: 'Bệnh nhân mới',
                        data: [8, 10, 5, 11, 9, 6, 10],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Bệnh nhân quay lại',
                        data: [4, 5, 3, 7, 5, 4, 6],
                        borderColor: '#06b6d4',
                        backgroundColor: 'rgba(6, 182, 212, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Satisfaction Trend chart
        const satisfactionCtx = document.getElementById('satisfactionChart').getContext('2d');
        const satisfactionChart = new Chart(satisfactionCtx, {
            type: 'line',
            data: {
                labels: ['14/04', '15/04', '16/04', '17/04', '18/04', '19/04', '20/04'],
                datasets: [
                    {
                        label: 'Mức độ hài lòng',
                        data: [4.5, 4.6, 4.7, 4.6, 4.8, 4.7, 4.6],
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 4.0,
                        max: 5.0
                    }
                }
            }
        });

        function setTimeRange(range) {
            document.querySelectorAll('.time-range-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            // In a real app, this would reload data for the selected time range
        }
    </script>
</body>
</html>
