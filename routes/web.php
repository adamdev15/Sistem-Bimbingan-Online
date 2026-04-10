<?php

use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\NotificationBellController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $settings = \App\Models\Setting::pluck('value', 'setting_key')->toArray();
    $programs = \App\Models\MateriLes::all();
    return view('welcome', compact('settings', 'programs'));
})->name('landing');

Route::post('/midtrans/notification', [MidtransWebhookController::class, 'handle'])
    ->middleware('throttle:120,1')
    ->name('midtrans.notification');

require __DIR__.'/roles/super_admin.php';

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/notifications/{userNotification}/read', [NotificationBellController::class, 'markRead'])
        ->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationBellController::class, 'markAllRead'])
        ->name('notifications.read-all');
});

require __DIR__.'/auth.php';
