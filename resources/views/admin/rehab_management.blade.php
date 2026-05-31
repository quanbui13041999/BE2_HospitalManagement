@extends('layouts.admin')

@section('title', 'Quan ly bai tap phuc hoi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Bai tap phuc hoi</h4>
        <div class="text-muted">Quan ly noi dung huong dan tap luyen cho benh nhan.</div>
    </div>
    <a href="{{ route('admin.rehab.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Tao bai tap
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('warning'))
    <div class="alert alert-warning">{{ session('warning') }}</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card p-3"><div class="text-muted small">Tong bai</div><h3>{{ $stats['total'] }}</h3></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted small">Cong khai</div><h3>{{ $stats['published'] }}</h3></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted small">Ban nhap</div><h3>{{ $stats['draft'] }}</h3></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted small">Luot xem</div><h3>{{ $stats['views'] }}</h3></div></div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Tieu de</th>
                    <th>Nhom</th>
                    <th>Giai doan</th>
                    <th>Trang thai</th>
                    <th>Luot xem</th>
                    <th class="text-end">Thao tac</th>
                </tr>
            </thead>
            <tbody>
                @forelse($exercises as $exercise)
                    <tr>
                        <td>
                            <strong>{{ $exercise->title }}</strong>
                            <div class="small text-muted">{{ optional($exercise->author)->full_name ?? 'Chua ro' }}</div>
                        </td>
                        <td>{{ $exercise->category_label }}</td>
                        <td>{{ $exercise->phase_label }}</td>
                        <td><span class="badge {{ $exercise->status === 'published' ? 'bg-success' : 'bg-secondary' }}">{{ $exercise->status_label }}</span></td>
                        <td>{{ $exercise->view_count }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.rehab.edit', $exercise) }}" class="btn btn-sm btn-outline-primary">Sua</a>
                            <form action="{{ route('admin.rehab.destroy', $exercise) }}" method="POST" class="d-inline" onsubmit="return confirm('Xoa bai tap nay?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Xoa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Chua co bai tap nao.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $exercises->links() }}</div>
</div>
@endsection
