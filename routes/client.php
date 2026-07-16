<?php

use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpkController;

Route::resource('clients', ClientController::class);
Route::patch('clients/{client}/emails/{email}/toggle', [ClientController::class, 'toggleEmail'])->name('clients.email.toggle');
Route::patch('clients/{client}/update-status', [ClientController::class, 'updateStatus'])->name('clients.update-status');

Route::get('spk', [SpkController::class, 'index'])->name('spk.index');
Route::get('spk/create', [SpkController::class, 'create'])->name('spk.create');
Route::get('spk/create-local', [SpkController::class, 'createLocal'])->name('spk.create-local');
Route::get('spk/create-ollama', [SpkController::class, 'createOllama'])->name('spk.create-ollama');
Route::get('spk/number-preview', [SpkController::class, 'numberPreview'])->name('spk.number-preview');
Route::post('spk/parse', [SpkController::class, 'parse'])->name('spk.parse');
Route::post('spk/parse-local', [SpkController::class, 'parseLocal'])->name('spk.parse-local');
Route::post('spk/store', [SpkController::class, 'store'])->name('spk.store');
Route::post('spk/store-with-client', [SpkController::class, 'storeWithClient'])->name('spk.store-with-client');
Route::get('spk/client/{client}', [SpkController::class, 'clientSpks'])->name('spk.client');
Route::delete('spk/{spk}', [SpkController::class, 'destroy'])->name('spk.destroy');
