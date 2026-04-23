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

        <div class="alert alert-warning border-0 shadow-sm mb-4" style="background-color: #fff8ec;">
            <div class="d-flex">
                <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>
                <div>
                    <strong class="text-dark">Thông tin cảnh báo dị ứng — Hiển thị nổi bật cho bác sĩ</strong>
                    <p class="mb-0 text-muted small">Vui lòng khai báo đầy đủ và chính xác.</p>
                </div>
            </div>
        </div>

        <form action="{{ route('tiensu.store') }}" method="POST">
            @csrf
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
                                    <select name="nhommau" class="form-select border-light-subtle">
                                        @foreach(['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'] as $type)
                                        <option value="{{ $type }}" {{ (old('nhommau', $tiensu->blood_group ?? '') == $type) ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-muted">YẾU TỐ RH</label>
                                    <select name="yeuto_rh" class="form-select border-light-subtle">
                                        <option value="positive" {{ (old('yeuto_rh', $tiensu->yeuto_rh ?? '') == 'positive') ? 'selected' : '' }}>Dương tính (+)</option>
                                        <option value="negative" {{ (old('yeuto_rh', $tiensu->yeuto_rh ?? '') == 'negative') ? 'selected' : '' }}>Âm tính (-)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label small text-muted">Chiều cao (cm)</label>
                                    <input type="number" name="height" class="form-control"
                                        value="{{ old('height', $tiensu->height ?? '') }}" placeholder="Nhập chiều cao..">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-muted">Cân nặng (kg)</label>
                                    <input type="number" name="weight" class="form-control"
                                        value="{{ old('weight', $tiensu->weight ?? '') }}" placeholder="Nhập cân nặng..">
                                </div>
                            </div>
                            <div class="mt-3 p-3 rounded bg-light border border-info-subtle">
                                <span class="fs-4 fw-bold text-primary">{{ $tiensu->blood_group ?? 'O+' }}</span>
                                <span class="ms-2">BMI HIỆN TẠI: <strong id="bmi-value">{{ $tiensu->bmi ?? 0 }}</strong></span>
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
                                <input type="text" name="food_allergies" class="form-control"
                                    value="{{ old('food_allergies', $tiensu->food_allergies ?? '') }}" placeholder="VD: Sữa, Gluten...">
                            </div>
                        </div>
                        <a href="{{ url('/home') }}"
                            class="d-inline-block text-decoration-none px-4 py-2 rounded shadow-sm border"
                            style="background-color: #edffec; color: #28a745; font-weight: 600; transition: all 0.3s ease; border-color: #d4edda !important;">
                            <i class="bi bi-house-door-fill me-1">Trang chủ</i> 
                        </a>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm mb-4 border-top border-danger border-4">
                        <div class="card-body">
                            <h6 class="card-title fw-bold text-danger ps-2 mb-3">
                                <i class="bi bi-capsule"></i> DỊ ỨNG THUỐC
                            </h6>
                            <div class="input-group">
                                <input type="text" name="drug_allergies" class="form-control"
                                    value="{{ old('drug_allergies', $tiensu->drug_allergies ?? '') }}" placeholder="Nhập tên thuốc...">
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title fw-bold text-info border-start border-4 border-info ps-2 mb-3">
                                <i class="bi bi-flower1"></i> BỆNH MÃN TÍNH
                            </h6>
                            @php
                            $selectedDiseases = old('chronic_diseases', $tiensu->chronic_diseases ?? []);
                            @endphp
                            <div class="row g-2">
                                <div class="col-12">
                                    <div class="form-check p-2 border rounded border-light-subtle mb-2">
                                        <input class="form-check-input ms-1" name="chronic_diseases[]" type="checkbox" value="TĂNG HUYẾT ÁP" id="check1"
                                            {{ in_array('TĂNG HUYẾT ÁP', $selectedDiseases) ? 'checked' : '' }}>
                                        <label class="form-check-label ms-2" for="check1">TĂNG HUYẾT ÁP</label>
                                    </div>
                                    <div class="form-check p-2 border rounded border-light-subtle">
                                        <input class="form-check-input ms-1" name="chronic_diseases[]" type="checkbox" value="TUỘT HUYẾT ÁP" id="check2"
                                            {{ in_array('TUỘT HUYẾT ÁP', $selectedDiseases) ? 'checked' : '' }}>
                                        <label class="form-check-label ms-2" for="check2">TUỘT HUYẾT ÁP</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 mb-3">
                                <label class="form-label small text-muted">BỆNH MÃN TÍNH KHÁC</label>
                                <textarea name="other_chronic_diseases" class="form-control bg-light" rows="3">{{ old('other_chronic_diseases', $tiensu->other_chronic_diseases ?? '') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Lưu thông tin</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="{{ asset('js/tiensu.js') }}"></script>
</body>

</html>