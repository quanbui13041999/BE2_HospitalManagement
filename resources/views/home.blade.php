<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quản lý Bệnh viện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background-color: #007bff; }
        .card { border: none; transition: transform 0.3s; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .sidebar { min-height: 100vh; background: #fff; border-right: 1px solid #dee2e6; }
       
    .profile-dropdown img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border: 2px solid #fff;
    }
    .dropdown-menu {
        border: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-radius: 10px;
    }
    .dropdown-item {
        padding: 10px 20px;
        display: flex;
        align-items: center;
    }
    .dropdown-item i {
        width: 25px;
        color: #007bff;
    }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><i class="fas fa-hospital-user me-2"></i>Hospital Pro</a>
        
        <div class="ms-auto d-flex align-items-center">
            <div class="dropdown profile-dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center text-white" href="#" role="button" id="profileMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->full_name) }}&background=random" class="rounded-circle me-2" alt="Avatar">
                    <span class="d-none d-md-inline">{{ $user->full_name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileMenu">
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-file-medical"></i> Hồ sơ bệnh án
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-star"></i> Thành viên ưu đãi
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="tiensu">
                            <i class="fas fa-history"></i> Tiền sử bệnh án
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt text-danger"></i> Đăng xuất
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 sidebar py-4">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active text-primary" href="#"><i class="fas fa-home me-2"></i>Tổng quan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="booking"><i class="fas fa-calendar-check me-2"></i>Lịch hẹn</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="#"><i class="fas fa-user-injured me-2"></i>Bệnh nhân</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="#"><i class="fas fa-file-medical me-2"></i>Hồ sơ bệnh án</a>
                    </li>
                </ul>
            </nav>

            <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Bảng điều khiển</h1>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card bg-white p-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white p-3 rounded-circle me-3">
                                    <i class="fas fa-calendar-alt fa-2x"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-0">Lịch hẹn hôm nay</h6>
                                    <h3 class="mb-0">12</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card bg-white p-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-success text-white p-3 rounded-circle me-3">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-0">Tổng bệnh nhân</h6>
                                    <h3 class="mb-0">1,240</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card bg-white p-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning text-white p-3 rounded-circle me-3">
                                    <i class="fas fa-user-md fa-2x"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-0">Bác sĩ trực</h6>
                                    <h3 class="mb-0">8</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info shadow-sm" role="alert">
                    <i class="fas fa-info-circle me-2"></i> Chào mừng bạn trở lại hệ thống. Bạn có <strong>3 lịch hẹn mới</strong> cần duyệt.
                </div>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>