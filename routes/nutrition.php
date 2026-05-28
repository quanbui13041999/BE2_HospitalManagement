<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminNutritionController;
use App\Http\Controllers\PatientNutritionController;



// =============================================
// PHÂN HỆ 1: ADMIN & BÁC SĨ (AdminNutritionController)
// Middleware 'role' kiểm tra cột `role_id` trong bảng users:
//   role_id = 1 → Admin, role_id = 2 → Bác sĩ
// =============================================
Route::middleware(['auth', 'role:1,2'])->prefix('admin/nutrition')->name('admin.nutrition.')->group(function () {

    // 1. Quản lý bài viết (Nutrition Articles)
    Route::get('/', [AdminNutritionController::class, 'index'])->name('index');
    Route::get('/create', [AdminNutritionController::class, 'create'])->name('create');
    Route::post('/', [AdminNutritionController::class, 'store'])->name('store');
    Route::get('/{article}/edit', [AdminNutritionController::class, 'edit'])->name('edit');
    Route::put('/{article}', [AdminNutritionController::class, 'update'])->name('update');
    Route::delete('/{article}', [AdminNutritionController::class, 'destroy'])->name('destroy');

    // 2. Quản lý quy tắc gợi ý thực đơn (Disease Nutrition Rules)
    Route::prefix('rules')->name('rules.')->group(function () {
        Route::get('/', [AdminNutritionController::class, 'rulesIndex'])->name('index');
        Route::get('/create', [AdminNutritionController::class, 'rulesCreate'])->name('create');
        Route::post('/', [AdminNutritionController::class, 'rulesStore'])->name('store');
        Route::get('/{rule}/edit', [AdminNutritionController::class, 'rulesEdit'])->name('edit');
        Route::put('/{rule}', [AdminNutritionController::class, 'rulesUpdate'])->name('update');
        Route::delete('/{rule}', [AdminNutritionController::class, 'rulesDestroy'])->name('destroy');
    });

    // 3. Quản lý danh mục thực phẩm & calo (Foods Database)
    Route::prefix('foods')->name('foods.')->group(function () {
        Route::get('/', [AdminNutritionController::class, 'foodsIndex'])->name('index');
        Route::get('/create', [AdminNutritionController::class, 'foodsCreate'])->name('create');
        Route::post('/', [AdminNutritionController::class, 'foodsStore'])->name('store');
        Route::get('/{food}/edit', [AdminNutritionController::class, 'foodsEdit'])->name('edit');
        Route::put('/{food}', [AdminNutritionController::class, 'foodsUpdate'])->name('update');
        Route::delete('/{food}', [AdminNutritionController::class, 'foodsDestroy'])->name('destroy');
    });
});

// =============================================
// PHÂN HỆ 2: BỆNH NHÂN (PatientNutritionController)
// =============================================
Route::middleware(['auth'])->prefix('patient/nutrition')->name('patient.nutrition.')->group(function () {
    Route::get('/', [PatientNutritionController::class, 'index'])->name('index');
    Route::post('/meal-log', [PatientNutritionController::class, 'storeMealLog'])->name('meal-log.store');
    Route::delete('/meal-log/{mealLog}', [PatientNutritionController::class, 'destroyMealLog'])->name('meal-log.destroy');
});
