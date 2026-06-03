<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    private const ROLE_OPTIONS = [
        'Admin' => 'Admin',
        'Bác sĩ' => 'Bác sĩ',
        'Bệnh nhân' => 'Bệnh nhân',
        'Lễ tân' => 'Lễ tân',
        'Dược sĩ' => 'Dược sĩ',
    ];

    private const ACTION_OPTIONS = [
        'login' => 'Đăng nhập',
        'login_failed' => 'Đăng nhập thất bại',
        'logout' => 'Đăng xuất',
        'appointment_book' => 'Đặt lịch khám',
        'appointment_cancel' => 'Hủy lịch khám',
        'appointment_reschedule' => 'Dời lịch khám',
        'appointment_confirm' => 'Xác nhận lịch hẹn / hàng đợi',
        'payment' => 'Thanh toán lịch khám',
        'medical_record_create' => 'Tạo hồ sơ bệnh án',
        'medical_record_update' => 'Cập nhật hồ sơ bệnh án',
        'doctor_exam' => 'Bác sĩ khám bệnh',
        'diagnosis_create' => 'Tạo chẩn đoán',
        'prescription_create' => 'Kê đơn thuốc',
        'order_create' => 'Thêm chỉ định xét nghiệm / hình ảnh',
        'admin_user_create' => 'Admin thêm người dùng',
        'admin_user_update' => 'Admin sửa người dùng',
        'admin_user_delete' => 'Admin xóa người dùng',
        'admin_doctor_create' => 'Admin thêm bác sĩ',
        'admin_doctor_update' => 'Admin sửa bác sĩ',
        'admin_doctor_delete' => 'Admin xóa bác sĩ',
        'admin_service_create' => 'Admin thêm dịch vụ',
        'admin_service_update' => 'Admin sửa dịch vụ',
        'admin_service_delete' => 'Admin xóa dịch vụ',
        'admin_room_create' => 'Admin thêm phòng',
        'admin_room_update' => 'Admin sửa phòng',
        'pharmacy' => 'Dược sĩ xử lý thuốc / đơn thuốc',
    ];

    private const SUBJECT_OPTIONS = [
        'appointment' => 'Lịch khám',
        'patient' => 'Bệnh nhân',
        'doctor' => 'Bác sĩ',
        'medical_record' => 'Hồ sơ bệnh án',
        'payment' => 'Thanh toán',
        'service' => 'Dịch vụ',
        'medicine' => 'Thuốc',
        'room' => 'Phòng khám',
        'user' => 'Người dùng',
        'queue' => 'Hàng đợi',
    ];

    private const STATUS_OPTIONS = [
        'success' => 'Thành công',
        'failed' => 'Thất bại',
        'all_unknown' => 'Chưa xác định',
    ];

    public function index(Request $request)
    {
        $query = ActivityLog::with('user.role')->latest();
        $filters = $this->normalizeFilters($request);

        if ($search = $filters['search']) {
            $query->where(function ($q) use ($search) {
                $q->where('actor_name', 'like', "%{$search}%")
                    ->orWhere('actor_email', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('subject_type', 'like', "%{$search}%")
                    ->when(is_numeric($search), fn($numericQuery) => $numericQuery->orWhere('subject_id', (int) $search))
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('full_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($filters['role_name']) {
            $roleName = $filters['role_name'];
            $query->where(function ($q) use ($roleName) {
                $q->where('role_name', $roleName)
                    ->orWhereHas('user.role', fn($roleQuery) => $roleQuery->where('role_name', $roleName));
            });
        }

        if ($filters['action']) {
            $this->applyActionFilter($query, $filters['action']);
        }

        if ($filters['subject_type']) {
            $this->applySubjectFilter($query, $filters['subject_type']);
        }

        if ($filters['status']) {
            if ($filters['status'] === 'all_unknown') {
                $query->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', '');
                });
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if ($filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('admin.activity-logs.index', [
            'logs' => $logs,
            'roles' => $this->roleOptions(),
            'actions' => self::ACTION_OPTIONS,
            'subjectTypes' => $this->subjectOptions(),
            'statuses' => self::STATUS_OPTIONS,
            'filters' => $filters,
        ]);
    }

    public function show(ActivityLog $activityLog)
    {
        $activityLog->load('user.role');

        return view('admin.activity-logs.show', [
            'log' => $activityLog,
        ]);
    }

    private function normalizeFilters(Request $request): array
    {
        $request->validate([
            'search' => 'nullable|string|max:150',
            'role_name' => 'nullable|string|max:80',
            'action' => 'nullable|string|max:80',
            'subject_type' => 'nullable|string|max:80',
            'status' => 'nullable|string|max:80',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
            'page' => 'nullable|integer|min:1|max:1000',
        ]); /* fixed: validate filter log truoc khi dua vao query */

        $roleName = (string) $request->query('role_name', '');
        $action = (string) $request->query('action', '');
        $subjectType = (string) $request->query('subject_type', '');
        $status = (string) $request->query('status', '');

        return [
            'search' => trim((string) $request->query('search', '')),
            'role_name' => array_key_exists($roleName, $this->roleOptions()) ? $roleName : '',
            'action' => array_key_exists($action, self::ACTION_OPTIONS) ? $action : '',
            'subject_type' => array_key_exists($subjectType, $this->subjectOptions()) ? $subjectType : '',
            'status' => array_key_exists($status, self::STATUS_OPTIONS) ? $status : '',
            'date_from' => $this->validDate($request->query('date_from')),
            'date_to' => $this->validDate($request->query('date_to')),
        ];
    }

    private function roleOptions(): array
    {
        $fromLogs = ActivityLog::query()
            ->whereNotNull('role_name')
            ->where('role_name', '!=', '')
            ->distinct()
            ->pluck('role_name')
            ->mapWithKeys(fn($role) => [$role => $role])
            ->all();

        return collect(self::ROLE_OPTIONS)->merge($fromLogs)->sortKeys()->all();
    }

    private function subjectOptions(): array
    {
        $fromLogs = ActivityLog::query()
            ->whereNotNull('subject_type')
            ->where('subject_type', '!=', '')
            ->distinct()
            ->pluck('subject_type')
            ->mapWithKeys(fn($type) => [$type => self::SUBJECT_OPTIONS[$type] ?? $type])
            ->all();

        return collect(self::SUBJECT_OPTIONS)->merge($fromLogs)->sortKeys()->all();
    }

    private function validDate(mixed $date): string
    {
        $date = trim((string) $date);
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return '';
        }

        return $date;
    }

    private function applyActionFilter($query, string $action): void
    {
        if ($action === 'login') {
            $query->where(function ($q) {
                $q->where('action', 'like', '%Đăng nhập%')
                    ->orWhere('description', 'like', '%Đăng nhập%');
            })->where(function ($q) {
                $q->where('action', 'not like', '%thất bại%')
                    ->where('description', 'not like', '%thất bại%');
            });

            return;
        }

        $keywords = match ($action) {
            'login_failed' => ['Đăng nhập thất bại'],
            'logout' => ['Đăng xuất'],
            'appointment_book' => ['Đặt lịch khám', 'Đặt lịch hẹn'],
            'appointment_cancel' => ['Hủy lịch khám', 'Hủy lịch hẹn'],
            'appointment_reschedule' => ['Dời lịch khám', 'Dời lịch hẹn'],
            'appointment_confirm' => ['Xác nhận lịch hẹn', 'Xử lý hàng đợi', 'hàng đợi'],
            'payment' => ['Thanh toán lịch khám', 'Thanh toán', 'Xác nhận thanh toán'],
            'medical_record_create' => ['Tạo hồ sơ bệnh án'],
            'medical_record_update' => ['Cập nhật hồ sơ bệnh án'],
            'doctor_exam' => ['Bác sĩ khám bệnh', 'đã khám cho bệnh nhân'],
            'diagnosis_create' => ['Tạo chẩn đoán'],
            'prescription_create' => ['Kê đơn thuốc'],
            'order_create' => ['Thêm chỉ định xét nghiệm / hình ảnh', 'chỉ định'],
            'admin_user_create' => ['Admin thêm người dùng'],
            'admin_user_update' => ['Admin sửa người dùng'],
            'admin_user_delete' => ['Admin xóa người dùng'],
            'admin_doctor_create' => ['Admin thêm bác sĩ'],
            'admin_doctor_update' => ['Admin sửa bác sĩ'],
            'admin_doctor_delete' => ['Admin xóa bác sĩ'],
            'admin_service_create' => ['Admin thêm dịch vụ'],
            'admin_service_update' => ['Admin sửa dịch vụ'],
            'admin_service_delete' => ['Admin xóa dịch vụ'],
            'admin_room_create' => ['Admin thêm phòng'],
            'admin_room_update' => ['Admin sửa phòng'],
            'pharmacy' => ['Dược sĩ', 'thuốc', 'đơn thuốc', 'xuất thuốc'],
            default => [],
        };

        if (!$keywords) {
            return;
        }

        $query->where(function ($q) use ($keywords) {
            foreach ($keywords as $keyword) {
                $q->orWhere('action', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            }
        });
    }

    private function applySubjectFilter($query, string $subjectType): void
    {
        $legacyKeywords = match ($subjectType) {
            'appointment' => ['lịch hẹn', 'lịch khám'],
            'medical_record' => ['hồ sơ bệnh án'],
            'payment' => ['thanh toán', 'giao dịch'],
            'review' => ['đánh giá'],
            'doctor' => ['bác sĩ'],
            'service' => ['dịch vụ'],
            'room' => ['phòng'],
            'user' => ['người dùng', 'tài khoản'],
            'medicine' => ['thuốc', 'đơn thuốc'],
            'queue' => ['hàng đợi'],
            default => [],
        };

        $query->where(function ($q) use ($subjectType, $legacyKeywords) {
            $q->where('subject_type', $subjectType);

            foreach ($legacyKeywords as $keyword) {
                $q->orWhere(function ($legacyQuery) use ($keyword) {
                    $legacyQuery->where(function ($emptySubjectQuery) {
                        $emptySubjectQuery->whereNull('subject_type')->orWhere('subject_type', '');
                    })->where(function ($textQuery) use ($keyword) {
                        $textQuery->where('action', 'like', "%{$keyword}%")
                            ->orWhere('description', 'like', "%{$keyword}%");
                    });
                });
            }
        });
    }
}
