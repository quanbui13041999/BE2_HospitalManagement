{{-- resources/views/nutrition/patient/index.blade.php --}}
{{-- Dashboard Dinh dưỡng Bệnh nhân: 4 chức năng trong 1 trang --}}

@extends('layouts.nutrition')

@section('title', 'Dashboard Dinh dưỡng')

@section('content')

{{-- ── HEADER ───────────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-success">
            <i class="bi bi-journal-medical me-2"></i>Chế độ dinh dưỡng của bạn
        </h2>
        <p class="text-muted mb-0">Xin chào, <strong>{{ $user->full_name }}</strong> – {{ now()->format('d/m/Y') }}</p>
    </div>
    @if($latestDiagnoses->isNotEmpty())
    <div class="text-end">
        <small class="text-muted">Chẩn đoán gần nhất:</small><br>
        @foreach($latestDiagnoses as $d)
        <span class="badge bg-warning text-dark me-1">{{ $d->diagnosis_name }}</span>
        @endforeach
    </div>
    @else
    <span class="badge bg-secondary">Chưa có chẩn đoán</span>
    @endif
</div>

<div class="row g-4">

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- CHỨC NĂNG 1 & 4: Cột trái – Gợi ý thực đơn + Bài viết   --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <div class="col-lg-5">

        {{-- ── CARD 1: Gợi ý thực đơn theo bệnh ──────────────── --}}
        <div class="card mb-4">
            <div class="card-header bg-white border-0 pb-0 pt-3 px-4">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-lightbulb text-warning me-2"></i>Gợi ý thực đơn cho bạn
                </h5>
                <p class="text-muted small mt-1">Dựa trên chẩn đoán bệnh gần nhất</p>
            </div>
            <div class="card-body px-4 pb-4">

                @if($latestDiagnoses->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="bi bi-clipboard2-x fs-2 d-block mb-2"></i>
                    Chưa có dữ liệu chẩn đoán để gợi ý thực đơn.
                </div>
                @else
                {{-- Nên ăn --}}
                @if($shouldEatFoods->isNotEmpty())
                <p class="fw-semibold text-success mb-2">
                    <i class="bi bi-check-circle-fill me-1"></i>Thực phẩm NÊN DÙNG
                </p>
                @foreach($shouldEatFoods as $rule)
                <div class="d-flex align-items-start mb-2 p-2 rounded" style="background:var(--primary-light)">
                    <i class="bi bi-leaf text-success me-2 mt-1"></i>
                    <div>
                        <strong>{{ $rule->food?->food_name ?? 'Thực phẩm không tồn tại' }}</strong>
                        <span class="text-muted ms-2 small">
                            {{ $rule->food?->calories_per_100g ?? 0 }} kcal/100g
                        </span>
                        @if($rule->reason)
                        <p class="mb-0 small text-muted">{{ $rule->reason }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
                @endif

                {{-- Nên tránh --}}
                @if($shouldAvoidFoods->isNotEmpty())
                <p class="fw-semibold text-danger mb-2 mt-3">
                    <i class="bi bi-x-circle-fill me-1"></i>Thực phẩm NÊN TRÁNH
                </p>
                @foreach($shouldAvoidFoods as $rule)
                <div class="d-flex align-items-start mb-2 p-2 rounded" style="background:var(--danger-light)">
                    <i class="bi bi-exclamation-triangle text-danger me-2 mt-1"></i>
                    <div>
                        <strong>{{ $rule->food?->food_name ?? 'Thực phẩm không tồn tại' }}</strong>
                        <span class="text-muted ms-2 small">
                            {{ $rule->food?->calories_per_100g ?? 0 }} kcal/100g
                        </span>
                        @if($rule->reason)
                        <p class="mb-0 small text-muted">{{ $rule->reason }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
                @endif

                @if($shouldEatFoods->isEmpty() && $shouldAvoidFoods->isEmpty())
                <p class="text-muted text-center py-2">Chưa có quy tắc dinh dưỡng cho bệnh này.</p>
                @endif
                @endif
            </div>
        </div>

        {{-- ── CARD 4: Lời khuyên chuyên gia ──────────────────── --}}
        <div class="card">
            <div class="card-header bg-white border-0 pb-0 pt-3 px-4">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-newspaper text-primary me-2"></i>Lời khuyên từ chuyên gia
                </h5>
            </div>
            <div class="card-body px-4 pb-4">
                @forelse($expertArticles as $article)
                <div class="border-bottom pb-3 mb-3">
                    <h6 class="fw-semibold mb-1">{{ $article->title }}</h6>
                    <p class="small text-muted mb-1">{{ $article->excerpt }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-person me-1"></i>
                            {{ $article->doctor?->full_name ?? 'Admin' }}
                        </small>
                        <span class="badge bg-info text-dark">{{ $article->target_disease }}</span>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3">Chưa có bài viết nào.</p>
                @endforelse
            </div>
        </div>

    </div>{{-- end col-lg-5 --}}

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- CHỨC NĂNG 2 & 3: Cột phải – Nhật ký ăn uống & Calo       --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <div class="col-lg-7">

        {{-- ── CARD: Tổng Calo hôm nay (Chức năng 3) ─────────── --}}
        <div class="card mb-4">
            <div class="card-body px-4 py-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-fire text-danger me-2"></i>Lượng Calo hôm nay
                    </h5>
                    <span class="fs-4 fw-bold text-{{ $caloriePercent >= 100 ? 'danger' : 'success' }}">
                        {{ number_format($totalCaloriesToday) }}
                        <small class="fs-6 text-muted fw-normal">/ {{ number_format($calorieGoal) }} kcal</small>
                    </span>
                </div>

                {{-- Progress bar --}}
                <div class="progress mb-2">
                    <div class="progress-bar bg-{{ $caloriePercent >= 100 ? 'danger' : ($caloriePercent >= 75 ? 'warning' : 'success') }} progress-bar-striped"
                        role="progressbar"
                        style="width: {{ $caloriePercent }}%"
                        aria-valuenow="{{ $caloriePercent }}"
                        aria-valuemin="0" aria-valuemax="100">
                        {{ $caloriePercent }}%
                    </div>
                </div>

                {{-- Calo theo buổi --}}
                <div class="row g-2 mt-2">
                    @foreach(['breakfast' => ['Sáng','bi-sunrise'], 'lunch' => ['Trưa','bi-sun'], 'dinner' => ['Tối','bi-moon-stars'], 'snack' => ['Phụ','bi-cup-hot']] as $type => [$label, $icon])
                    <div class="col-3 text-center">
                        <div class="p-2 rounded" style="background:#f8f9fa">
                            <i class="bi {{ $icon }} text-muted"></i>
                            <div class="small fw-semibold">{{ $label }}</div>
                            <div class="small text-muted">{{ $calorieByMeal[$type] ?? 0 }} kcal</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── CARD: Thêm bữa ăn (Chức năng 2 - Form) ────────── --}}
        <div class="card mb-4">
            <div class="card-header bg-white border-0 pb-0 pt-3 px-4">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-plus-circle text-success me-2"></i>Ghi lại bữa ăn
                </h5>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="{{ route('patient.nutrition.meal-log.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Món ăn</label>
                            <select name="food_id" class="form-select @error('food_id') is-invalid @enderror" required>
                                <option value="">-- Chọn món ăn --</option>
                                @foreach($allFoods as $food)
                                <option value="{{ $food->food_id }}"
                                    data-calo="{{ $food->calories_per_100g }}"
                                    {{ old('food_id') == $food->food_id ? 'selected' : '' }}>
                                    {{ $food->food_name }} ({{ $food->calories_per_100g }} kcal/100g)
                                </option>
                                @endforeach
                            </select>
                            @error('food_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Buổi ăn</label>
                            <select name="meal_type" class="form-select @error('meal_type') is-invalid @enderror" required>
                                <option value="breakfast" {{ old('meal_type') == 'breakfast' ? 'selected' : '' }}>Bữa sáng</option>
                                <option value="lunch" {{ old('meal_type') == 'lunch'     ? 'selected' : '' }}>Bữa trưa</option>
                                <option value="dinner" {{ old('meal_type') == 'dinner'    ? 'selected' : '' }}>Bữa tối</option>
                                <option value="snack" {{ old('meal_type') == 'snack'     ? 'selected' : '' }}>Bữa phụ</option>
                            </select>
                            @error('meal_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Gram</label>
                            <input type="number" name="weight_gram" id="weight_gram"
                                class="form-control @error('weight_gram') is-invalid @enderror"
                                min="1" max="5000" placeholder="150"
                                value="{{ old('weight_gram') }}" required>
                            @error('weight_gram')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <div class="w-100">
                                <div id="calo-preview" class="text-center text-muted small mb-1">≈ - kcal</div>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-plus"></i> Thêm
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── CARD: Nhật ký ăn uống hôm nay (Chức năng 2 - List) --}}
        <div class="card">
            <div class="card-header bg-white border-0 pb-0 pt-3 px-4">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-calendar-check me-2 text-info"></i>Nhật ký hôm nay
                </h5>
            </div>
            <div class="card-body px-4 pb-4">
                @forelse($todayLogs as $log)
                <div class="d-flex justify-content-between align-items-center meal-card p-3 rounded mb-2">
                    <div>
                        <strong>{{ $log->food?->food_name ?? 'Thực phẩm không tồn tại' }}</strong>
                        <span class="badge bg-light text-dark ms-2">{{ $log->meal_label }}</span>
                        <p class="mb-0 small text-muted">{{ $log->weight_gram }}g</p>
                    </div>
                    <div class="text-end">
                        <span class="fw-bold text-success">{{ $log->total_calories_intake }} kcal</span>
                        <form action="{{ route('patient.nutrition.meal-log.destroy', $log->log_id) }}"
                            method="POST" class="d-inline ms-2"
                            onsubmit="return confirm('Xóa bản ghi này?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                    Chưa có bữa ăn nào hôm nay. Hãy ghi lại bữa ăn đầu tiên!
                </div>
                @endforelse
            </div>
        </div>

    </div>{{-- end col-lg-7 --}}
</div>

@endsection

@push('scripts')
<script>
    // Tính calo preview realtime khi chọn món ăn hoặc nhập gram
    const foodSelect = document.querySelector('select[name="food_id"]');
    const weightInput = document.getElementById('weight_gram');
    const preview = document.getElementById('calo-preview');

    function updatePreview() {
        const option = foodSelect.options[foodSelect.selectedIndex];
        const calo100 = parseFloat(option?.dataset?.calo || 0);
        const gram = parseFloat(weightInput.value || 0);
        if (calo100 && gram) {
            const total = Math.round(calo100 * gram / 100);
            preview.textContent = `≈ ${total} kcal`;
            preview.className = 'text-center fw-semibold text-success small mb-1';
        } else {
            preview.textContent = '≈ - kcal';
            preview.className = 'text-center text-muted small mb-1';
        }
    }

    foodSelect.addEventListener('change', updatePreview);
    weightInput.addEventListener('input', updatePreview);
</script>
@endpush
