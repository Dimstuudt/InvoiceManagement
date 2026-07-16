<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SecurityGateController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CronController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/artifact-design', fn() => view('artifact-design'))->name('artifact-design');

Route::get('/cron/run', [CronController::class, 'run'])->name('cron.run');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');
});

// App
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/2fa/setup',      [TwoFactorController::class, 'setup'])->name('2fa.setup');
    Route::post('/2fa/setup',     [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::get('/2fa/challenge',  [TwoFactorController::class, 'challenge'])->name('2fa.challenge');
    Route::post('/2fa/challenge', [TwoFactorController::class, 'verify'])->name('2fa.verify')->middleware('throttle:2fa');

    Route::middleware('2fa')->group(function () {
        Route::delete('/2fa', [TwoFactorController::class, 'disable'])->name('2fa.disable');

        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('notifications/page', [NotificationController::class, 'page'])->name('notifications.page');

        Route::get('logs', [ActivityLogController::class, 'index'])->name('logs.index');
        Route::get('logs/cron', [ActivityLogController::class, 'cronLogs'])->name('logs.cron');
        Route::delete('logs/cron', [ActivityLogController::class, 'deleteCronLogs'])->name('logs.cron.delete');

        Route::resource('users', UserController::class)->except(['show'])->middleware('admin');

        Route::post('/security/activate-bypass', [SecurityGateController::class, 'activateBypass'])->name('security.activate-bypass');
        Route::post('/security/deactivate-bypass', [SecurityGateController::class, 'deactivateBypass'])->name('security.deactivate-bypass');
        Route::post('/security/verify-gate', [SecurityGateController::class, 'verifyGate'])->name('security.verify-gate');

        require __DIR__ . '/invoice.php';
        require __DIR__ . '/client.php';
        require __DIR__ . '/master.php';
        require __DIR__ . '/admin.php';
        require __DIR__ . '/settings.php';
    });
});
