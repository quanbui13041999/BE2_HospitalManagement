@forelse($patients as $patient)
<div class="col-12 col-md-6 col-xxl-4 mb-4 patient-card-item" data-id="{{ $patient->user_id }}">
    <div class="card border-0 shadow-sm h-100 patient-hover-card position-relative overflow-hidden" style="border-radius: 16px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); background: #ffffff;">
        {{-- Elegant top accent bar based on membership tier --}}
        @php
            $tier = $patient->membershipCard->tier ?? 'Thường';
            $accentClass = match($tier) {
                'Kim Cương' => 'bg-gradient-diamond',
                'Vàng'      => 'bg-gradient-gold',
                'Bạc'       => 'bg-gradient-silver',
                'Đồng'      => 'bg-gradient-bronze',
                default     => 'bg-secondary'
            };
        @endphp
        <div class="tier-accent {{ $accentClass }}" style="height: 4px; width: 100%;"></div>

        <div class="card-body p-4 d-flex flex-column justify-content-between">
            <div>
                {{-- Header with Avatar & Tier Badge --}}
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="position-relative">
                            @if($patient->avatar_url)
                                <img src="{{ $patient->avatar_url }}" alt="{{ $patient->full_name }}" class="rounded-circle shadow-sm" style="width: 54px; height: 54px; object-fit: cover; border: 2px solid #e2e8f0;">
                            @else
                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm fw-bold fs-5" style="width: 54px; height: 54px; border: 2px solid #dbeafe;">
                                    {{ substr($patient->full_name ?? 'P', 0, 1) }}
                                </div>
                            @endif
                            {{-- Account Status Dot --}}
                            <span class="position-absolute bottom-0 end-0 rounded-circle border border-white border-2" style="width: 13px; height: 13px; background-color: {{ $patient->status ? '#10b981' : '#94a3b8' }};" title="{{ $patient->status ? 'Hoạt động' : 'Tạm khóa' }}"></span>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="mb-0 fw-bold text-slate-800 text-truncate" style="max-width: 140px;" title="{{ $patient->full_name }}">{{ $patient->full_name ?? 'Bệnh nhân ẩn danh' }}</h6>
                                <span class="text-muted small">#P-{{ $patient->user_id }}</span>
                            </div>
                            <p class="text-muted mb-0 small mt-0.5">
                                <span class="fw-medium text-slate-600">{{ $patient->gender ?? 'Không rõ' }}</span>
                                @if($patient->age)
                                    <span class="mx-1">•</span>
                                    <span>{{ $patient->age }} tuổi</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Elegant Tier Badge --}}
                    @php
                        $tierStyle = match($tier) {
                            'Kim Cương' => 'background: linear-gradient(135deg, #4f46e5, #06b6d4); color: #ffffff; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.25); border: none;',
                            'Vàng'      => 'background: linear-gradient(135deg, #eab308, #ca8a04); color: #ffffff; box-shadow: 0 4px 10px rgba(234, 179, 8, 0.25); border: none;',
                            'Bạc'       => 'background: linear-gradient(135deg, #94a3b8, #64748b); color: #ffffff; box-shadow: 0 4px 10px rgba(148, 163, 184, 0.25); border: none;',
                            'Đồng'      => 'background: linear-gradient(135deg, #b45309, #78350f); color: #ffffff; box-shadow: 0 4px 10px rgba(180, 83, 9, 0.25); border: none;',
                            default     => 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;'
                        };
                    @endphp
                    <span class="badge rounded-pill px-2.5 py-1.5 font-semibold text-xs tracking-wider uppercase shadow-sm" style="{{ $tierStyle }}">
                        <i class="bi bi-gem me-1"></i>{{ $tier }}
                    </span>
                </div>

                {{-- Contact Info Row --}}
                <div class="row g-2 mb-3 py-2 border-top border-bottom border-slate-50 text-slate-600" style="font-size: 12.5px;">
                    <div class="col-6 text-truncate" title="{{ $patient->phone }}">
                        <i class="bi bi-telephone text-primary-subtle me-1.5"></i>{{ $patient->phone ?? 'Chưa cập nhật' }}
                    </div>
                    <div class="col-6 text-truncate" title="{{ $patient->email }}">
                        <i class="bi bi-envelope text-primary-subtle me-1.5"></i>{{ $patient->email ?? 'Chưa cập nhật' }}
                    </div>
                </div>

                {{-- Highlights Tags: Insurance, Allergies, Chronic Condition --}}
                <div class="highlights-tags d-flex flex-wrap gap-1.5 mb-4">
                    {{-- Active Insurance --}}
                    @php
                        $activeInsurance = $patient->insuranceCards->firstWhere('status', 'Còn hạn');
                    @endphp
                    @if($activeInsurance)
                        <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-200/60 rounded-lg px-2.5 py-1.5 d-flex align-items-center gap-1 fs-12">
                            <i class="bi bi-shield-check text-emerald-600"></i> BHYT: Còn hạn
                        </span>
                    @else
                        <span class="badge bg-slate-50 text-slate-400 border border-slate-200/50 rounded-lg px-2.5 py-1.5 d-flex align-items-center gap-1 fs-12">
                            <i class="bi bi-shield-x text-slate-300"></i> BHYT: Không
                        </span>
                    @endif

                    {{-- Allergies --}}
                    @if($patient->patientAllergies->isNotEmpty())
                        @php
                            $allAllergens = $patient->patientAllergies->pluck('allergen')->take(2)->implode(', ');
                            $moreAllergiesCount = $patient->patientAllergies->count() - 2;
                        @endphp
                        <span class="badge bg-rose-50 text-rose-700 border border-rose-200/60 rounded-lg px-2.5 py-1.5 d-flex align-items-center gap-1 fs-12" title="Dị ứng: {{ $patient->patientAllergies->pluck('allergen')->implode(', ') }}">
                            <i class="bi bi-exclamation-triangle text-rose-500"></i>
                            Dị ứng: {{ $allAllergens }}{{ $moreAllergiesCount > 0 ? " +{$moreAllergiesCount}" : "" }}
                        </span>
                    @endif

                    {{-- Chronic Illnesses --}}
                    @php
                        $chronicConditions = $patient->patientMedicalHistories->where('is_chronic', 1);
                    @endphp
                    @if($chronicConditions->isNotEmpty())
                        @php
                            $allChronis = $chronicConditions->pluck('condition')->take(2)->implode(', ');
                            $moreChronicCount = $chronicConditions->count() - 2;
                        @endphp
                        <span class="badge bg-amber-50 text-amber-700 border border-amber-200/60 rounded-lg px-2.5 py-1.5 d-flex align-items-center gap-1 fs-12" title="Bệnh mãn tính: {{ $chronicConditions->pluck('condition')->implode(', ') }}">
                            <i class="bi bi-heart-pulse text-amber-500"></i>
                            Mãn tính: {{ $allChronis }}{{ $moreChronicCount > 0 ? " +{$moreChronicCount}" : "" }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Last Appointment Info & Action Button --}}
            <div class="mt-auto">
                <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 mb-3" style="min-height: 58px;">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted small font-medium"><i class="bi bi-clock me-1"></i>Lịch khám gần nhất</span>
                        <span class="badge bg-blue-50 text-blue-700 border border-blue-100 rounded px-1.5 py-0.5 fs-10 fw-semibold">{{ $patient->total_appointments }} lượt khám</span>
                    </div>
                    @if($patient->last_appointment)
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-slate-800 fw-semibold text-truncate small" style="max-width: 140px;">
                                BS. {{ $patient->last_appointment->doctorSchedule->doctor->full_name ?? 'Không rõ' }}
                            </span>
                            <span class="text-slate-500 small">
                                {{ \Carbon\Carbon::parse($patient->last_appointment->appointment_time)->format('d/m/Y') }}
                            </span>
                        </div>
                    @else
                        <div class="text-slate-400 small italic">Chưa có lịch sử khám</div>
                    @endif
                </div>

                {{-- Action Button --}}
                <button class="btn btn-outline-primary border-2 w-100 btn-view-detail py-2 rounded-xl fw-bold text-sm tracking-wide d-flex align-items-center justify-content-center gap-2" 
                        data-id="{{ $patient->user_id }}" 
                        style="transition: all 0.2s ease; border-color: rgba(30, 41, 59, 0.1);">
                    <i class="bi bi-file-medical"></i> Xem hồ sơ bệnh án
                </button>
            </div>
        </div>
    </div>
</div>
@empty
<div class="col-12 py-5 text-center my-4">
    <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle shadow-sm mb-3" style="width: 80px; height: 80px;">
        <i class="bi bi-clipboard-x text-muted fs-2"></i>
    </div>
    <h5 class="fw-bold text-slate-800">Không tìm thấy bệnh nhân nào</h5>
    <p class="text-muted text-sm mx-auto" style="max-width: 400px;">Hãy thử điều chỉnh lại bộ lọc hoặc thay đổi cụm từ tìm kiếm AI để có kết quả tốt hơn.</p>
</div>
@endforelse

{{-- Extra CSS Utilities specifically for Card badging --}}
<style>
    .fs-12 { font-size: 11.5px !important; }
    .fs-10 { font-size: 9.5px !important; }
    .bg-gradient-diamond { background: linear-gradient(90deg, #4f46e5, #06b6d4, #4f46e5); }
    .bg-gradient-gold { background: linear-gradient(90deg, #ca8a04, #eab308, #ca8a04); }
    .bg-gradient-silver { background: linear-gradient(90deg, #64748b, #94a3b8, #64748b); }
    .bg-gradient-bronze { background: linear-gradient(90deg, #78350f, #b45309, #78350f); }
    
    /* Curated colors */
    .text-slate-800 { color: #1e293b !important; }
    .text-slate-600 { color: #475569 !important; }
    .bg-emerald-50 { background-color: #ecfdf5 !important; }
    .text-emerald-700 { color: #047857 !important; }
    .border-emerald-200\/60 { border-color: rgba(167, 243, 208, 0.6) !important; }
    .bg-rose-50 { background-color: #fff1f2 !important; }
    .text-rose-700 { color: #be123c !important; }
    .border-rose-200\/60 { border-color: rgba(254, 205, 211, 0.6) !important; }
    .bg-amber-50 { background-color: #fffbeb !important; }
    .text-amber-700 { color: #b45309 !important; }
    .border-amber-200\/60 { border-color: rgba(253, 230, 138, 0.6) !important; }
    .bg-blue-50 { background-color: #eff6ff !important; }
    .text-blue-700 { color: #1d4ed8 !important; }
    .bg-slate-50 { background-color: #f8fafc !important; }
    .border-slate-100 { border-color: #f1f5f9 !important; }
    
    .patient-hover-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.08) !important;
        border-color: rgba(37, 99, 235, 0.1) !important;
    }
    .patient-hover-card:hover .btn-view-detail {
        background-color: #1e40af !important;
        color: #ffffff !important;
        border-color: #1e40af !important;
    }
</style>
