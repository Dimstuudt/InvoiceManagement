<?php

use App\Http\Controllers\Settings\NotificationEmailController;
use Illuminate\Support\Facades\Route;

Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('notification-emails', [NotificationEmailController::class, 'index'])->name('notification-emails.index');
    Route::post('notification-emails', [NotificationEmailController::class, 'store'])->name('notification-emails.store');
    Route::patch('notification-emails/{notificationEmail}/toggle', [NotificationEmailController::class, 'toggle'])->name('notification-emails.toggle');
    Route::patch('notification-emails/{notificationEmail}/set-type', [NotificationEmailController::class, 'setType'])->name('notification-emails.set-type');
    Route::delete('notification-emails/{notificationEmail}', [NotificationEmailController::class, 'destroy'])->name('notification-emails.destroy');
});
