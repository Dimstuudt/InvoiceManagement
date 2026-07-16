<?php

use App\Http\Controllers\ArtisanController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CronController;

Route::middleware('admin')->group(function () {
    Route::get('/admin/artisan', [ArtisanController::class, 'dashboard'])->name('admin.artisan');
    Route::post('/admin/artisan/run', [ArtisanController::class, 'run'])->name('admin.artisan.run');

    Route::get('/admin/cron', [CronController::class, 'dashboard'])->name('admin.cron');
    Route::post('/admin/cron/run', [CronController::class, 'manualRun'])->name('admin.cron.run');
    Route::post('/admin/cron/demo', [CronController::class, 'createDemo'])->name('admin.cron.demo');
});
