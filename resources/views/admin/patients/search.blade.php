@extends('layouts.admin')

@section('title', 'Tìm kiếm Bệnh nhân Nâng cao')

@push('styles')
    {{-- Load Google Fonts for premium clinical aesthetics --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=DM+Mono:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&display=swap" rel="stylesheet">
    
    <style>
        /* ── Design System & Fonts override ── */
        .clinical-dashboard {
            font-family: 'DM Sans', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
        }
        
        .font-mono {
            font-family: 'DM Mono', monospace;
        }

        /* Curated Colors */
        :root {
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
            
            --blue-50: #eff6ff;
            --blue-100: #dbeafe;
            --blue-500: #3b82f6;
            --blue-600: #2563eb;
            --blue-700: #1d4ed8;
            --blue-800: #1e40af;
            
            --indigo-50: #f5f3ff;
            --indigo-100: #e0e7ff;
            --indigo-500: #6366f1;
            --indigo-600: #4f46e5;
            --indigo-700: #4338ca;
        }

        /* ── Stats Cards ── */
        .stats-card {
            border-radius: 20px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            background: #ffffff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 20px rgba(15, 23, 42, 0.04);
            border-color: rgba(37, 99, 235, 0.15);
        }

        /* ── Search Tabs ── */
        .search-tabs {
            background-color: #f1f5f9;
            border-radius: 14px;
            padding: 5px;
        }
        .search-tab-btn {
            border: none;
            background: transparent;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 10px;
            color: #475569;
            transition: all 0.2s ease;
        }
        .search-tab-btn.active {
            background-color: #ffffff;
            color: #1d4ed8;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        .search-tab-btn.active.ai-tab {
            color: #4f46e5;
            background: #ffffff;
        }

        /* ── Form Inputs ── */
        .clinical-input, .clinical-select {
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            padding: 10.5px 16px;
            font-size: 14px;
            background-color: #ffffff;
            color: #0f172a;
            transition: all 0.2s ease;
        }
        .clinical-input:focus, .clinical-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
        }
        .clinical-label {
            font-size: 12.5px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 6px;
            display: block;
        }

        /* AI input focus glowing effect */
        .ai-textarea {
            border-radius: 16px;
            border: 1px solid #cbd5e1;
            padding: 18px;
            font-size: 15px;
            line-height: 1.6;
            background-color: #ffffff;
            transition: all 0.3s ease;
            resize: none;
        }
        .ai-textarea:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 5px rgba(99, 102, 241, 0.15), inset 0 2px 4px rgba(0,0,0,0.02);
            outline: none;
        }

        /* ── AI Floating Sparkles & Badging ── */
        @keyframes rotateSparks {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .ai-sparkle-icon {
            animation: rotateSparks 8s linear infinite;
        }
        .ai-badge-highlight {
            border: 1.5px dashed rgba(99, 102, 241, 0.4) !important;
            background-color: rgba(245, 243, 255, 0.6) !important;
            animation: pulseHighlight 2s infinite alternate;
        }
        @keyframes pulseHighlight {
            0% { border-color: rgba(99, 102, 241, 0.3); }
            100% { border-color: rgba(99, 102, 241, 0.8); background-color: rgba(245, 243, 255, 0.9); }
        }

        /* ── Shimmer Skeletons ── */
        .shimmer-card {
            background: #ffffff;
            border-radius: 16px;
            height: 280px;
            overflow: hidden;
            position: relative;
        }
        .shimmer-animation {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: shimmerEffect 1.5s infinite linear;
            border-radius: 8px;
        }
        @keyframes shimmerEffect {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        /* Card fly-in animation */
        .patient-card-item {
            animation: fadeInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Prompt library bubbles */
        .prompt-pill {
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #475569;
            padding: 8px 16px;
            border-radius: 99px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .prompt-pill:hover {
            background-color: #e0e7ff;
            border-color: #a5b4fc;
            color: #4f46e5;
            transform: scale(1.02);
        }

        /* Premium scrollbars */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f8fafc;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 99px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid clinical-dashboard pb-5">
    
    {{-- ══ SECTION 1: TOP STATS COUNTS ══════════════════════════════════ --}}
    <div class="row g-4 mb-4">
        {{-- Total Patients --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 stats-card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 46px; height: 46px;">
                            <i class="bi bi-people-fill fs-5"></i>
                        </div>
                        <span class="badge bg-success-subtle text-success font-semibold px-2.5 py-1">Hồ sơ y tế</span>
                    </div>
                    <h6 class="text-muted small mb-1 fw-bold text-uppercase">Tổng số bệnh nhân</h6>
                    <h3 class="mb-0 fw-bold text-slate-800 font-mono">{{ number_format($stats['total_patients']) }}</h3>
                </div>
            </div>
        </div>

        {{-- Total Appointments --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 stats-card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-indigo-subtle text-indigo rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 46px; height: 46px;">
                            <i class="bi bi-journal-check fs-5"></i>
                        </div>
                        <span class="badge bg-indigo-subtle text-indigo font-semibold px-2.5 py-1">Tất cả lượt khám</span>
                    </div>
                    <h6 class="text-muted small mb-1 fw-bold text-uppercase">Tổng lượt đăng ký khám</h6>
                    <h3 class="mb-0 fw-bold text-slate-800 font-mono">{{ number_format($stats['total_appointments']) }}</h3>
                </div>
            </div>
        </div>

        {{-- Appointments Today --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 stats-card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 46px; height: 46px;">
                            <i class="bi bi-calendar-day fs-5"></i>
                        </div>
                        <span class="badge bg-success-subtle text-success font-semibold px-2.5 py-1">Hôm nay</span>
                    </div>
                    <h6 class="text-muted small mb-1 fw-bold text-uppercase">Lịch khám hôm nay</h6>
                    <h3 class="mb-0 fw-bold text-slate-800 font-mono">{{ number_format($stats['appointments_today']) }}</h3>
                </div>
            </div>
        </div>

        {{-- New Patients This Month --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 stats-card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-amber-subtle text-amber rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 46px; height: 46px;">
                            <i class="bi bi-person-plus-fill fs-5"></i>
                        </div>
                        <span class="badge bg-amber-subtle text-amber font-semibold px-2.5 py-1">Tháng {{ now()->format('m/Y') }}</span>
                    </div>
                    <h6 class="text-muted small mb-1 fw-bold text-uppercase">Bệnh nhân đăng ký mới</h6>
                    <h3 class="mb-0 fw-bold text-slate-800 font-mono">+{{ number_format($stats['new_patients_month']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ SECTION 2: SEARCH OPTIONS & CONTROLS ════════════════════════ --}}
    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 20px; background-color: #ffffff;">
        {{-- Custom Navigation Tabs --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-2 border-bottom border-slate-100">
            <div class="search-tabs d-inline-flex">
                <button class="search-tab-btn active" id="tab-standard-trigger" onclick="switchSearchTab('standard')">
                    <i class="bi bi-sliders me-1.5"></i>Tìm kiếm thường (Bộ lọc)
                </button>
                <button class="search-tab-btn" id="tab-ai-trigger" onclick="switchSearchTab('ai')">
                    <i class="bi bi-stars me-1.5 ai-sparkle-icon text-indigo-500"></i>Tìm kiếm bằng AI (Trí tuệ nhân tạo)
                </button>
            </div>
            
            <button class="btn btn-sm btn-outline-secondary rounded-xl px-3 py-2 fw-bold text-xs" onclick="resetAllFilters()">
                <i class="bi bi-arrow-counterclockwise me-1.5"></i>Reset bộ lọc
            </button>
        </div>

        {{-- ══ SUB-TAB 1: STANDARD SEARCH FORM ══ --}}
        <form id="standard-search-form" onsubmit="event.preventDefault(); triggerSearch(1);">
            <div id="standard-search-container" class="transition-container">
                <div class="row g-3">
                    {{-- Generic Keyword --}}
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="clinical-label" for="keyword">Thông tin bệnh nhân</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px; border-color: #cbd5e1;"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control clinical-input border-start-0 ps-0" id="keyword" name="keyword" placeholder="Nhập tên, số ĐT, email, hoặc mã ID..." style="border-radius: 0 12px 12px 0;">
                        </div>
                    </div>

                    {{-- Gender --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="clinical-label" for="gender">Giới tính</label>
                        <select class="form-select clinical-select" id="gender" name="gender">
                            <option value="">-- Tất cả --</option>
                            <option value="Nam">Nam</option>
                            <option value="Nữ">Nữ</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>

                    {{-- Age range --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="clinical-label">Tuổi từ</label>
                        <input type="number" class="form-control clinical-input" id="age_from" name="age_from" placeholder="Tối thiểu" min="0" max="120">
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="clinical-label">Đến tuổi</label>
                        <input type="number" class="form-control clinical-input" id="age_to" name="age_to" placeholder="Tối đa" min="0" max="120">
                    </div>

                    {{-- Account Status --}}
                    <div class="col-6 col-md-3 col-lg-3">
                        <label class="clinical-label" for="status">Trạng thái tài khoản</label>
                        <select class="form-select clinical-select" id="status" name="status">
                            <option value="">-- Tất cả --</option>
                            <option value="1">Hoạt động</option>
                            <option value="0">Tạm khóa</option>
                        </select>
                    </div>

                    {{-- Membership Tier --}}
                    <div class="col-6 col-md-4 col-lg-3">
                        <label class="clinical-label" for="membership_tier">Hạng thẻ thành viên</label>
                        <select class="form-select clinical-select" id="membership_tier" name="membership_tier">
                            <option value="">-- Tất cả hạng thẻ --</option>
                            <option value="Kim Cương">Diamond (Kim Cương)</option>
                            <option value="Vàng">Gold (Vàng)</option>
                            <option value="Bạc">Silver (Bạc)</option>
                            <option value="Đồng">Bronze (Đồng)</option>
                        </select>
                    </div>

                    {{-- BHYT Check --}}
                    <div class="col-6 col-md-4 col-lg-3">
                        <label class="clinical-label" for="has_insurance">Bảo hiểm y tế (BHYT)</label>
                        <select class="form-select clinical-select" id="has_insurance" name="has_insurance">
                            <option value="">-- Tất cả --</option>
                            <option value="1">Còn hạn sử dụng</option>
                        </select>
                    </div>

                    {{-- Chronic Disease --}}
                    <div class="col-6 col-md-4 col-lg-3">
                        <label class="clinical-label" for="chronic_disease">Bệnh mãn tính</label>
                        <input type="text" class="form-control clinical-input" id="chronic_disease" name="chronic_disease" placeholder="Ví dụ: tiểu đường, huyết áp...">
                    </div>

                    {{-- Allergy --}}
                    <div class="col-6 col-md-4 col-lg-3">
                        <label class="clinical-label" for="allergy">Dị ứng lâm sàng</label>
                        <input type="text" class="form-control clinical-input" id="allergy" name="allergy" placeholder="Ví dụ: penicillin, aspirin...">
                    </div>

                    {{-- Reference Data dropdowns queried directly in view --}}
                    @php
                        $departments = \App\Models\Department::all();
                        $doctors = \App\Models\Doctor::where('status', 1)->get();
                    @endphp

                    {{-- Department Visited --}}
                    <div class="col-6 col-md-4 col-lg-3">
                        <label class="clinical-label" for="department_id">Từng khám tại chuyên khoa</label>
                        <select class="form-select clinical-select" id="department_id" name="department_id">
                            <option value="">-- Tất cả chuyên khoa --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department_id }}">{{ $dept->department_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Doctor Visited --}}
                    <div class="col-6 col-md-4 col-lg-3">
                        <label class="clinical-label" for="doctor_id">Bác sĩ từng thăm khám</label>
                        <select class="form-select clinical-select" id="doctor_id" name="doctor_id">
                            <option value="">-- Tất cả bác sĩ --</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->doctor_id }}">BS. {{ $doc->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Appointment Status --}}
                    <div class="col-6 col-md-4 col-lg-3">
                        <label class="clinical-label" for="appointment_status">Trạng thái lịch hẹn</label>
                        <select class="form-select clinical-select" id="appointment_status" name="appointment_status">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="Chờ xác nhận">Chờ xác nhận</option>
                            <option value="Đã xác nhận">Đã xác nhận</option>
                            <option value="Hoàn thành">Hoàn thành</option>
                            <option value="Đã hủy">Đã hủy</option>
                        </select>
                    </div>

                    {{-- Registration Dates --}}
                    <div class="col-6 col-md-4 col-lg-3">
                        <label class="clinical-label" for="registered_from">Ngày đăng ký tài khoản (Từ)</label>
                        <input type="date" class="form-control clinical-input" id="registered_from" name="registered_from">
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <label class="clinical-label" for="registered_to">Ngày đăng ký tài khoản (Đến)</label>
                        <input type="date" class="form-control clinical-input" id="registered_to" name="registered_to">
                    </div>

                    {{-- Appointment Dates --}}
                    <div class="col-6 col-md-4 col-lg-3">
                        <label class="clinical-label" for="appointment_from">Thời gian khám lịch hẹn (Từ)</label>
                        <input type="date" class="form-control clinical-input" id="appointment_from" name="appointment_from">
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <label class="clinical-label" for="appointment_to">Thời gian khám lịch hẹn (Đến)</label>
                        <input type="date" class="form-control clinical-input" id="appointment_to" name="appointment_to">
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary rounded-xl px-4 py-2.5 fw-bold text-sm tracking-wide d-flex align-items-center gap-2 shadow-sm bg-blue-600 border-0" style="transition: all 0.2s ease;">
                        <i class="bi bi-search"></i> Áp dụng bộ lọc tìm kiếm
                    </button>
                </div>
            </div>
        </form>

        {{-- ══ SUB-TAB 2: AI SEARCH FORM ══ --}}
        <div id="ai-search-container" class="transition-container d-none">
            <form id="ai-search-form" onsubmit="event.preventDefault(); processAISearch();">
                <div class="mb-4">
                    <label class="clinical-label" for="ai_query" style="font-size: 14.5px;">Mô tả yêu cầu tìm kiếm của bạn bằng ngôn ngữ tự nhiên</label>
                    <div class="position-relative">
                        <textarea class="form-control ai-textarea w-100" id="ai_query" rows="3" placeholder="Ví dụ: Tìm cho tôi các bệnh nhân nữ trên 40 tuổi có thẻ hạng vàng, bị dị ứng penicillin và đã từng khám tại khoa Nội tổng quát..."></textarea>
                        
                        <div class="position-absolute bottom-0 end-0 m-3 d-flex gap-2">
                            <button type="submit" id="btn-submit-ai" class="btn text-white rounded-xl px-4 py-2.5 fw-bold text-sm tracking-wide d-flex align-items-center gap-2 border-0 shadow-sm" style="background: linear-gradient(135deg, #4f46e5, #3b82f6); transition: all 0.2s ease;">
                                <i class="bi bi-stars ai-sparkle-icon"></i> Phân tích bằng AI
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Prompts Template Library --}}
                <div class="mb-4">
                    <div class="text-muted small fw-bold mb-2 text-uppercase text-xs"><i class="bi bi-lightbulb-fill text-warning me-1.5"></i>Gợi ý câu lệnh mẫu</div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="prompt-pill" onclick="fillAIPrompt(this)">Bệnh nhân nam có bảo hiểm y tế hạng vàng</span>
                        <span class="prompt-pill" onclick="fillAIPrompt(this)">Tìm bệnh nhân nữ trên 50 tuổi bị bệnh mãn tính tiểu đường</span>
                        <span class="prompt-pill" onclick="fillAIPrompt(this)">Hồ sơ bệnh nhân bị dị ứng penicillin mới đăng ký tài khoản trong tháng này</span>
                        <span class="prompt-pill" onclick="fillAIPrompt(this)">Tìm bệnh nhân nam có lịch khám hoàn thành với Bác sĩ</span>
                    </div>
                </div>
            </form>

            {{-- AI Analysis Explanation Box (Hidden initially) --}}
            <div id="ai-explanation-box" class="card border border-indigo-100 rounded-2xl p-4 d-none" style="background-color: rgba(245, 243, 255, 0.4);">
                <div class="d-flex align-items-start gap-3">
                    <div class="bg-indigo-100 text-indigo-700 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; flex-shrink: 0;">
                        <i class="bi bi-stars fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-indigo-900 mb-1">Kết quả phân tích trích xuất dữ liệu của AI</h6>
                        <p class="text-indigo-800 text-sm mb-0" id="ai-explanation-text">...</p>
                        <div class="mt-2.5 d-flex flex-wrap gap-1.5" id="ai-extracted-badges">
                            {{-- Extracted parameters badges will go here --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ SECTION 3: RESULTS GRID ═════════════════════════════════════ --}}
    <div class="card border-0 shadow-sm p-4" style="border-radius: 20px; background-color: #ffffff;">
        {{-- Results Header, Sorters and Sizers --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-3 border-bottom border-slate-100">
            <div>
                <h5 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                    Danh sách kết quả bệnh nhân
                    <span class="badge bg-blue-50 text-blue-700 rounded-pill px-2.5 py-1 text-xs font-bold" id="results-count">0</span>
                </h5>
            </div>

            <div class="d-flex align-items-center gap-3 flex-wrap">
                {{-- Sizing --}}
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted text-xs font-medium">Hiển thị:</span>
                    <select class="form-select form-select-sm border-slate-200" id="per_page" style="border-radius: 8px; width: 70px;" onchange="triggerSearch(1)">
                        <option value="12" selected>12</option>
                        <option value="24">24</option>
                        <option value="48">48</option>
                    </select>
                </div>

                {{-- Sorter --}}
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted text-xs font-medium">Sắp xếp:</span>
                    <select class="form-select form-select-sm border-slate-200" id="sort_by" style="border-radius: 8px; width: 140px;" onchange="triggerSearch(1)">
                        <option value="created_at" selected>Ngày đăng ký</option>
                        <option value="full_name">Tên bệnh nhân</option>
                        <option value="date_of_birth">Ngày sinh</option>
                        <option value="user_id">Mã ID</option>
                    </select>
                    
                    <button class="btn btn-sm btn-light border border-slate-200 p-1.5" id="sort_dir_btn" onclick="toggleSortDir()" style="border-radius: 8px;" title="Đảo chiều sắp xếp">
                        <i class="bi bi-sort-down fs-5" id="sort_dir_icon"></i>
                    </button>
                    <input type="hidden" id="sort_dir" value="desc">
                </div>
            </div>
        </div>

        {{-- Dynamic Patient Cards Grid --}}
        <div class="row g-4" id="patients-results-container">
            {{-- Standard Shimmer Skeletons shown on load --}}
            @for($i = 0; $i < 6; $i++)
                <div class="col-12 col-md-6 col-xxl-4 shimmer-placeholder">
                    <div class="card border border-slate-100 shadow-none shimmer-card p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="shimmer-animation" style="width: 54px; height: 54px; border-radius: 50%;"></div>
                                <div class="flex-grow-1">
                                    <div class="shimmer-animation mb-2" style="width: 60%; height: 16px;"></div>
                                    <div class="shimmer-animation" style="width: 40%; height: 12px;"></div>
                                </div>
                            </div>
                            <div class="shimmer-animation mb-3" style="width: 100%; height: 35px;"></div>
                            <div class="d-flex gap-2">
                                <div class="shimmer-animation" style="width: 30%; height: 22px;"></div>
                                <div class="shimmer-animation" style="width: 45%; height: 22px;"></div>
                            </div>
                        </div>
                        <div class="shimmer-animation mt-3" style="width: 100%; height: 38px;"></div>
                    </div>
                </div>
            @endfor
        </div>

        {{-- Dynamic AJAX Pagination Controls --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-4 pt-3 border-top border-slate-100" id="pagination-controls-wrap">
            <span class="text-muted text-xs font-medium" id="pagination-info">Đang hiển thị...</span>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0 gap-1" id="pagination-links-container">
                    {{-- Pagination links will be populated dynamically --}}
                </ul>
            </nav>
        </div>
    </div>
</div>

{{-- ══ SECTION 4: HIGH-FIDELITY DETAILED CLINICAL PROFILE MODAL ══════════ --}}
<div class="modal fade" id="patientDetailModal" tabindex="-1" aria-labelledby="patientDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-bottom border-slate-100 px-4 py-3" style="background-color: #f8fafc; border-radius: 20px 20px 0 0;">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px;">
                        <i class="bi bi-file-earmark-medical-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-slate-800" id="patientDetailModalLabel">Hồ sơ bệnh án điện tử chi tiết</h5>
                        <p class="text-muted text-xs mb-0 mt-0.5">Dữ liệu hồ sơ bệnh án được cập nhật trực tiếp theo thời gian thực</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4" id="patient-detail-modal-body" style="background-color: #f8fafc;">
                {{-- Loaded dynamically via AJAX --}}
                <div class="text-center py-5">
                    <div class="spinner-border text-primary shadow-sm mb-3" role="status" style="width: 3rem; height: 3rem; border-width: 3px;">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <h6 class="fw-bold text-slate-800">Đang truy xuất hồ sơ bệnh án...</h6>
                    <p class="text-muted text-sm mx-auto mb-0" style="max-width: 300px;">Vui lòng đợi giây lát để hệ thống tải thông tin bệnh án đầy đủ.</p>
                </div>
            </div>
            
            <div class="modal-footer border-top border-slate-100 px-4 py-3 bg-white" style="border-radius: 0 0 20px 20px;">
                <button type="button" class="btn btn-outline-secondary rounded-xl px-4 py-2 fw-bold text-sm" data-bs-dismiss="modal">Đóng hồ sơ bệnh án</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Tab switching state
    let activeTab = 'standard';
    
    // Initial loading
    document.addEventListener('DOMContentLoaded', function() {
        triggerSearch(1);
    });

    /**
     * Switch search tab options
     */
    function switchSearchTab(tab) {
        activeTab = tab;
        const stdTrigger = document.getElementById('tab-standard-trigger');
        const aiTrigger = document.getElementById('tab-ai-trigger');
        const stdContainer = document.getElementById('standard-search-container');
        const aiContainer = document.getElementById('ai-search-container');
        
        if (tab === 'standard') {
            stdTrigger.classList.add('active');
            aiTrigger.classList.remove('active');
            stdContainer.classList.remove('d-none');
            aiContainer.classList.add('d-none');
        } else {
            stdTrigger.classList.remove('active');
            aiTrigger.classList.add('active');
            aiTrigger.classList.add('ai-tab');
            stdContainer.classList.add('d-none');
            aiContainer.classList.remove('d-none');
        }
    }

    /**
     * Helper to populate prompt suggestion
     */
    function fillAIPrompt(pill) {
        document.getElementById('ai_query').value = pill.textContent.trim();
        document.getElementById('ai_query').focus();
    }

    /**
     * Clear all form filters and re-run search
     */
    function resetAllFilters() {
        // Clear Standard Form Inputs
        document.getElementById('keyword').value = '';
        document.getElementById('gender').value = '';
        document.getElementById('age_from').value = '';
        document.getElementById('age_to').value = '';
        document.getElementById('status').value = '';
        document.getElementById('membership_tier').value = '';
        document.getElementById('has_insurance').value = '';
        document.getElementById('chronic_disease').value = '';
        document.getElementById('allergy').value = '';
        document.getElementById('department_id').value = '';
        document.getElementById('doctor_id').value = '';
        document.getElementById('appointment_status').value = '';
        document.getElementById('registered_from').value = '';
        document.getElementById('registered_to').value = '';
        document.getElementById('appointment_from').value = '';
        document.getElementById('appointment_to').value = '';
        
        // Clear AI inputs and explanation
        document.getElementById('ai_query').value = '';
        document.getElementById('ai-explanation-box').classList.add('d-none');
        document.getElementById('ai-explanation-text').textContent = '';
        
        // Remove AI-highlight styles from standard inputs
        document.querySelectorAll('.ai-badge-highlight').forEach(el => {
            el.classList.remove('ai-badge-highlight');
        });
        
        // Trigger generic search
        triggerSearch(1);
    }

    /**
     * Toggle Sorting direction
     */
    function toggleSortDir() {
        const sortDirInput = document.getElementById('sort_dir');
        const icon = document.getElementById('sort_dir_icon');
        
        if (sortDirInput.value === 'desc') {
            sortDirInput.value = 'asc';
            icon.className = 'bi bi-sort-up fs-5';
        } else {
            sortDirInput.value = 'desc';
            icon.className = 'bi bi-sort-down fs-5';
        }
        triggerSearch(1);
    }

    /**
     * Execute AJAX Patient Search
     */
    function triggerSearch(page = 1) {
        const resultsContainer = document.getElementById('patients-results-container');
        
        // Inject Shimmer Loading Skeleton
        resultsContainer.innerHTML = '';
        for (let i = 0; i < 6; i++) {
            resultsContainer.innerHTML += `
                <div class="col-12 col-md-6 col-xxl-4 shimmer-placeholder">
                    <div class="card border border-slate-100 shadow-none shimmer-card p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="shimmer-animation" style="width: 54px; height: 54px; border-radius: 50%;"></div>
                                <div class="flex-grow-1">
                                    <div class="shimmer-animation mb-2" style="width: 60%; height: 16px;"></div>
                                    <div class="shimmer-animation" style="width: 40%; height: 12px;"></div>
                                </div>
                            </div>
                            <div class="shimmer-animation mb-3" style="width: 100%; height: 35px;"></div>
                            <div class="d-flex gap-2">
                                <div class="shimmer-animation" style="width: 30%; height: 22px;"></div>
                                <div class="shimmer-animation" style="width: 45%; height: 22px;"></div>
                            </div>
                        </div>
                        <div class="shimmer-animation mt-3" style="width: 100%; height: 38px;"></div>
                    </div>
                </div>
            `;
        }

        // Collect inputs from Standard Form
        const params = new URLSearchParams({
            page: page,
            keyword: document.getElementById('keyword').value.trim(),
            gender: document.getElementById('gender').value,
            age_from: document.getElementById('age_from').value,
            age_to: document.getElementById('age_to').value,
            status: document.getElementById('status').value,
            membership_tier: document.getElementById('membership_tier').value,
            has_insurance: document.getElementById('has_insurance').value,
            chronic_disease: document.getElementById('chronic_disease').value.trim(),
            allergy: document.getElementById('allergy').value.trim(),
            department_id: document.getElementById('department_id').value,
            doctor_id: document.getElementById('doctor_id').value,
            appointment_status: document.getElementById('appointment_status').value,
            registered_from: document.getElementById('registered_from').value,
            registered_to: document.getElementById('registered_to').value,
            appointment_from: document.getElementById('appointment_from').value,
            appointment_to: document.getElementById('appointment_to').value,
            sort_by: document.getElementById('sort_by').value,
            sort_dir: document.getElementById('sort_dir').value,
            per_page: document.getElementById('per_page').value
        });

        // Request results via AJAX
        fetch(`/admin/patients/search/results?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Populate results
                resultsContainer.innerHTML = data.html;
                document.getElementById('results-count').textContent = data.total;
                
                // Update pagination layout
                buildPagination(data.total, data.current_page, data.last_page);
            }
        })
        .catch(err => {
            console.error('Error fetching search results:', err);
            resultsContainer.innerHTML = `
                <div class="col-12 py-5 text-center">
                    <div class="text-danger mb-2"><i class="bi bi-exclamation-triangle-fill fs-2"></i></div>
                    <h6 class="fw-bold text-slate-800">Không thể kết nối đến máy chủ</h6>
                    <p class="text-muted text-sm mb-0">Vui lòng kiểm tra lại kết nối mạng hoặc liên hệ Quản trị viên hệ thống.</p>
                </div>
            `;
        });
    }

    /**
     * Process Natural Language Search via Anthropic API
     */
    function processAISearch() {
        const queryText = document.getElementById('ai_query').value.trim();
        if (!queryText) return;

        const btnSubmit = document.getElementById('btn-submit-ai');
        const originalHtml = btnSubmit.innerHTML;
        
        // Show AI Loading State
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang phân tích...`;

        // Request AI parser
        fetch('/admin/patients/ai-search', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ query: queryText })
        })
        .then(res => res.json())
        .then(data => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalHtml;

            if (data.success) {
                const filters = data.filters;
                
                // Show AI Explanation Card
                document.getElementById('ai-explanation-box').classList.remove('d-none');
                document.getElementById('ai-explanation-text').innerHTML = `<strong>AI hiểu:</strong> ${data.explanation}`;
                
                // Build extracted parameter badges
                const badgesContainer = document.getElementById('ai-extracted-badges');
                badgesContainer.innerHTML = '';
                
                // Standardize active highlight styles on Standard inputs
                document.querySelectorAll('.ai-badge-highlight').forEach(el => {
                    el.classList.remove('ai-badge-highlight');
                });

                // Helper to map and highlight
                function applyFilter(elementId, value, label) {
                    if (value !== undefined && value !== null && value !== '') {
                        const inputEl = document.getElementById(elementId);
                        if (inputEl) {
                            inputEl.value = value;
                            inputEl.classList.add('ai-badge-highlight');
                            badgesContainer.innerHTML += `
                                <span class="badge bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg px-2.5 py-1.5 d-flex align-items-center gap-1 text-xs">
                                    <i class="bi bi-tag-fill"></i> ${label}: ${value}
                                </span>
                            `;
                        }
                    }
                }

                // Map results into standard fields and badge them
                applyFilter('keyword', filters.keyword, 'Từ khóa');
                applyFilter('gender', filters.gender, 'Giới tính');
                applyFilter('age_from', filters.age_from, 'Tuổi từ');
                applyFilter('age_to', filters.age_to, 'Đến tuổi');
                applyFilter('appointment_status', filters.appointment_status, 'Trạng thái khám');
                applyFilter('has_insurance', filters.has_insurance, 'Bảo hiểm');
                applyFilter('membership_tier', filters.membership_tier, 'Hạng thẻ');
                applyFilter('chronic_disease', filters.chronic_disease, 'Bệnh mãn tính');
                applyFilter('allergy', filters.allergy, 'Dị ứng');
                
                if (filters.sort_by) {
                    document.getElementById('sort_by').value = filters.sort_by;
                }
                if (filters.sort_dir) {
                    document.getElementById('sort_dir').value = filters.sort_dir;
                    const icon = document.getElementById('sort_dir_icon');
                    if (filters.sort_dir === 'asc') {
                        icon.className = 'bi bi-sort-up fs-5';
                    } else {
                        icon.className = 'bi bi-sort-down fs-5';
                    }
                }

                // Show success feedback
                const explanationBox = document.getElementById('ai-explanation-box');
                explanationBox.style.animation = 'pulseHighlight 1.5s 1 alternate';

                // Instantly execute search with populated filters
                triggerSearch(1);
            } else {
                // Display error message
                alert(data.message || 'Lỗi không rõ khi xử lý bằng AI');
            }
        })
        .catch(err => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalHtml;
            console.error('AI Search Connection Error:', err);
            alert('Không thể kết nối dịch vụ AI. Vui lòng sử dụng tính năng "Tìm kiếm thường" hoặc thử lại sau.');
        });
    }

    /**
     * Build Pagination DOM dynamically
     */
    function buildPagination(total, currentPage, lastPage) {
        const perPage = parseInt(document.getElementById('per_page').value);
        const startEntry = total === 0 ? 0 : (currentPage - 1) * perPage + 1;
        const endEntry = Math.min(currentPage * perPage, total);
        
        // Update pagination info text
        document.getElementById('pagination-info').innerHTML = 
            `Hiển thị <strong>${startEntry} - ${endEntry}</strong> trên tổng số <strong>${total}</strong> bệnh nhân`;
            
        const linksContainer = document.getElementById('pagination-links-container');
        linksContainer.innerHTML = '';

        if (lastPage <= 1) {
            document.getElementById('pagination-controls-wrap').classList.add('d-none');
            return;
        } else {
            document.getElementById('pagination-controls-wrap').classList.remove('d-none');
        }

        // Previous Page trigger
        linksContainer.innerHTML += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <button class="page-link border-slate-200" onclick="triggerSearch(${currentPage - 1})" aria-label="Previous" style="border-radius: 8px;">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </li>
        `;

        // Page link elements (Limit to showing maximum 5 surrounding pages)
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(lastPage, currentPage + 2);

        if (startPage > 1) {
            linksContainer.innerHTML += `
                <li class="page-item">
                    <button class="page-link border-slate-200" onclick="triggerSearch(1)">1</button>
                </li>
            `;
            if (startPage > 2) {
                linksContainer.innerHTML += `<li class="page-item disabled"><span class="page-link border-slate-200 border-0">...</span></li>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            linksContainer.innerHTML += `
                <li class="page-item ${currentPage === i ? 'active' : ''}">
                    <button class="page-link border-slate-200 ${currentPage === i ? 'bg-blue-600 text-white' : ''}" onclick="triggerSearch(${i})" style="border-radius: 8px;">${i}</button>
                </li>
            `;
        }

        if (endPage < lastPage) {
            if (endPage < lastPage - 1) {
                linksContainer.innerHTML += `<li class="page-item disabled"><span class="page-link border-slate-200 border-0">...</span></li>`;
            }
            linksContainer.innerHTML += `
                <li class="page-item">
                    <button class="page-link border-slate-200" onclick="triggerSearch(${lastPage})">${lastPage}</button>
                </li>
            `;
        }

        // Next Page trigger
        linksContainer.innerHTML += `
            <li class="page-item ${currentPage === lastPage ? 'disabled' : ''}">
                <button class="page-link border-slate-200" onclick="triggerSearch(${currentPage + 1})" aria-label="Next" style="border-radius: 8px;">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </li>
        `;
    }

    /**
     * Clinical Profile detail loader
     */
    // Event delegation to capture clicks on AJAX-loaded patient card detail buttons
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-view-detail');
        if (!btn) return;
        
        const patientId = btn.getAttribute('data-id');
        const modalBody = document.getElementById('patient-detail-modal-body');
        
        // Show clinical detail modal with bootstrap first
        const myModal = new bootstrap.Modal(document.getElementById('patientDetailModal'));
        myModal.show();
        
        // Reset modal body to loader state
        modalBody.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary shadow-sm mb-3" role="status" style="width: 3rem; height: 3rem; border-width: 3px;">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
                <h6 class="fw-bold text-slate-800">Đang truy xuất hồ sơ bệnh án...</h6>
                <p class="text-muted text-sm mx-auto mb-0" style="max-width: 300px;">Vui lòng đợi giây lát để hệ thống tải thông tin bệnh án đầy đủ.</p>
            </div>
        `;
        
        // Query AJAX detailed profile markup
        fetch(`/admin/patients/${patientId}/detail`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Populate detailed profile markup
                modalBody.innerHTML = data.html;
            } else {
                modalBody.innerHTML = `
                    <div class="text-center py-5 text-danger">
                        <i class="bi bi-exclamation-octagon fs-1 d-block mb-3"></i>
                        <h6 class="fw-bold">Truy xuất dữ liệu thất bại</h6>
                        <p class="text-muted text-sm mb-0">Hồ sơ bệnh án cho bệnh nhân này tạm thời không thể truy xuất.</p>
                    </div>
                `;
            }
        })
        .catch(err => {
            console.error('Error fetching clinical details:', err);
            modalBody.innerHTML = `
                <div class="text-center py-5 text-danger">
                    <i class="bi bi-wifi-off fs-1 d-block mb-3"></i>
                    <h6 class="fw-bold">Lỗi kết nối máy chủ</h6>
                    <p class="text-muted text-sm mb-0">Hệ thống không thể tải hồ sơ chi tiết. Hãy chắc chắn máy chủ đang hoạt động.</p>
                </div>
            `;
        });
    });
</script>
@endpush
