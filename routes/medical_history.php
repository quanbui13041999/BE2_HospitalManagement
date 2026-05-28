<?php

use App\Http\Controllers\MedicalHistoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('medical_history')
    ->middleware(['auth'])
    ->name('medical_history.')
    ->group(function () {
        Route::get('/', [MedicalHistoryController::class, 'index'])->name('index');
    });
