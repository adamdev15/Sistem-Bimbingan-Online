<?php

use App\Http\Controllers\AdminCabang\DashboardController as AdminCabangDashboardController;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboardController;
use App\Http\Controllers\SuperAdmin\BranchPriceController;
use App\Http\Controllers\SuperAdmin\CabangController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\KehadiranTutorController;
use App\Http\Controllers\SuperAdmin\LaporanKeuanganController;
use App\Http\Controllers\SuperAdmin\FeeController;
use App\Http\Controllers\SuperAdmin\MateriLesController;
use App\Http\Controllers\SuperAdmin\PengeluaranController;
use App\Http\Controllers\SuperAdmin\PembayaranController;
use App\Http\Controllers\SuperAdmin\PresensiController;
use App\Http\Controllers\SuperAdmin\SalaryController;
use App\Http\Controllers\SuperAdmin\SiswaController;
use App\Http\Controllers\SuperAdmin\TutorController;
use App\Http\Controllers\SuperAdmin\UserManagementController;
use App\Http\Controllers\SuperAdmin\WhatsappSettingsController;
use App\Http\Controllers\SuperAdmin\SettingController;
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
        Route::get('/api/dashboard/keuangan-chart', [SuperAdminDashboardController::class, 'keuanganChart'])->name('api.dashboard.keuangan-chart');
        Route::get('/cabang', [CabangController::class, 'index'])->name('cabang.index');
        Route::post('/cabang', [CabangController::class, 'store'])->name('cabang.store');
        Route::put('/cabang/{cabang}', [CabangController::class, 'update'])->name('cabang.update');
        Route::delete('/cabang/{cabang}', [CabangController::class, 'destroy'])->name('cabang.destroy');

        Route::get('/pengguna', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/pengguna', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/pengguna/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/pengguna/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

        Route::get('/pengaturan/whatsapp', [WhatsappSettingsController::class, 'edit'])->name('whatsapp-settings.edit');
        Route::put('/pengaturan/whatsapp', [WhatsappSettingsController::class, 'update'])->name('whatsapp-settings.update');
        Route::post('/pengaturan/whatsapp/test', [WhatsappSettingsController::class, 'test'])->name('whatsapp-settings.test');

        Route::get('/pengaturan/website', [\App\Http\Controllers\SuperAdmin\SettingController::class, 'index'])->name('settings.website');
        Route::put('/pengaturan/website', [\App\Http\Controllers\SuperAdmin\SettingController::class, 'update'])->name('settings.website.update');

        Route::get('/biaya', [FeeController::class, 'index'])->name('fees.index');
        Route::post('/biaya', [FeeController::class, 'store'])->name('fees.store');
        Route::put('/biaya/{fee}', [FeeController::class, 'update'])->name('fees.update');
        Route::delete('/biaya/{fee}', [FeeController::class, 'destroy'])->name('fees.destroy');
    });

    Route::middleware('role:super_admin|admin_cabang')->group(function () {
        Route::get('/materi-les', [\App\Http\Controllers\SuperAdmin\MateriLesController::class, 'index'])->name('materi-les.index');
        Route::middleware('role:super_admin')->group(function () {
            Route::post('/materi-les', [\App\Http\Controllers\SuperAdmin\MateriLesController::class, 'store'])->name('materi-les.store');
            Route::put('/materi-les/{materiLes}', [\App\Http\Controllers\SuperAdmin\MateriLesController::class, 'update'])->name('materi-les.update');
            Route::delete('/materi-les/{materiLes}', [\App\Http\Controllers\SuperAdmin\MateriLesController::class, 'destroy'])->name('materi-les.destroy');
        });

        Route::get('/gaji-tutor', [SalaryController::class, 'index'])->name('salaries.index');
        Route::get('/gaji-tutor/export/pdf', [SalaryController::class, 'exportPdf'])->name('salaries.export.pdf');
        Route::get('/gaji-tutor/export/excel', [SalaryController::class, 'exportExcel'])->name('salaries.export.excel');
        Route::post('/gaji-tutor', [SalaryController::class, 'store'])->name('salaries.store');
        Route::patch('/gaji-tutor/{salary}', [SalaryController::class, 'update'])->name('salaries.update');
        Route::delete('/gaji-tutor/{salary}', [SalaryController::class, 'destroy'])->name('salaries.destroy');
        Route::get('/gaji-tutor/{salary}/slip', [SalaryController::class, 'printSlip'])->name('salaries.print-slip');
        Route::get('/api/salaries/attendance-count', [SalaryController::class, 'getAttendanceCount'])->name('api.salaries.attendance-count');

        Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
        Route::get('/siswa/export/csv', [SiswaController::class, 'exportCsv'])->name('siswa.export.csv');
        Route::get('/siswa/export/excel', [SiswaController::class, 'exportExcel'])->name('siswa.export.excel');
        Route::post('/siswa/import', [SiswaController::class, 'importExcel'])->name('siswa.import');
        Route::get('/siswa/template/download', [SiswaController::class, 'downloadTemplate'])->name('siswa.template');
        Route::get('/siswa/{siswa}', [SiswaController::class, 'show'])->name('siswa.show');
        Route::post('/siswa', [SiswaController::class, 'store'])->name('siswa.store');
        Route::put('/siswa/{siswa}', [SiswaController::class, 'update'])->name('siswa.update');
        Route::delete('/siswa/{siswa}', [SiswaController::class, 'destroy'])->name('siswa.destroy');

        Route::get('/tutors', [TutorController::class, 'index'])->name('tutors.index');
        Route::get('/tutors/{tutor}', [TutorController::class, 'show'])->name('tutors.show');
        Route::post('/tutors', [TutorController::class, 'store'])->name('tutors.store');
        Route::put('/tutors/{tutor}', [TutorController::class, 'update'])->name('tutors.update');
        Route::delete('/tutors/{tutor}', [TutorController::class, 'destroy'])->name('tutors.destroy');

        Route::get('/harga-cabang', [BranchPriceController::class, 'index'])->name('branch-prices.index');
        Route::put('/harga-cabang', [BranchPriceController::class, 'update'])->name('branch-prices.update');
    });

    Route::middleware('role:super_admin|admin_cabang|tutor|siswa')->group(function () {
        // Obsolete jadwal routes removed

        Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
        Route::get('/presensi/input', [PresensiController::class, 'create'])->name('presensi.create');
        Route::post('/presensi/sesi', [PresensiController::class, 'storeSesi'])
            ->middleware('role:super_admin|admin_cabang')
            ->name('presensi.store-sesi');
        Route::get('/presensi/export', [PresensiController::class, 'export'])->name('presensi.export');
        Route::get('/presensi/generate-kartu', [PresensiController::class, 'printCard'])->name('presensi.print-card');
        Route::get('/api/cabang/{cabang}/tutors', [PresensiController::class, 'getTutorsByCabang'])->name('api.cabang.tutors');
        Route::get('/api/cabang/{cabang}/students', [PresensiController::class, 'getStudentsByCabang'])->name('api.cabang.students');
        Route::patch('/presensi/{presensi}', [PresensiController::class, 'update'])->name('presensi.update');
        Route::delete('/presensi/{presensi}', [PresensiController::class, 'destroy'])->name('presensi.destroy');

        // KEHADIRAN TUTOR
        Route::get('/kehadiran-tutor', [KehadiranTutorController::class, 'index'])->name('kehadiran-tutor.index');
        Route::post('/kehadiran-tutor', [KehadiranTutorController::class, 'store'])->name('kehadiran-tutor.store');
        Route::put('/kehadiran-tutor/{kehadiranTutor}', [KehadiranTutorController::class, 'update'])->name('kehadiran-tutor.update');
        Route::delete('/kehadiran-tutor/{kehadiranTutor}', [KehadiranTutorController::class, 'destroy'])->name('kehadiran-tutor.destroy');
        Route::get('/kehadiran-tutor/export', [KehadiranTutorController::class, 'export'])->name('kehadiran-tutor.export');

        // PENGELUARAN
        Route::get('/pengeluaran', [PengeluaranController::class, 'index'])->name('pengeluaran.index');
        Route::get('/pengeluaran/print', [PengeluaranController::class, 'printReport'])->name('pengeluaran.print');
        Route::post('/pengeluaran', [PengeluaranController::class, 'store'])->name('pengeluaran.store');
        Route::put('/pengeluaran/{pengeluaran}', [PengeluaranController::class, 'update'])->name('pengeluaran.update');
        Route::delete('/pengeluaran/{pengeluaran}', [PengeluaranController::class, 'destroy'])->name('pengeluaran.destroy');

        // LAPORAN KEUANGAN
        Route::get('/laporan-keuangan', [LaporanKeuanganController::class, 'index'])->name('laporan-keuangan.index');
        Route::get('/laporan-keuangan/harian', [LaporanKeuanganController::class, 'harian'])->name('laporan-keuangan.harian');
        Route::get('/laporan-keuangan/bulanan', [LaporanKeuanganController::class, 'bulanan'])->name('laporan-keuangan.bulanan');
        Route::get('/laporan-keuangan/rekap-bulanan', [LaporanKeuanganController::class, 'rekapBulanan'])->name('laporan-keuangan.rekap-bulanan');
        Route::get('/laporan-keuangan/export/excel', [LaporanKeuanganController::class, 'exportExcel'])->name('laporan-keuangan.export.excel');
        Route::get('/laporan-keuangan/export/pdf', [LaporanKeuanganController::class, 'exportPdf'])->name('laporan-keuangan.export.pdf');


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
            Route::get('/pembayaran/export/excel', [PembayaranController::class, 'exportExcel'])->name('pembayaran.export.excel');
            Route::get('/pembayaran/export/outstanding.pdf', [PembayaranController::class, 'exportOutstandingPdf'])->name('pembayaran.export.outstanding.pdf');
            Route::get('/pembayaran/export/outstanding.xlsx', [PembayaranController::class, 'exportOutstandingExcel'])->name('pembayaran.export.outstanding.excel');
            Route::post('/pembayaran/massal', [PembayaranController::class, 'massStore'])->name('pembayaran.mass.store');
            Route::post('/pembayaran/notify-due-bulk', [PembayaranController::class, 'notifyDueBulk'])->name('pembayaran.notify-due-bulk');
            Route::post('/pembayaran/{payment}/mark-lunas', [PembayaranController::class, 'markLunas'])->name('pembayaran.mark-lunas');
            Route::delete('/pembayaran/{payment}', [PembayaranController::class, 'destroy'])->name('pembayaran.destroy');
        });
    });

});
