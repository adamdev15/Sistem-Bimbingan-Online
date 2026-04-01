<?php

use App\Http\Controllers\Tutor\DashboardController;
use App\Http\Controllers\SuperAdmin\JadwalController;
use App\Http\Controllers\SuperAdmin\PresensiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:tutor'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
    Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
});
