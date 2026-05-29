@extends('layouts.nutrition')

@section('title', 'Chỉnh sửa thực phẩm')

@section('content')

<div class="mb-4">
    <a href="{{ route('admin.nutrition.foods.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
    </a>
    <h2 class="fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Chỉnh sửa thực phẩm</h2>
    <p class="text-muted">Cập nhật thông tin thực phẩm hoặc lượng calorie tương ứng</p>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-body p-4">
        <form action="{{ route('admin.nutrition.foods.update', $food->food_id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                {{-- Food Name --}}
                <div class="col-md-8">
                    <label for="food_name" class="form-label fw-semibold">Tên thực phẩm <span class="text-danger">*</span></label>
                    <input type="text" name="food_name" id="food_name" 
                           class="form-control @error('food_name') is-invalid @enderror" 
                           placeholder="Ví dụ: Bún chả, Táo Mỹ, Sữa hạt..." 
                           value="{{ old('food_name', $food->food_name) }}" required>
                    @error('food_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Calories per 100g --}}
                <div class="col-md-4">
                    <label for="calories_per_100g" class="form-label fw-semibold">Calo / 100g <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="calories_per_100g" id="calories_per_100g" 
                               class="form-control @error('calories_per_100g') is-invalid @enderror" 
                               placeholder="130" min="0" max="5000"
                               value="{{ old('calories_per_100g', $food->calories_per_100g) }}" required>
                        <span class="input-group-text">kcal</span>
                    </div>
                    @error('calories_per_100g')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="col-12">
                    <label for="description" class="form-label fw-semibold">Mô tả chi tiết</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                              rows="3" placeholder="Nhập mô tả về thành phần dinh dưỡng chính hoặc lưu ý chế biến..."
                              >{{ old('description', $food->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="col-md-6">
                    <label for="status" class="form-label fw-semibold">Trạng thái hiển thị</label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="1" {{ old('status', $food->status) == '1' ? 'selected' : '' }}>Kích hoạt (Hiển thị cho bệnh nhân)</option>
                        <option value="0" {{ old('status', $food->status) == '0' ? 'selected' : '' }}>Ẩn (Không cho phép chọn)</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary px-4 me-2">
                    <i class="bi bi-check-circle me-1"></i> Cập nhật thực phẩm
                </button>
                <a href="{{ route('admin.nutrition.foods.index') }}" class="btn btn-light px-4">Hủy bỏ</a>
            </div>
        </form>
    </div>
</div>

@endsection
