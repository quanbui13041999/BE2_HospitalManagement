@extends('layouts.nutrition')

@section('title', 'Quản lý quy tắc dinh dưỡng')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-lightbulb text-primary me-2"></i>Quy tắc dinh dưỡng theo bệnh</h2>
        <p class="text-muted mb-0">Thiết lập các nhóm thực phẩm khuyên dùng hoặc nên tránh theo chẩn đoán bệnh lý</p>
    </div>
    <a href="{{ route('admin.nutrition.rules.create') }}" class="btn btn-success">
        <i class="bi bi-plus-circle me-1"></i> Thêm quy tắc mới
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
                    <th>Tên bệnh lý</th>
                    <th>Mã ICD</th>
                    <th>Thực phẩm</th>
                    <th>Loại gợi ý</th>
                    <th>Lý do / Giải thích</th>
                    <th class="text-end pe-4">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                    <tr>
                        <td class="ps-4 text-muted">{{ $rule->rule_id }}</td>
                        <td><strong>{{ $rule->disease_name }}</strong></td>
                        <td>
                            @if($rule->icd_code)
                                <span class="badge bg-secondary">{{ $rule->icd_code }}</span>
                            @else
                                <span class="text-muted small"><em>Không có</em></span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $rule->food->food_name ?? 'N/A' }}</strong>
                            <div class="text-muted small">{{ $rule->food->calories_per_100g ?? 0 }} kcal/100g</div>
                        </td>
                        <td>
                            @if($rule->recommendation_type === 'should_eat')
                                <span class="badge badge-should-eat px-2.5 py-1.5 fs-7 rounded-pill">
                                    <i class="bi bi-check-circle-fill me-1"></i>Nên dùng
                                </span>
                            @else
                                <span class="badge badge-should-avoid px-2.5 py-1.5 fs-7 rounded-pill">
                                    <i class="bi bi-x-circle-fill me-1"></i>Nên tránh
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="text-muted small" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $rule->reason }}">
                                {{ $rule->reason ?? '—' }}
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.nutrition.rules.edit', $rule->rule_id) }}"
                               class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.nutrition.rules.destroy', $rule->rule_id) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Bạn chắc chắn muốn xóa quy tắc dinh dưỡng này?')">
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
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Chưa có quy tắc dinh dưỡng nào được thiết lập. <a href="{{ route('admin.nutrition.rules.create') }}">Tạo quy tắc đầu tiên</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($rules->hasPages())
        <div class="card-footer bg-white border-0 d-flex justify-content-end">
            {{ $rules->links() }}
        </div>
    @endif
</div>

@endsection
