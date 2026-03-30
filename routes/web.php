<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('landing');

Route::middleware(['auth', 'verified', 'role:super_admin|admin_cabang|tutor|siswa'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:super_admin|admin_cabang'])->group(function () {
    Route::view('/cabang', 'modules.cabang.index')->name('cabang.index');
    Route::view('/siswa', 'modules.siswa.index')->name('siswa.index');
    Route::view('/tutors', 'modules.tutor.index')->name('tutors.index');
    Route::view('/laporan', 'modules.laporan.index')->name('laporan.index');
});

Route::middleware(['auth', 'verified', 'role:super_admin|admin_cabang|tutor|siswa'])->group(function () {
    Route::view('/jadwal', 'modules.jadwal.index')->name('jadwal.index');
    Route::view('/presensi', 'modules.presensi.index')->name('presensi.index');
});

Route::middleware(['auth', 'verified', 'role:super_admin|admin_cabang|siswa'])->group(function () {
    Route::view('/pembayaran', 'modules.pembayaran.index')->name('pembayaran.index');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
