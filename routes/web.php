<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataStokController;
use App\Http\Controllers\PrediksiController;

// Authentication Routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Data Stok Routes
    Route::prefix('data-stok')->name('data-stok.')->group(function () {
        Route::get('/', [DataStokController::class, 'index'])->name('index');
        Route::post('/', [DataStokController::class, 'store'])->name('store');
        Route::put('/{id}', [DataStokController::class, 'update'])->name('update');
        Route::delete('/{id}', [DataStokController::class, 'destroy'])->name('destroy');
        Route::post('/training', [DataStokController::class, 'training'])->name('training');
    });
    
    // Prediksi Routes
    Route::prefix('prediksi')->name('prediksi.')->group(function () {
        Route::get('/', [PrediksiController::class, 'index'])->name('index');
        Route::get('/create', [PrediksiController::class, 'create'])->name('create');
        Route::post('/predict', [PrediksiController::class, 'predict'])->name('predict');
        Route::delete('/{id}', [PrediksiController::class, 'destroy'])->name('destroy');
    });
});