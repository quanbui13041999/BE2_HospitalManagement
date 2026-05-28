<?php

namespace App\Providers;

use App\Models\Doctor;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        \Illuminate\Support\Facades\Schema::defaultStringLength(191);

        \Illuminate\Pagination\Paginator::useBootstrapFive();

        $this->registerActivityObservers();

    }

    private function registerActivityObservers(): void
    {
        User::created(function (User $user) {
            $actor = Auth::user();
            if (!$actor?->isAdmin()) {
                return;
            }

            ActivityLogService::log(
                'Admin thêm người dùng',
                'Admin ' . $actor->full_name . ' đã thêm người dùng ' . $user->full_name . '.',
                'user',
                $user->user_id,
                [
                    'created_user' => $user->only(['user_id', 'full_name', 'email', 'role_id', 'status']),
                ],
                'success',
                $actor
            );
        });

        User::updated(function (User $user) {
            $actor = Auth::user();
            if (!$actor?->isAdmin()) {
                return;
            }

            $changes = ActivityLogService::summarizeChanges(
                array_intersect_key($user->getOriginal(), array_flip(['full_name', 'email', 'phone', 'address', 'role_id', 'status'])),
                $user->only(['full_name', 'email', 'phone', 'address', 'role_id', 'status']),
                ['full_name', 'email', 'phone', 'address', 'role_id', 'status']
            );

            if (!$changes) {
                return;
            }

            ActivityLogService::log(
                'Admin sửa người dùng',
                'Admin ' . $actor->full_name . ' đã cập nhật người dùng ' . $user->full_name . '.',
                'user',
                $user->user_id,
                ['changes' => $changes],
                'success',
                $actor
            );
        });

        User::deleting(function (User $user) {
            $actor = Auth::user();
            if (!$actor?->isAdmin()) {
                return;
            }

            ActivityLogService::log(
                'Admin xóa người dùng',
                'Admin ' . $actor->full_name . ' đã xóa người dùng ' . $user->full_name . '.',
                'user',
                $user->user_id,
                [
                    'deleted_user' => $user->only(['user_id', 'full_name', 'email', 'role_id', 'status']),
                ],
                'success',
                $actor
            );
        });

        Doctor::created(function (Doctor $doctor) {
            $actor = Auth::user();
            if (!$actor?->isAdmin()) {
                return;
            }

            ActivityLogService::log(
                'Admin thêm bác sĩ',
                'Admin ' . $actor->full_name . ' đã thêm bác sĩ ' . $doctor->full_name . '.',
                'doctor',
                $doctor->doctor_id,
                [
                    'doctor' => $doctor->only(['doctor_id', 'user_id', 'full_name', 'department_id', 'experience', 'price', 'status']),
                ],
                'success',
                $actor
            );
        });

        Doctor::updated(function (Doctor $doctor) {
            $actor = Auth::user();
            if (!$actor?->isAdmin()) {
                return;
            }

            $fields = ['full_name', 'department_id', 'experience', 'price', 'status'];
            $changes = ActivityLogService::summarizeChanges(
                array_intersect_key($doctor->getOriginal(), array_flip($fields)),
                $doctor->only($fields),
                $fields
            );

            if (!$changes) {
                return;
            }

            ActivityLogService::log(
                'Admin sửa bác sĩ',
                'Admin ' . $actor->full_name . ' đã cập nhật bác sĩ ' . $doctor->full_name . '.',
                'doctor',
                $doctor->doctor_id,
                ['changes' => $changes],
                'success',
                $actor
            );
        });

        Doctor::deleting(function (Doctor $doctor) {
            $actor = Auth::user();
            if (!$actor?->isAdmin()) {
                return;
            }

            ActivityLogService::log(
                'Admin xóa bác sĩ',
                'Admin ' . $actor->full_name . ' đã xóa bác sĩ ' . $doctor->full_name . '.',
                'doctor',
                $doctor->doctor_id,
                [
                    'deleted_doctor' => $doctor->only(['doctor_id', 'user_id', 'full_name', 'department_id', 'experience', 'price', 'status']),
                ],
                'success',
                $actor
            );
        });
    }
}
