# Health Tracking — Hướng dẫn cài đặt

## Cấu trúc file (10 files)

```
app/Models/HealthTracking.php
app/Http/Controllers/HealthTrackingController.php
app/Http/Requests/HealthTrackingRequest.php       ← dùng chung store + update
app/Services/HealthRiskService.php
app/Policies/HealthTrackingPolicy.php
database/migrations/create_health_trackings_table.php
resources/views/health-tracking/_form.blade.php   ← partial dùng chung create + edit
resources/views/health-tracking/index.blade.php
resources/views/health-tracking/create.blade.php
resources/views/health-tracking/edit.blade.php
resources/views/health-tracking/show.blade.php
routes/health_tracking.php
```

---

## 1. Đăng ký Policy — AppServiceProvider.php

```php
use App\Models\HealthTracking;
use App\Policies\HealthTrackingPolicy;
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::policy(HealthTracking::class, HealthTrackingPolicy::class);
}
```

## 2. Include routes — routes/web.php

```php
require __DIR__.'/health_tracking.php';
```

## 3. Thêm cột role vào users (nếu chưa có)

```php
$table->enum('role', ['patient', 'doctor', 'admin'])->default('patient');
```

## 4. Chạy migration

```bash
php artisan migrate
```

---

## Tính năng Optimistic Locking

Khi 2 người cùng mở form edit một bản ghi:
- Người **submit trước** → lưu thành công, version tăng lên.
- Người **submit sau** → nhận thông báo đỏ:
  > *"Dữ liệu đã được chỉnh sửa bởi người khác lúc 14:32:05 27/05/2025. Vui lòng tải lại trang trước khi chỉnh sửa."*

---

## Risk Levels

| Level   | Màu  | Khi nào                                    |
|---------|------|--------------------------------------------|
| normal  | Xanh | Tất cả trong ngưỡng                        |
| warning | Vàng | HA > 140/90, nhịp tim ngoài 50–120, v.v.   |
| danger  | Đỏ   | HA > 180/120, SpO2 < 90, nhịp tim < 40 hoặc > 180, đường huyết > 300 |
