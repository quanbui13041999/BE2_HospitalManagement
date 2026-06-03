<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Tiền Sử & Dị Ứng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/tiensu.css') }}">
</head>

<body>
    <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning border-0 shadow-sm mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('warning') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-3">
                <i class="bi bi-x-circle-fill me-2"></i> Dữ liệu nhập chưa hợp lệ. Vui lòng kiểm tra lại các ô được báo lỗi.
            </div>
        @endif

        <div class="alert alert-warning border-0 shadow-sm mb-4" style="background-color: #fff8ec;">
            <div class="d-flex">
                <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>
                <div>
                    <strong class="text-dark">Thông tin cảnh báo dị ứng - hiển thị nổi bật cho bác sĩ</strong>
                    <p class="mb-0 text-muted small">Vui lòng khai báo đầy đủ và chính xác.</p>
                    @if($patient ?? null)
                        <p class="mb-0 text-muted small">Đang xem hồ sơ của: <strong>{{ $patient->full_name }}</strong></p>
                    @endif
                </div>
            </div>
        </div>

        <form action="{{ route('health.store') }}" method="POST" novalidate>
            @csrf
            <input type="hidden" name="health_background_id" value="{{ old('health_background_id', $healthData->id ?? '') }}">
            <input type="hidden" name="health_background_updated_at" value="{{ old('health_background_updated_at', optional($healthData?->updated_at)->toDateTimeString()) }}">

            <fieldset @disabled($readonly ?? false)>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <h6 class="card-title fw-bold text-primary border-start border-4 border-primary ps-2 mb-3">
                                    <i class="bi bi-droplet-fill text-danger"></i> NHÓM MÁU & THÔNG TIN CƠ BẢN
                                </h6>

                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small text-muted">NHÓM MÁU</label>
                                        <select name="nhommau" class="form-select border-light-subtle @error('nhommau') is-invalid @enderror">
                                            <option value="">Chưa chọn</option>
                                            @foreach($bloodGroups as $type)
                                                <option value="{{ $type }}" @selected(old('nhommau', $healthData->blood_group ?? '') === $type)>
                                                    {{ $type }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('nhommau')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label small text-muted">YẾU TỐ RH</label>
                                        <select name="yeuto_rh" class="form-select border-light-subtle @error('yeuto_rh') is-invalid @enderror">
                                            <option value="">Chưa chọn</option>
                                            <option value="positive" @selected(old('yeuto_rh', $healthData->yeuto_rh ?? '') === 'positive')>Dương tính (+)</option>
                                            <option value="negative" @selected(old('yeuto_rh', $healthData->yeuto_rh ?? '') === 'negative')>Âm tính (-)</option>
                                        </select>
                                        @error('yeuto_rh')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="form-label small text-muted">Chiều cao (cm)</label>
                                        <input type="number" name="height" class="form-control @error('height') is-invalid @enderror"
                                               value="{{ old('height', $healthData->height ?? '') }}"
                                               min="30" max="300" step="0.01" inputmode="decimal"
                                               placeholder="Nhập chiều cao">
                                        @error('height')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label small text-muted">Cân nặng (kg)</label>
                                        <input type="number" name="weight" class="form-control @error('weight') is-invalid @enderror"
                                               value="{{ old('weight', $healthData->weight ?? '') }}"
                                               min="1" max="500" step="0.01" inputmode="decimal"
                                               placeholder="Nhập cân nặng">
                                        @error('weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="mt-3 p-3 rounded bg-light border border-info-subtle">
                                    <span class="fs-4 fw-bold text-primary">{{ $healthData->blood_group ?? 'Chưa có nhóm máu' }}</span>
                                    <span class="ms-2">BMI HIỆN TẠI: <strong id="bmi-value">{{ $healthData->bmi ?? 0 }}</strong><span id="bmi-status"></span></span>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title fw-bold text-warning border-start border-4 border-warning ps-2 mb-3">
                                    <i class="bi bi-leaf-fill"></i> DỊ ỨNG THỰC PHẨM
                                </h6>

                                <div class="mb-3">
                                    <label class="form-label small text-muted">THỰC PHẨM</label>
                                    <input type="text" name="food_allergies" class="form-control js-vietnamese-words @error('food_allergies') is-invalid @enderror"
                                           value="{{ old('food_allergies', $healthData->food_allergies ?? '') }}"
                                           maxlength="100" pattern="[\p{L}\p{M}]+( [\p{L}\p{M}]+)*"
                                           title="Chỉ nhập chữ tiếng Việt và đúng một khoảng trắng giữa các từ, không nhập số hoặc ký tự lạ."
                                           placeholder="VD: Sữa Gluten">
                                    @error('food_allergies')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <a href="{{ route('profile.show') }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-house-door-fill me-1"></i> Quay lại
                            </a>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-4 border-top border-danger border-4">
                            <div class="card-body">
                                <h6 class="card-title fw-bold text-danger ps-2 mb-3">
                                    <i class="bi bi-capsule"></i> DỊ ỨNG THUỐC
                                </h6>
                                <input type="text" name="drug_allergies" class="form-control js-vietnamese-words @error('drug_allergies') is-invalid @enderror"
                                       value="{{ old('drug_allergies', $healthData->drug_allergies ?? '') }}"
                                       maxlength="255" pattern="[\p{L}\p{M}]+( [\p{L}\p{M}]+)*" title="Chỉ nhập chữ tiếng Việt và đúng một khoảng trắng giữa các từ, không nhập số hoặc ký tự lạ." placeholder="Nhập tên thuốc">
                                @error('drug_allergies')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title fw-bold text-info border-start border-4 border-info ps-2 mb-3">
                                    <i class="bi bi-flower1"></i> BỆNH MÃN TÍNH
                                </h6>

                                @php
                                    $selectedDiseases = old('chronic_diseases', $healthData->chronic_diseases ?? []);
                                    $diseaseColumns = array_chunk($chronicDiseaseOptions, 6);
                                @endphp

                                <div class="row g-2">
                                    @foreach($diseaseColumns as $column)
                                        <div class="col-6">
                                            @foreach($column as $disease)
                                                @php $diseaseId = 'chronic_' . md5($disease); @endphp
                                                <div class="form-check p-2 border rounded border-light-subtle mb-2">
                                                    <input class="form-check-input ms-1" name="chronic_diseases[]" type="checkbox"
                                                           value="{{ $disease }}" id="{{ $diseaseId }}"
                                                           @checked(in_array($disease, $selectedDiseases, true))>
                                                    <label class="form-check-label ms-2" for="{{ $diseaseId }}">{{ $disease }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                                @error('chronic_diseases')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                @error('chronic_diseases.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

                                <div class="mt-3 mb-3">
                                    <label class="form-label small text-muted">BỆNH MÃN TÍNH KHÁC</label>
                                    <textarea name="other_chronic_diseases" class="form-control bg-light js-vietnamese-words @error('other_chronic_diseases') is-invalid @enderror"
                                              rows="3" maxlength="500" data-vietnamese-words="true" title="Chỉ nhập chữ tiếng Việt và đúng một khoảng trắng giữa các từ, không nhập số hoặc ký tự lạ.">{{ old('other_chronic_diseases', $healthData->other_chronic_diseases ?? '') }}</textarea>
                                    @error('other_chronic_diseases')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                @unless($readonly ?? false)
                                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Lưu thông tin</button>
                                @endunless
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>

    <script src="{{ asset('js/tiensu.js') }}"></script>
</body>

</html>
