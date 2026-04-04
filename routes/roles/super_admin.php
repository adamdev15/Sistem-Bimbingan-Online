<?php

use App\Http\Controllers\AdminCabang\DashboardController as AdminCabangDashboardController;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboardController;
use App\Http\Controllers\SuperAdmin\CabangController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\JadwalController;
use App\Http\Controllers\SuperAdmin\LaporanController;
use App\Http\Controllers\SuperAdmin\MataPelajaranController;
use App\Http\Controllers\SuperAdmin\PembayaranController;
use App\Http\Controllers\SuperAdmin\PresensiController;
use App\Http\Controllers\SuperAdmin\SalaryController;
use App\Http\Controllers\SuperAdmin\SiswaController;
use App\Http\Controllers\SuperAdmin\TutorController;
use App\Http\Controllers\SuperAdmin\UserManagementController;
use App\Http\Controllers\Tutor\DashboardController as TutorDashboardController;
use App\Http\Controllers\Tutor\SiswaController as TutorSiswaController;
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

        Route::get('/pengguna', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/pengguna', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/pengguna/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/pengguna/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('role:super_admin|admin_cabang')->group(function () {
        Route::get('/mata-pelajaran', [MataPelajaranController::class, 'index'])->name('mata-pelajaran.index');
        Route::middleware('role:super_admin')->group(function () {
            Route::post('/mata-pelajaran', [MataPelajaranController::class, 'store'])->name('mata-pelajaran.store');
            Route::put('/mata-pelajaran/{mataPelajaran}', [MataPelajaranController::class, 'update'])->name('mata-pelajaran.update');
            Route::delete('/mata-pelajaran/{mataPelajaran}', [MataPelajaranController::class, 'destroy'])->name('mata-pelajaran.destroy');
        });

        Route::get('/gaji-tutor', [SalaryController::class, 'index'])->name('salaries.index');
        Route::get('/gaji-tutor/export/pdf', [SalaryController::class, 'exportPdf'])->name('salaries.export.pdf');
        Route::get('/gaji-tutor/export/excel', [SalaryController::class, 'exportExcel'])->name('salaries.export.excel');
        Route::post('/gaji-tutor', [SalaryController::class, 'store'])->name('salaries.store');
        Route::patch('/gaji-tutor/{salary}', [SalaryController::class, 'update'])->name('salaries.update');

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
            Route::get('/jadwal/{jadwal}/peserta', [JadwalController::class, 'peserta'])->name('jadwal.peserta');
            Route::put('/jadwal/{jadwal}/peserta', [JadwalController::class, 'updatePeserta'])->name('jadwal.peserta.update');
        });

        Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
        Route::post('/presensi/sesi', [PresensiController::class, 'storeSesi'])
            ->middleware('role:tutor')
            ->name('presensi.store-sesi');
        Route::get('/presensi/export', [PresensiController::class, 'export'])->name('presensi.export');

        Route::get('/bimbingan-siswa', [TutorSiswaController::class, 'index'])
            ->middleware('role:tutor')
            ->name('tutor.siswa.index');
    });

    Route::middleware('role:super_admin|admin_cabang|siswa')->group(function () {
        Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
        Route::middleware('role:siswa')->group(function () {
            Route::get('/pembayaran/{payment}/manual', [PembayaranController::class, 'manualPayment'])->name('pembayaran.manual');
            Route::post('/pembayaran/{payment}/snap-token', [PembayaranController::class, 'snapToken'])->name('pembayaran.snap-token');
            Route::post('/pembayaran/{payment}/sync-midtrans', [PembayaranController::class, 'syncMidtrans'])->name('pembayaran.sync-midtrans');
        });
        Route::middleware('role:super_admin|admin_cabang')->group(function () {
            Route::get('/pembayaran/export/ringkasan.pdf', [PembayaranController::class, 'exportRingkasanPdf'])->name('pembayaran.export.ringkasan.pdf');
            Route::get('/pembayaran/export/ringkasan.xlsx', [PembayaranController::class, 'exportRingkasanExcel'])->name('pembayaran.export.ringkasan.excel');
            Route::get('/pembayaran/export/outstanding.pdf', [PembayaranController::class, 'exportOutstandingPdf'])->name('pembayaran.export.outstanding.pdf');
            Route::get('/pembayaran/export/outstanding.xlsx', [PembayaranController::class, 'exportOutstandingExcel'])->name('pembayaran.export.outstanding.excel');
            Route::post('/pembayaran/massal', [PembayaranController::class, 'massStore'])->name('pembayaran.mass.store');
            Route::post('/pembayaran/notify-due-bulk', [PembayaranController::class, 'notifyDueBulk'])->name('pembayaran.notify-due-bulk');
            Route::post('/pembayaran/{payment}/mark-lunas', [PembayaranController::class, 'markLunas'])->name('pembayaran.mark-lunas');
        });
    });

    Route::middleware('role:super_admin|admin_cabang')->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/export/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.export.pdf');
        Route::get('/laporan/export/excel', [LaporanController::class, 'exportExcel'])->name('laporan.export.excel');
    });
});
