<?php

use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpkController;

Route::resource('clients', ClientController::class);
Route::patch('clients/{client}/emails/{email}/toggle', [ClientController::class, 'toggleEmail'])->name('clients.email.toggle');
Route::patch('clients/{client}/update-status', [ClientController::class, 'updateStatus'])->name('clients.update-status');

Route::get('spk', [SpkController::class, 'index'])->name('spk.index');
Route::get('spk/client/{client}', [SpkController::class, 'clientSpks'])->name('spk.client');
Route::post('spk/parse', [SpkController::class, 'parse'])->name('spk.parse');
Route::post('spk/parse-local', [SpkController::class, 'parseLocal'])->name('spk.parse-local');
Route::post('spk/store', [SpkController::class, 'store'])->name('spk.store');
Route::patch('spk/invoice/{invoice}/number', [SpkController::class, 'updateSpkNumber'])->name('spk.update-number');
Route::post('spk/invoice/{invoice}/upload-file', [SpkController::class, 'uploadSpkFile'])->name('spk.upload-file');
