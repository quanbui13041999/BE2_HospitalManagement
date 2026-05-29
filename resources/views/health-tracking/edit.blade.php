<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chỉnh sửa nhật ký</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body{background:#f0f4f8}.card{border:none;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.07)}
        .conflict-box{border-left:5px solid #dc3545}
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-primary shadow-sm px-3 mb-4">
    <a class="navbar-brand fw-bold" href="{{ route('health-tracking.index') }}">
        <i class="bi bi-heart-pulse-fill me-2"></i>Health Tracker
    </a>
</nav>
<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="{{ route('health-tracking.show', $tracking) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h5 class="fw-bold mb-0"><i class="bi bi-pencil-square text-warning me-2"></i>Chỉnh sửa nhật ký</h5>
            </div>

            {{-- Cảnh báo xung đột dữ liệu (optimistic locking) --}}
            @if(session('conflict'))
            <div class="alert alert-danger alert-dismissible conflict-box mb-4">
                <div class="d-flex gap-2">
                    <i class="bi bi-exclamation-octagon-fill fs-4 text-danger flex-shrink-0"></i>
                    <div>
                        <strong>⚠️ Xung đột dữ liệu!</strong><br>
                        {{ session('conflict_message') }}
                        <div class="mt-2">
                            <a href="javascript:location.reload()" class="btn btn-danger btn-sm">
                                <i class="bi bi-arrow-clockwise me-1"></i>Tải lại trang ngay
                            </a>
                        </div>
                    </div>
                </div>
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                <button class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="card">
                <div class="card-header bg-warning text-dark py-3 d-flex justify-content-between">
                    <span><i class="bi bi-pencil me-2"></i>Cập nhật chỉ số sức khỏe</span>
                    <small class="text-muted">Phiên bản #{{ $tracking->version }}</small>
                </div>
                <div class="card-body p-4">
                    @include('health-tracking._form', [
                        'action'   => route('health-tracking.update', $tracking),
                        'method'   => 'PUT',
                        'old'      => $tracking->toArray(),
                        'tracking' => $tracking,
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
