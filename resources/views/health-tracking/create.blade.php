<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Thêm nhật ký sức khỏe</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>body{background:#f0f4f8}.card{border:none;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.07)}</style>
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
                <a href="{{ route('health-tracking.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle text-primary me-2"></i>Thêm nhật ký sức khỏe</h5>
            </div>
            <div class="card">
                <div class="card-header bg-primary text-white py-3">
                    <i class="bi bi-clipboard2-pulse me-2"></i>Nhập chỉ số hôm nay — {{ now()->format('d/m/Y H:i') }}
                </div>
                <div class="card-body p-4">
                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show d-flex gap-2">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ session('warning') }}
                            <button class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show d-flex gap-2">
                            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                            <button class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @include('health-tracking._form', [
                        'action' => route('health-tracking.store'),
                        'method' => 'POST',
                        'old'    => old(),
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
