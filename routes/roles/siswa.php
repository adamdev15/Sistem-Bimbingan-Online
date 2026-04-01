<?php

use App\Http\Controllers\Siswa\DashboardController;
use App\Http\Controllers\SuperAdmin\JadwalController;
use App\Http\Controllers\SuperAdmin\PembayaranController;
use App\Http\Controllers\SuperAdmin\PresensiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:siswa'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
    Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
    Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
});
