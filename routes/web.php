<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\RecapController;
use App\Http\Controllers\StatusSubmitController;
use Illuminate\Support\Facades\Route;

Route::view('/panduan', 'guide')->name('guide');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/absensi', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/absensi', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/status-submit', StatusSubmitController::class)->name('submissions.index');
    Route::get('/rekap-harian', RecapController::class)->middleware('admin')->name('recap.index');

    Route::middleware('admin')->group(function (): void {
        Route::patch('/pegawai/{employee}/nonaktifkan', [EmployeeController::class, 'deactivate'])
            ->name('employees.deactivate');
        Route::resource('/pegawai', EmployeeController::class)
            ->parameters(['pegawai' => 'employee'])
            ->names('employees')
            ->except(['show', 'destroy']);
    });
});
