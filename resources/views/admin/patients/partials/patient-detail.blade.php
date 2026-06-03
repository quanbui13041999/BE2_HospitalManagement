<div class="patient-profile-dashboard">
    {{-- ══ PROFILE HEADER ══════════════════════════════════════════════ --}}
    <div class="row g-4 mb-4">
        {{-- Demographic and Contact Profile Card --}}
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 16px; background: linear-gradient(145deg, #ffffff, #fdfdfd);">
                <div class="d-flex align-items-center gap-4 flex-wrap flex-sm-nowrap">
                    @if($patient->avatar_url)
                        <img src="{{ $patient->avatar_url }}" alt="{{ $patient->full_name }}"
                             class="rounded-circle shadow-sm" style="width: 100px; height: 100px; object-fit: cover; border: 4px solid #f1f5f9;"
                             onerror="this.onerror=null; this.src='/images/default-avatar.png';">
                    @else
                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm fw-bold fs-2" style="width: 100px; height: 100px; border: 4px solid #dbeafe;">
                            {{ substr($patient->full_name ?? 'P', 0, 1) }}
                        </div>
                    @endif
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h4 class="mb-0 fw-bold text-slate-800">{{ $patient->full_name }}</h4>
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1.5 fs-12 fw-bold">#P-{{ $patient->user_id }}</span>
                            <span class="badge rounded-pill px-2.5 py-1.5 {{ $patient->status ? 'bg-success-subtle text-success' : 'bg-slate-200 text-slate-600' }} fs-11 fw-semibold">
                                {{ $patient->status ? '● Hoạt động' : '● Tạm khóa' }}
                            </span>
                        </div>
                        <p class="text-muted text-sm mb-3 mt-1">
                            <span class="fw-medium text-slate-700">{{ $patient->gender }}</span>
                            @if($patient->age)
                                <span class="mx-2">•</span>
                                <span class="fw-medium text-slate-700">{{ $patient->age }} tuổi</span>
                            @endif
                            @if($patient->date_of_birth)
                                <span class="mx-2">•</span>
                                <span>Ngày sinh: {{ $patient->date_of_birth->format('d/m/Y') }}</span>
                            @endif
                        </p>
                        
                        {{-- Contact Row --}}
                        <div class="row g-2 text-slate-600 text-sm">
                            <div class="col-12 col-sm-6">
                                <i class="bi bi-telephone text-primary me-2"></i><strong>Điện thoại:</strong> {{ $patient->phone ?? 'Chưa cập nhật' }}
                            </div>
                            <div class="col-12 col-sm-6">
                                <i class="bi bi-envelope text-primary me-2"></i><strong>Email:</strong> {{ $patient->email ?? 'Chưa cập nhật' }}
                            </div>
                            <div class="col-12">
                                <i class="bi bi-geo-alt text-primary me-2"></i><strong>Địa chỉ:</strong> {{ $patient->address ?? 'Chưa cập nhật' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Membership & BHYT Summary --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 16px; background: linear-gradient(145deg, #ffffff, #fcfcfc);">
                <h6 class="fw-bold text-slate-800 border-bottom border-slate-100 pb-2 mb-3">
                    <i class="bi bi-credit-card-2-front text-primary me-2"></i>Hạng thẻ & Bảo hiểm
                </h6>
                
                {{-- Membership tier progress bar --}}
                @if($patient->membershipCard)
                    @php
                        $mCard = $patient->membershipCard;
                        $tier = $mCard->tier;
                        $progress = $mCard->progress_percent;
                        $remaining = $mCard->remaining_to_next_tier;
                        
                        $accentGradient = match($tier) {
                            'Kim Cương' => 'linear-gradient(135deg, #4f46e5, #06b6d4)',
                            'Vàng'      => 'linear-gradient(135deg, #eab308, #ca8a04)',
                            'Bạc'       => 'linear-gradient(135deg, #94a3b8, #64748b)',
                            'Đồng'      => 'linear-gradient(135deg, #b45309, #78350f)',
                            default     => 'linear-gradient(135deg, #cbd5e1, #94a3b8)'
                        };
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1 text-xs">
                            <span class="fw-semibold text-slate-700">Hạng thẻ: <span class="badge text-white px-2 py-0.5 rounded" style="background: {{ $accentGradient }}">{{ $tier }}</span></span>
                            <span class="text-muted fw-medium">{{ number_format($mCard->points) }} điểm</span>
                        </div>
                        <div class="progress shadow-none" style="height: 8px; border-radius: 99px; background-color: #f1f5f9;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $progress }}%; background: {{ $accentGradient }}; border-radius: 99px;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        @if($remaining > 0)
                            <div class="text-slate-500 mt-1" style="font-size: 11px;">
                                Cần chi tiêu thêm <strong>{{ number_format($remaining) }}đ</strong> để thăng hạng <strong>{{ $mCard->next_tier }}</strong>.
                            </div>
                        @else
                            <div class="text-emerald-600 mt-1 fw-medium" style="font-size: 11px;">
                                <i class="bi bi-star-fill text-warning me-1"></i>Đã đạt hạng thẻ cao nhất.
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-muted small italic mb-3">Chưa phát hành thẻ thành viên</div>
                @endif

                {{-- BHYT Insurance --}}
                @php
                    $bhyt = $patient->insuranceCards->first();
                @endphp
                @if($bhyt)
                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 text-xs text-slate-700">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-slate-800"><i class="bi bi-shield-check text-success me-1"></i>Bảo hiểm y tế (BHYT)</span>
                            <span class="badge {{ $bhyt->status === 'Còn hạn' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $bhyt->status }}</span>
                        </div>
                        <div class="row g-1 text-slate-600">
                            <div class="col-6"><strong>Mã số:</strong> <code>{{ $bhyt->card_number }}</code></div>
                            <div class="col-6"><strong>Hưởng BHYT:</strong> <span class="text-emerald-700 fw-bold">{{ round($bhyt->discount_pct) }}%</span></div>
                            <div class="col-6"><strong>Nhà cung cấp:</strong> {{ $bhyt->provider }}</div>
                            <div class="col-6"><strong>Hết hạn:</strong> {{ $bhyt->expiry_date ? $bhyt->expiry_date->format('d/m/Y') : '---' }}</div>
                        </div>
                    </div>
                @else
                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 text-xs text-center text-slate-400 italic">
                        <i class="bi bi-shield-slash text-slate-300 fs-4 d-block mb-1"></i>Chưa cập nhật thông tin bảo hiểm y tế
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ══ STATS DASHBOARD ══════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-sm-3">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius: 12px; background: #eff6ff;">
                <div class="text-blue-700 fw-bold fs-3 mb-0">{{ $patient->stats['total_appointments'] }}</div>
                <div class="text-slate-600 small fw-medium">Tổng lịch khám</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius: 12px; background: #ecfdf5;">
                <div class="text-emerald-700 fw-bold fs-3 mb-0">{{ $patient->stats['completed'] }}</div>
                <div class="text-slate-600 small fw-medium">Đã hoàn thành</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius: 12px; background: #fff1f2;">
                <div class="text-rose-700 fw-bold fs-3 mb-0">{{ $patient->stats['cancelled'] }}</div>
                <div class="text-slate-600 small fw-medium">Đã hủy bỏ</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius: 12px; background: #fffbeb;">
                <div class="text-amber-700 fw-bold fs-3 mb-0">{{ $patient->stats['upcoming'] }}</div>
                <div class="text-slate-600 small fw-medium">Chờ khám / Mới</div>
            </div>
        </div>
    </div>

    {{-- ══ PROFILE BODY ═════════════════════════════════════════════════ --}}
    <div class="row g-4">
        {{-- LEFT COLUMN: Medical background & Clinical highlights --}}
        <div class="col-12 col-lg-4">
            {{-- Allergies Box --}}
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px;">
                <h6 class="fw-bold text-rose-700 mb-3 border-bottom border-slate-100 pb-2">
                    <i class="bi bi-exclamation-octagon-fill text-rose-600 me-2"></i>Dị ứng lâm sàng
                </h6>
                @forelse($patient->patientAllergies as $allergy)
                    <div class="p-3 bg-rose-50/60 rounded-xl border border-rose-100 mb-2.5">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <strong class="text-rose-800 text-sm"><i class="bi bi-shield-alert text-rose-600 me-1"></i>{{ $allergy->allergen }}</strong>
                            @php
                                $severityClass = match($allergy->severity) {
                                    'Severe', 'Nặng' => 'bg-danger text-white',
                                    'Moderate', 'Trung bình' => 'bg-warning text-dark',
                                    default => 'bg-secondary-subtle text-secondary'
                                };
                            @endphp
                            <span class="badge {{ $severityClass }} fs-10 tracking-wider text-uppercase">{{ $allergy->severity }}</span>
                        </div>
                        <div class="text-slate-600 text-xs mt-1">
                            <strong>Phản ứng:</strong> {{ $allergy->reaction ?? 'Không rõ' }}
                        </div>
                        @if($allergy->notes)
                            <div class="text-slate-500 text-xs italic mt-1 border-top border-rose-200/40 pt-1.5">
                                {{ $allergy->notes }}
                            </div>
                        @endif
                        <div class="text-muted" style="font-size: 10px; text-align: right; margin-top: 5px;">
                            Ghi nhận: {{ $allergy->noted_date ? $allergy->noted_date->format('d/m/Y') : '---' }}
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 bg-slate-50 border border-slate-100 rounded-xl text-slate-400 text-xs italic">
                        <i class="bi bi-shield-check text-success fs-3 d-block mb-1"></i>Không phát hiện tiền sử dị ứng
                    </div>
                @endforelse
            </div>

            {{-- Chronic Illnesses Box --}}
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px;">
                <h6 class="fw-bold text-amber-700 mb-3 border-bottom border-slate-100 pb-2">
                    <i class="bi bi-heart-pulse-fill text-amber-600 me-2"></i>Bệnh mãn tính
                </h6>
                @php
                    $chronics = $patient->patientMedicalHistories->where('is_chronic', 1);
                @endphp
                @forelse($chronics as $history)
                    <div class="p-3 bg-amber-50/60 rounded-xl border border-amber-100 mb-2.5">
                        <strong class="text-amber-800 text-sm d-block mb-1">
                            <i class="bi bi-activity text-amber-600 me-1.5"></i>{{ $history->condition }}
                        </strong>
                        <div class="row g-1 text-xs text-slate-600">
                            <div class="col-6"><strong>Chẩn đoán:</strong> {{ $history->diagnosed_at ? $history->diagnosed_at->format('d/m/Y') : '---' }}</div>
                            <div class="col-6"><strong>Điều trị:</strong> {{ $history->treated_at ? $history->treated_at->format('d/m/Y') : 'Chưa điều trị' }}</div>
                        </div>
                        @if($history->notes)
                            <div class="text-slate-500 text-xs italic mt-2 border-top border-amber-200/40 pt-1.5">
                                {{ $history->notes }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-4 bg-slate-50 border border-slate-100 rounded-xl text-slate-400 text-xs italic">
                        <i class="bi bi-heart-pulse text-success fs-3 d-block mb-1"></i>Không phát hiện bệnh mãn tính
                    </div>
                @endforelse
            </div>

            {{-- General Medical History Box --}}
            <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                <h6 class="fw-bold text-slate-800 mb-3 border-bottom border-slate-100 pb-2">
                    <i class="bi bi-clock-history text-slate-600 me-2"></i>Tiền sử bệnh án khác
                </h6>
                @php
                    $nonChronics = $patient->patientMedicalHistories->where('is_chronic', 0);
                @endphp
                @forelse($nonChronics as $history)
                    <div class="mb-2.5 pb-2 border-bottom border-slate-50 text-xs text-slate-700">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-slate-800 text-sm">{{ $history->condition }}</strong>
                            <span class="text-muted" style="font-size: 11px;">{{ $history->diagnosed_at ? $history->diagnosed_at->format('d/m/Y') : '' }}</span>
                        </div>
                        @if($history->notes)
                            <div class="text-slate-500 italic">{{ $history->notes }}</div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-4 bg-slate-50 border border-slate-100 rounded-xl text-slate-400 text-xs italic">
                        Chưa ghi nhận tiền sử bệnh án khác
                    </div>
                @endforelse
            </div>
        </div>

        {{-- RIGHT COLUMN: Medical records history --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 16px; background-color: #ffffff;">
                <h5 class="fw-bold text-slate-800 mb-4 pb-2 border-bottom border-slate-100 d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-journal-medical text-primary me-2"></i>Lịch sử khám & Hồ sơ bệnh án</span>
                    <span class="badge bg-slate-100 text-slate-600 rounded-pill px-2.5 py-1 text-xs fw-semibold">{{ $patient->medicalRecords->count() }} hồ sơ</span>
                </h5>

                {{-- Record Timeline --}}
                @if($patient->medicalRecords->isNotEmpty())
                    <div class="clinical-timeline position-relative ps-4" style="border-left: 2px dashed #e2e8f0; margin-left: 10px;">
                        @foreach($patient->medicalRecords->sortByDesc('exam_date') as $record)
                            <div class="timeline-item position-relative mb-5">
                                {{-- Timeline bullet with status color --}}
                                @php
                                    $bulletBg = match($record->status) {
                                        'completed', 'prescribed' => '#10b981',
                                        'examining' => '#3b82f6',
                                        'cancelled' => '#ef4444',
                                        default => '#f59e0b'
                                    };
                                @endphp
                                <span class="timeline-bullet rounded-circle position-absolute" style="left: -29px; top: 0; width: 16px; height: 16px; background-color: {{ $bulletBg }}; border: 3px solid #ffffff; box-shadow: 0 0 0 4px {{ $bulletBg }}20;"></span>

                                <div class="card border border-slate-100 shadow-none bg-slate-50/50 rounded-2xl p-4">
                                    {{-- Record Header --}}
                                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-3">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <h6 class="fw-bold text-slate-800 mb-0">Hồ sơ khám bệnh <code class="small fw-bold">{{ $record->record_code }}</code></h6>
                                                <span style="background:{{ $record->status_bg }}; color:{{ $record->status_color }}; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:500; display:inline-flex; align-items:center; gap:4px;">
                                                    <span>{{ $record->status_icon }}</span> <span>{{ $record->status_label }}</span>
                                                </span> {{-- fixed: khong render raw HTML tu accessor --}}
                                            </div>
                                            <div class="text-slate-500 mt-1 small">
                                                <i class="bi bi-calendar2-event text-primary-subtle me-1.5"></i>Ngày khám: <strong>{{ $record->exam_date ? $record->exam_date->format('d/m/Y') : '---' }}</strong>
                                                @if($record->exam_time)
                                                    <span class="mx-1">•</span>
                                                    Giờ: {{ $record->exam_time }}
                                                @endif
                                                <span class="mx-1.5">•</span>
                                                <i class="bi bi-person-workspace text-primary-subtle me-1.5"></i>Bác sĩ: <strong>BS. {{ $record->doctor_name ?? $record->doctor->full_name ?? '---' }}</strong>
                                            </div>
                                        </div>
                                        <span class="badge bg-light text-slate-600 border border-slate-200 text-xs px-2 py-1.5">{{ $record->visit_type ?? 'Khám thường' }}</span>
                                    </div>

                                    {{-- Chief Complaint --}}
                                    <div class="p-3 bg-white rounded-xl border border-slate-100 mb-3 text-sm">
                                        <div class="text-muted small fw-bold mb-1 text-uppercase text-xs"><i class="bi bi-chat-left-dots text-primary me-1"></i>Lý do thăm khám</div>
                                        <div class="text-slate-800 fw-medium">{{ $record->chief_complaint ?? 'Không ghi nhận lý do đặc biệt' }}</div>
                                    </div>

                                    {{-- Vital Signs Block --}}
                                    @if($record->vitalSigns)
                                        @php
                                            $v = $record->vitalSigns;
                                        @endphp
                                        <div class="p-3 bg-white rounded-xl border border-slate-100 mb-3">
                                            <div class="text-muted small fw-bold mb-2 text-uppercase text-xs"><i class="bi bi-heartpulse text-primary me-1"></i>Chỉ số sinh tồn (Sinh hiệu)</div>
                                            <div class="row g-2 text-slate-700" style="font-size: 12.5px;">
                                                <div class="col-6 col-sm-4 col-md-3">
                                                    <div class="bg-light p-2 rounded text-center">
                                                        <div class="text-muted small fs-10">Huyết áp</div>
                                                        <strong class="{{ $v->getStatusClass('bp') }}">{{ $v->blood_pressure ?? '---' }}</strong> <small class="text-slate-400">mmHg</small>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-sm-4 col-md-3">
                                                    <div class="bg-light p-2 rounded text-center">
                                                        <div class="text-muted small fs-10">Nhịp tim</div>
                                                        <strong class="{{ $v->getStatusClass('hr') }}">{{ $v->heart_rate ?? '---' }}</strong> <small class="text-slate-400">bpm</small>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-sm-4 col-md-3">
                                                    <div class="bg-light p-2 rounded text-center">
                                                        <div class="text-muted small fs-10">Nhiệt độ</div>
                                                        <strong class="{{ $v->getStatusClass('temp') }}">{{ $v->temperature ?? '---' }}</strong> <small class="text-slate-400">°C</small>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-sm-4 col-md-3">
                                                    <div class="bg-light p-2 rounded text-center">
                                                        <div class="text-muted small fs-10">SpO2</div>
                                                        <strong class="{{ $v->getStatusClass('spo2') }}">{{ $v->spo2 ?? '---' }}</strong> <small class="text-slate-400">%</small>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-sm-4 col-md-6">
                                                    <div class="bg-light p-2 rounded text-center">
                                                        <div class="text-muted small fs-10">Đường huyết</div>
                                                        <strong class="{{ $v->getStatusClass('sugar') }}">{{ $v->blood_sugar ?? '---' }}</strong> <small class="text-slate-400">mg/dL</small>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-sm-4 col-md-6">
                                                    <div class="bg-light p-2 rounded text-center">
                                                        <div class="text-muted small fs-10">Cân nặng</div>
                                                        <strong>{{ $v->weight ?? '---' }}</strong> <small class="text-slate-400">kg</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Clinical Diagnosis --}}
                                    @if($record->diagnoses->isNotEmpty())
                                        <div class="p-3 bg-white rounded-xl border border-slate-100 mb-3 text-sm">
                                            <div class="text-muted small fw-bold mb-2 text-uppercase text-xs"><i class="bi bi-journal-medical text-primary me-1"></i>Chẩn đoán lâm sàng</div>
                                            @foreach($record->diagnoses as $diagnosis)
                                                <div class="d-flex align-items-start gap-2 mb-1.5">
                                                    <span class="badge text-white px-2 py-0.5 rounded text-xs" style="background-color: {{ $diagnosis->border_color }}">{{ $diagnosis->icd_code }}</span>
                                                    <div>
                                                        <strong class="text-slate-800">{{ $diagnosis->diagnosis_name }}</strong>
                                                        <span class="text-muted small">({{ $diagnosis->diagnosis_type === 'primary' ? 'Chính' : ($diagnosis->diagnosis_type === 'secondary' ? 'Phụ' : 'Biến chứng') }})</span>
                                                        @if($diagnosis->note)
                                                            <div class="text-slate-500 text-xs mt-0.5">{{ $diagnosis->note }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Diagnostic Orders --}}
                                    @if($record->medicalOrders->isNotEmpty())
                                        <div class="p-3 bg-white rounded-xl border border-slate-100 mb-3 text-sm">
                                            <div class="text-muted small fw-bold mb-2 text-uppercase text-xs"><i class="bi bi-file-earmark-medical text-primary me-1"></i>Chỉ định cận lâm sàng (Xét nghiệm & Hình ảnh)</div>
                                            <div class="row g-2">
                                                @foreach($record->medicalOrders as $order)
                                                    <div class="col-12 col-sm-6">
                                                        <div class="p-2 border border-slate-100 rounded bg-slate-50 d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span class="me-1">{{ $order->icon }}</span>
                                                                <strong class="text-slate-800 small">{{ $order->order_name }}</strong>
                                                            </div>
                                                            <span class="badge {{ $order->hasResult() ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} fs-10">{{ $order->result_status }}</span>
                                                        </div>
                                                        @if($order->result_note)
                                                            <div class="text-slate-500 text-xs italic mt-1 px-2">Kết quả: {{ $order->result_note }}</div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Prescriptions --}}
                                    @if($record->prescriptions->isNotEmpty())
                                        <div class="p-3 bg-white rounded-xl border border-slate-100 mb-3 text-sm">
                                            <div class="text-muted small fw-bold mb-2 text-uppercase text-xs"><i class="bi bi-capsule text-primary me-1"></i>Đơn thuốc & Phác đồ điều trị</div>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12.5px;">
                                                    <thead>
                                                        <tr class="text-muted">
                                                            <th>Tên thuốc</th>
                                                            <th>Liều dùng</th>
                                                            <th>Số lượng</th>
                                                            <th>Cách dùng</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($record->prescriptions as $rx)
                                                            <tr>
                                                                <td class="fw-bold text-slate-800">{{ $rx->drug_name }}</td>
                                                                <td>{{ $rx->dosage }}</td>
                                                                <td><span class="badge bg-slate-100 text-slate-700">{{ $rx->quantity }} {{ $rx->unit }}</span></td>
                                                                <td class="text-slate-600">{{ $rx->instructions }} <span class="text-muted">({{ $rx->duration_days }} ngày)</span></td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Clinical Attachments --}}
                                    @if($record->attachments->isNotEmpty())
                                        <div class="p-3 bg-white rounded-xl border border-slate-100 text-sm">
                                            <div class="text-muted small fw-bold mb-2 text-uppercase text-xs"><i class="bi bi-paperclip text-primary me-1"></i>Tập tin đính kèm (Phim chụp / Báo cáo)</div>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($record->attachments as $file)
                                                    <a href="{{ asset($file->file_path) }}" target="_blank" class="btn btn-sm btn-light border border-slate-200 d-inline-flex align-items-center gap-1.5 text-xs text-slate-700 p-2 rounded-xl">
                                                        @if($file->isPdf())
                                                            <i class="bi bi-file-earmark-pdf-fill text-danger fs-6"></i>
                                                        @elseif($file->isImage())
                                                            <i class="bi bi-file-earmark-image-fill text-info fs-6"></i>
                                                        @else
                                                            <i class="bi bi-file-earmark-medical-fill text-primary fs-6"></i>
                                                        @endif
                                                        <span class="text-truncate" style="max-width: 130px;">{{ $file->file_name }}</span>
                                                        <small class="text-slate-400">({{ $file->file_size_formatted }})</small>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle shadow-sm mb-3" style="width: 70px; height: 70px;">
                            <i class="bi bi-folder-x text-muted fs-3"></i>
                        </div>
                        <h6 class="fw-bold text-slate-800">Không tìm thấy hồ sơ khám bệnh</h6>
                        <p class="text-muted text-sm mx-auto mb-0" style="max-width: 320px;">Bệnh nhân này chưa có hồ sơ bệnh án hoặc các lượt thăm khám trên hệ thống.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .fs-11 { font-size: 11px !important; }
    .fs-12 { font-size: 12px !important; }
    .fs-10 { font-size: 10px !important; }
    
    .text-slate-800 { color: #1e293b !important; }
    .text-slate-700 { color: #334155 !important; }
    .text-slate-600 { color: #475569 !important; }
    .text-slate-500 { color: #64748b !important; }
    
    .bg-rose-50\/60 { background-color: rgba(255, 241, 242, 0.6) !important; }
    .bg-amber-50\/60 { background-color: rgba(255, 251, 235, 0.6) !important; }
    .bg-slate-50 { background-color: #f8fafc !important; }
    .bg-slate-50\/50 { background-color: rgba(248, 250, 252, 0.5) !important; }
    .bg-primary-subtle { background-color: #dbeafe !important; }
    
    .border-slate-100 { border-color: #f1f5f9 !important; }
    .border-slate-50 { border-color: #f8fafc !important; }
    .border-rose-100 { border-color: #ffe4e6 !important; }
    .border-amber-100 { border-color: #fef3c7 !important; }
    .rounded-xl { border-radius: 12px !important; }
    .rounded-2xl { border-radius: 16px !important; }
    
    /* Clinical timeline custom hover effects */
    .timeline-item {
        transition: transform 0.2s ease;
    }
    .timeline-item:hover {
        transform: translateX(4px);
    }
</style>
