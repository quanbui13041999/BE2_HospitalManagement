@extends('layouts.admin')

@section('title', 'Quản lý Bản tin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Quản lý Bản tin</h4>
    <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Thêm bài viết mới
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('warning'))
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    {{ session('warning') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Tiêu đề</th>
                        <th>Danh mục</th>
                        <th>Trạng thái</th>
                        <th>Email</th>
                        <th>Ngày tạo</th>
                        <th>Tác giả</th>
                        <th class="text-end pe-4">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($news as $item)
                    <tr>
                        <td class="ps-4">{{ $item->news_id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($item->thumbnail)
                                <img src="{{ $item->thumbnail_url }}" class="rounded me-2" width="40" height="40" style="object-cover">
                                @endif
                                <div>
                                    <div class="fw-bold text-truncate" style="max-width: 250px;">{{ $item->title }}</div>
                                    @if($item->is_published)
                                    <small class="text-muted">Đăng: {{ $item->published_at->format('d/m/Y') }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1">
                                {{ $item->category }}
                            </span>
                        </td>
                        <td>
                            <form action="{{ route('admin.news.toggle', $item->news_id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="version" value="{{ optional($item->updated_at)->format('Y-m-d H:i:s') }}">
                                <button type="submit" class="btn btn-sm {{ $item->is_published ? 'btn-success' : 'btn-outline-secondary' }}">
                                    {{ $item->is_published ? 'Đã đăng' : 'Nháp' }}
                                </button>
                            </form>
                        </td>
                        <td>
                            @if($item->email_sent)
                            <span class="text-success" title="Đã gửi email"><i class="bi bi-check-circle-fill"></i> Đã gửi</span>
                            @else
                            <form action="{{ route('admin.news.sendEmail', $item->news_id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="version" value="{{ optional($item->updated_at)->format('Y-m-d H:i:s') }}">
                                <button type="submit" class="btn btn-sm btn-outline-primary" {{ !$item->is_published ? 'disabled' : '' }} title="Gửi email cho bệnh nhân">
                                    <i class="bi bi-envelope"></i> Gửi
                                </button>
                            </form>
                            @endif
                        </td>
                        <td>{{ $item->created_at->format('d/m/Y') }}</td>
                        <td>{{ $item->author->full_name ?? 'Admin' }}</td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <a href="{{ route('admin.news.edit', $item->news_id) }}" class="btn btn-sm btn-outline-secondary" title="Sửa">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.news.destroy', $item->news_id) }}" method="POST" class="d-inline" data-confirm="Bạn có chắc chắn muốn xóa bài viết này?">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="version" value="{{ optional($item->updated_at)->format('Y-m-d H:i:s') }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            Chưa có bài viết nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($news->hasPages())
    <div class="card-footer bg-white border-0">
        {{ $news->links() }}
    </div>
    @endif
</div>
@endsection
