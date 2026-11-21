@extends('layouts.nutrition')

@section('title', 'Quản lý danh mục thực phẩm')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-egg-fried text-primary me-2"></i>Danh mục thực phẩm</h2>
        <p class="text-muted mb-0">Quản lý cơ sở dữ liệu các món ăn, nguyên liệu và lượng calorie của chúng</p>
    </div>
    <a href="{{ route('admin.nutrition.foods.create') }}" class="btn btn-success">
        <i class="bi bi-plus-circle me-1"></i> Thêm thực phẩm mới
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('warning'))
    <div class="alert alert-warning">{{ session('warning') }}</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">#</th>
                    <th>Tên thực phẩm</th>
                    <th>Calo (kcal/100g)</th>
                    <th>Mô tả</th>
                    <th>Trạng thái hiển thị</th>
                    <th class="text-end pe-4">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($foods as $food)
                    <tr>
                        <td class="ps-4 text-muted">{{ $food->food_id }}</td>
                        <td><strong>{{ $food->food_name }}</strong></td>
                        <td><span class="badge bg-danger px-2.5 py-1.5 fs-7 rounded-pill">{{ $food->calories_per_100g }} kcal</span></td>
                        <td>
                            <div class="text-muted small" style="max-width: 350px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $food->description }}">
                                {{ $food->description ?? '—' }}
                            </div>
                        </td>
                        <td>
                            @if($food->status === 1)
                                <span class="badge bg-success px-2 py-1 fs-8">Kích hoạt</span>
                            @else
                                <span class="badge bg-secondary px-2 py-1 fs-8">Ẩn</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.nutrition.foods.edit', $food->food_id) }}"
                               class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.nutrition.foods.destroy', $food->food_id) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Bạn có chắc muốn xóa thực phẩm này không? Nhật ký ăn uống và quy tắc liên quan có thể bị ảnh hưởng.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Chưa có thực phẩm nào trong danh mục. <a href="{{ route('admin.nutrition.foods.create') }}">Thêm thực phẩm đầu tiên</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($foods->hasPages())
        <div class="card-footer bg-white border-0 d-flex justify-content-end">
            {{ $foods->links() }}
        </div>
    @endif
</div>

@endsection
