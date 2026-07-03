<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-send invoice yang sudah ditandai dan sudah melewati tanggal terbit
Schedule::command('invoice:auto-send --triggered-by=schedule')
    ->everyTwoMinutes()
    ->withoutOverlapping()
    ->runInBackground();
