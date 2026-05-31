@extends('layouts.nutrition')

@section('title', 'Chỉnh sửa quy tắc dinh dưỡng')

@section('content')

<div class="mb-4">
    <a href="{{ route('admin.nutrition.rules.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
    </a>
    <h2 class="fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Chỉnh sửa quy tắc dinh dưỡng</h2>
    <p class="text-muted">Cập nhật quy tắc ăn uống cho bệnh nhân</p>
</div>

<div class="card" style="max-width: 800px;">
    <div class="card-body p-4">
        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif

        <form action="{{ route('admin.nutrition.rules.update', $rule->rule_id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="rule_snapshot" value="{{ $ruleSnapshot }}">

            <div class="row g-3">
                {{-- Disease Name --}}
                <div class="col-md-8">
                    <label for="disease_name" class="form-label fw-semibold">Tên bệnh lý <span class="text-danger">*</span></label>
                    <input type="text" name="disease_name" id="disease_name" 
                           class="form-control @error('disease_name') is-invalid @enderror" 
                           placeholder="Ví dụ: Đái tháo đường" 
                           value="{{ old('disease_name', $rule->disease_name) }}" required minlength="3" maxlength="120" pattern="^[A-Za-zÀ-ỹ\s]+$">
                    <small class="text-muted">Nên nhập chính xác cụm từ chẩn đoán y khoa.</small>
                    @error('disease_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ICD Code --}}
                <div class="col-md-4">
                    <label for="icd_code" class="form-label fw-semibold">Mã ICD (Nếu có)</label>
                    <input type="text" name="icd_code" id="icd_code" 
                           class="form-control @error('icd_code') is-invalid @enderror" 
                           placeholder="Ví dụ: E11, I10" 
                           value="{{ old('icd_code', $rule->icd_code) }}" maxlength="10" pattern="^[A-Za-z][0-9]{1,2}(\\.[0-9A-Za-z]{1,2})?$">
                    @error('icd_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Food --}}
                <div class="col-md-6">
                    <label for="food_id" class="form-label fw-semibold">Thực phẩm liên quan <span class="text-danger">*</span></label>
                    <select name="food_id" id="food_id" class="form-select @error('food_id') is-invalid @enderror" required>
                        <option value="">-- Chọn thực phẩm --</option>
                        @foreach($foods as $food)
                            <option value="{{ $food->food_id }}" {{ old('food_id', $rule->food_id) == $food->food_id ? 'selected' : '' }}>
                                {{ $food->food_name }} ({{ $food->calories_per_100g }} kcal/100g)
                            </option>
                        @endforeach
                    </select>
                    @error('food_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Recommendation Type --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Loại gợi ý <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3 mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="recommendation_type" id="type_eat" 
                                   value="should_eat" {{ old('recommendation_type', $rule->recommendation_type) == 'should_eat' ? 'checked' : '' }}>
                            <label class="form-check-label text-success fw-semibold" for="type_eat">
                                <i class="bi bi-check-circle-fill me-1"></i>Nên dùng
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="recommendation_type" id="type_avoid" 
                                   value="should_avoid" {{ old('recommendation_type', $rule->recommendation_type) == 'should_avoid' ? 'checked' : '' }}>
                            <label class="form-check-label text-danger fw-semibold" for="type_avoid">
                                <i class="bi bi-x-circle-fill me-1"></i>Nên tránh
                            </label>
                        </div>
                    </div>
                    @error('recommendation_type')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Reason / Explanation --}}
                <div class="col-12">
                    <label for="reason" class="form-label fw-semibold">Lý do khuyến nghị / Giải thích</label>
                    <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" 
                              rows="3" maxlength="500" placeholder="Giải thích lý do chuyên khoa"
                              >{{ old('reason', $rule->reason) }}</textarea>
                    @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary px-4 me-2">
                    <i class="bi bi-check-circle me-1"></i> Cập nhật quy tắc
                </button>
                <a href="{{ route('admin.nutrition.rules.index') }}" class="btn btn-light px-4">Hủy bỏ</a>
            </div>
        </form>
    </div>
</div>

@endsection
