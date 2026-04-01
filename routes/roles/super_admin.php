<?php

use App\Http\Controllers\SuperAdmin\CabangController;
use App\Http\Controllers\AdminCabang\DashboardController as AdminCabangDashboardController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboardController;
use App\Http\Controllers\Tutor\DashboardController as TutorDashboardController;
use App\Http\Controllers\Tutor\SiswaController as TutorSiswaController;
use App\Http\Controllers\SuperAdmin\JadwalController;
use App\Http\Controllers\SuperAdmin\LaporanController;
use App\Http\Controllers\SuperAdmin\PembayaranController;
use App\Http\Controllers\SuperAdmin\PresensiController;
use App\Http\Controllers\SuperAdmin\SiswaController;
use App\Http\Controllers\SuperAdmin\TutorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user->hasRole('super_admin')) {
            return app(SuperAdminDashboardController::class)();
        }
        if ($user->hasRole('admin_cabang')) {
            return app(AdminCabangDashboardController::class)();
        }
        if ($user->hasRole('tutor')) {
            return app(TutorDashboardController::class)();
        }

        return app(SiswaDashboardController::class)();
    })->middleware('role:super_admin|admin_cabang|tutor|siswa')->name('dashboard');

    Route::middleware('role:super_admin')->group(function () {
    Route::get('/cabang', [CabangController::class, 'index'])->name('cabang.index');
    Route::post('/cabang', [CabangController::class, 'store'])->name('cabang.store');
    Route::put('/cabang/{cabang}', [CabangController::class, 'update'])->name('cabang.update');
    Route::delete('/cabang/{cabang}', [CabangController::class, 'destroy'])->name('cabang.destroy');
    });

    Route::middleware('role:super_admin|admin_cabang')->group(function () {
    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
    Route::get('/siswa/export/csv', [SiswaController::class, 'exportCsv'])->name('siswa.export.csv');
    Route::get('/siswa/{siswa}', [SiswaController::class, 'show'])->name('siswa.show');
    Route::post('/siswa', [SiswaController::class, 'store'])->name('siswa.store');
    Route::put('/siswa/{siswa}', [SiswaController::class, 'update'])->name('siswa.update');
    Route::delete('/siswa/{siswa}', [SiswaController::class, 'destroy'])->name('siswa.destroy');

    Route::get('/tutors', [TutorController::class, 'index'])->name('tutors.index');
    Route::get('/tutors/{tutor}', [TutorController::class, 'show'])->name('tutors.show');
    Route::post('/tutors', [TutorController::class, 'store'])->name('tutors.store');
    Route::put('/tutors/{tutor}', [TutorController::class, 'update'])->name('tutors.update');
    Route::delete('/tutors/{tutor}', [TutorController::class, 'destroy'])->name('tutors.destroy');
    });

    Route::middleware('role:super_admin|admin_cabang|tutor|siswa')->group(function () {
    Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
    Route::middleware('role:super_admin|admin_cabang')->group(function () {
        Route::post('/jadwal', [JadwalController::class, 'store'])->name('jadwal.store');
        Route::put('/jadwal/{jadwal}', [JadwalController::class, 'update'])->name('jadwal.update');
        Route::delete('/jadwal/{jadwal}', [JadwalController::class, 'destroy'])->name('jadwal.destroy');
    });

    Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
    Route::get('/presensi/export', [PresensiController::class, 'export'])->name('presensi.export');

    Route::get('/bimbingan-siswa', [TutorSiswaController::class, 'index'])
        ->middleware('role:tutor')
        ->name('tutor.siswa.index');
    });

    Route::middleware('role:super_admin|siswa')->group(function () {
    Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::middleware('role:super_admin')->group(function () {
        Route::post('/pembayaran/massal', [PembayaranController::class, 'massStore'])->name('pembayaran.mass.store');
    });
    });

    Route::middleware('role:super_admin|admin_cabang')->group(function () {
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.export.pdf');
    Route::get('/laporan/export/excel', [LaporanController::class, 'exportExcel'])->name('laporan.export.excel');
    });
});
