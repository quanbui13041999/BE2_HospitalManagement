{{-- resources/views/nutrition/admin/index.blade.php --}}

@extends('layouts.nutrition')

@section('title', 'Quản lý bài viết dinh dưỡng')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-newspaper text-primary me-2"></i>Bài viết dinh dưỡng</h2>
        <p class="text-muted mb-0">Quản lý nội dung lời khuyên cho bệnh nhân</p>
    </div>
    <a href="{{ route('admin.nutrition.create') }}" class="btn btn-success">
        <i class="bi bi-plus-circle me-1"></i> Thêm bài viết
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
                    <th>Tiêu đề</th>
                    <th>Bệnh mục tiêu</th>
                    <th>Tác giả</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th class="text-end pe-4">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($articles as $article)
                    <tr>
                        <td class="ps-4 text-muted">{{ $article->article_id }}</td>
                        <td>
                            <strong>{{ $article->title }}</strong>
                            <p class="mb-0 small text-muted">{{ $article->excerpt }}</p>
                        </td>
                        <td>
                            @if($article->target_disease)
                                <span class="badge bg-info text-dark">{{ $article->target_disease }}</span>
                            @else
                                <span class="text-muted">--</span>
                            @endif
                        </td>
                        <td>{{ $article->doctor?->full_name ?? 'Admin' }}</td>
                        <td>
                            @if($article->status === 1)
                                <span class="badge bg-success">Xuất bản</span>
                            @else
                                <span class="badge bg-secondary">Nháp</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $article->created_at->format('d/m/Y') }}</td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.nutrition.edit', $article->article_id) }}"
                               class="btn btn-sm btn-outline-primary me-1"
                               title="Sửa bài viết">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.nutrition.destroy', $article->article_id) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Bạn có chắc muốn xóa bài viết này không?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa bài viết">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Chưa có bài viết nào. <a href="{{ route('admin.nutrition.create') }}">Tạo bài viết đầu tiên</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($articles->hasPages())
        <div class="card-footer bg-white border-0 d-flex justify-content-end">
            {{ $articles->links() }}
        </div>
    @endif
</div>
@endsection
