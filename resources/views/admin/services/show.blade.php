{{-- resources/views/admin/services/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Chi tiết Dịch vụ: ' . $service->service_name)

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 me-auto">{{ $service->service_name }}</h4>
        <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i>Sửa dịch vụ
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row g-4">
        {{-- Thông tin dịch vụ --}}
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold"><i class="bi bi-info-circle me-2"></i>Thông tin</div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width:45%">Mã dịch vụ</td>
                            <td><code>{{ $service->service_code }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Khoa</td>
                            <td>{{ $service->department->department_name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Thời gian</td>
                            <td>{{ $service->duration_minutes }} phút</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Trạng thái</td>
                            <td>
                                @if($service->status)
                                    <span class="badge bg-success">Hoạt động</span>
                                @else
                                    <span class="badge bg-danger">Vô hiệu</span>
                                @endif
                            </td>
                        </tr>
                        @if($service->description)
                        <tr>
                            <td class="text-muted">Mô tả</td>
                            <td>{{ $service->description }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
                <div class="card-footer">
                    <form method="POST" action="{{ route('admin.services.toggle-status', $service) }}">
                        @csrf @method('PATCH')
                        <button type="submit"
                                class="btn btn-sm btn-outline-{{ $service->status ? 'warning' : 'success' }} w-100">
                            {{ $service->status ? 'Vô hiệu hoá dịch vụ' : 'Kích hoạt dịch vụ' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Bảng giá --}}
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center fw-semibold">
                    <span><i class="bi bi-tags me-2"></i>Bảng giá dịch vụ</span>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#addPriceForm">
                        <i class="bi bi-plus-lg me-1"></i>Thêm giá
                    </button>
                </div>

                {{-- Form thêm giá mới --}}
                <div class="collapse" id="addPriceForm">
                    <form method="POST" action="{{ route('admin.services.prices.store', $service) }}"
                          class="card-body border-bottom bg-light">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label form-label-sm">Loại giá</label>
                                <select name="price_type" class="form-select form-select-sm" required>
                                    @foreach($priceTypes as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label form-label-sm">Đơn giá (đ)</label>
                                <input type="number" name="price" class="form-control form-control-sm"
                                       min="0" step="1000" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label form-label-sm">Hiệu lực từ</label>
                                <input type="date" name="effective_date" class="form-control form-control-sm"
                                       value="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label form-label-sm">Đến ngày (tuỳ chọn)</label>
                                <input type="date" name="end_date" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="bi bi-check-lg me-1"></i>Lưu giá
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="collapse" data-bs-target="#addPriceForm">Đóng</button>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Loại giá</th>
                                    <th>Đơn giá</th>
                                    <th>Hiệu lực từ</th>
                                    <th>Đến ngày</th>
                                    <th>Trạng thái</th>
                                    <th>Người tạo</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($service->prices->sortByDesc('effective_date') as $price)
                                @php
                                    $isActive = $price->effective_date <= now() &&
                                        ($price->end_date === null || $price->end_date >= now());
                                @endphp
                                <tr class="{{ $isActive ? '' : 'text-muted' }}">
                                    <td>
                                        <span class="badge bg-secondary">{{ $price->price_type }}</span>
                                    </td>
                                    <td class="fw-semibold">
                                        {{ number_format($price->price, 0, ',', '.') }} đ
                                    </td>
                                    <td>{{ $price->effective_date->format('d/m/Y') }}</td>
                                    <td>{{ $price->end_date ? $price->end_date->format('d/m/Y') : '—' }}</td>
                                    <td>
                                        @if($isActive)
                                            <span class="badge bg-success-subtle text-success">Đang áp dụng</span>
                                        @elseif($price->effective_date > now())
                                            <span class="badge bg-info-subtle text-info">Sắp áp dụng</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Hết hiệu lực</span>
                                        @endif
                                    </td>
                                    <td class="small">{{ $price->createdBy->full_name ?? '—' }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editPriceModal{{ $price->price_id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST"
                                              action="{{ route('admin.services.prices.destroy', [$service, $price]) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Xoá mức giá này?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- Modal sửa giá --}}
                                <div class="modal fade" id="editPriceModal{{ $price->price_id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form method="POST"
                                              action="{{ route('admin.services.prices.update', [$service, $price]) }}">
                                            @csrf @method('PUT')
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Sửa mức giá</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body row g-3">
                                                    <div class="col-6">
                                                        <label class="form-label">Loại giá</label>
                                                        <select name="price_type" class="form-select" required>
                                                            @foreach($priceTypes as $type)
                                                                <option value="{{ $type }}"
                                                                    {{ $price->price_type === $type ? 'selected' : '' }}>
                                                                    {{ $type }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label">Đơn giá (đ)</label>
                                                        <input type="number" name="price" class="form-control"
                                                               value="{{ $price->price }}" min="0" step="1000" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label">Hiệu lực từ</label>
                                                        <input type="date" name="effective_date" class="form-control"
                                                               value="{{ $price->effective_date->toDateString() }}" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label">Đến ngày</label>
                                                        <input type="date" name="end_date" class="form-control"
                                                               value="{{ $price->end_date?->toDateString() }}">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                                                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Chưa có bảng giá nào.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
