@extends('layouts.nutrition')

@section('title', 'Thêm thực phẩm mới')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.nutrition.foods.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
    </a>
    <h2 class="fw-bold"><i class="bi bi-plus-circle text-success me-2"></i>Thêm thực phẩm mới</h2>
    <p class="text-muted">Bổ sung món ăn hoặc nguyên liệu mới vào cơ sở dữ liệu dinh dưỡng</p>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-body p-4">
        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif

        <form action="{{ route('admin.nutrition.foods.store') }}" method="POST" data-food-form novalidate>
            @csrf

            <div class="row g-3">
                <div class="col-md-8">
                    <label for="food_name" class="form-label fw-semibold">Tên thực phẩm <span class="text-danger">*</span></label>
                    <input type="text"
                           name="food_name"
                           id="food_name"
                           class="form-control @error('food_name') is-invalid @enderror"
                           placeholder="Ví dụ: Bún chả"
                           value="{{ old('food_name') }}"
                           required
                           minlength="2"
                           maxlength="80"
                           pattern="^[A-Za-zÀ-ỹ]+( [A-Za-zÀ-ỹ]+)*$"
                           data-error-required="Vui lòng nhập tên thực phẩm."
                           data-error-pattern="Tên thực phẩm chỉ được nhập chữ tiếng Việt và một khoảng trắng giữa các từ, không nhập số hoặc ký tự lạ."
                           data-error-minlength="Tên thực phẩm phải có ít nhất 2 ký tự.">
                    @error('food_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label for="calories_per_100g" class="form-label fw-semibold">Calo / 100g <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number"
                               name="calories_per_100g"
                               id="calories_per_100g"
                               class="form-control @error('calories_per_100g') is-invalid @enderror"
                               placeholder="130"
                               min="0"
                               max="5000"
                               step="1"
                               inputmode="numeric"
                               value="{{ old('calories_per_100g') }}"
                               required
                               data-error-required="Vui lòng nhập calo trên 100g."
                               data-error-input="Calo chỉ được nhập số nguyên."
                               data-error-min="Calo không được nhỏ hơn 0."
                               data-error-max="Calo tối đa là 5000 kcal.">
                        <span class="input-group-text">kcal</span>
                    </div>
                    @error('calories_per_100g')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label for="description" class="form-label fw-semibold">Mô tả chi tiết</label>
                    <textarea name="description"
                              id="description"
                              class="form-control @error('description') is-invalid @enderror"
                              rows="3"
                              maxlength="300"
                              placeholder="Nhập mô tả về thành phần dinh dưỡng chính"
                              data-pattern="^[A-Za-zÀ-ỹ]+( [A-Za-zÀ-ỹ]+)*$"
                              data-error-pattern="Mô tả chỉ được nhập chữ tiếng Việt và một khoảng trắng giữa các từ, không nhập số hoặc ký tự lạ.">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label fw-semibold">Trạng thái hiển thị</label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Kích hoạt (Hiển thị cho bệnh nhân)</option>
                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Ẩn (Không cho phép chọn)</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-success px-4 me-2">
                    <i class="bi bi-check-circle me-1"></i> Thêm thực phẩm
                </button>
                <a href="{{ route('admin.nutrition.foods.index') }}" class="btn btn-light px-4">Hủy bỏ</a>
            </div>
        </form>
    </div>
</div>
@endsection

@include('nutrition.admin.foods._form_validation')
