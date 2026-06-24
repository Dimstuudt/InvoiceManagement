<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Master\BankAccountController;
use App\Http\Controllers\Master\ClientCategoryController;
use App\Http\Controllers\Master\DocumentIssuerController;
use App\Http\Controllers\Master\ProjectCategoryController;
use App\Http\Controllers\Master\SignatureController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('/', '/login');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

// App
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('users', UserController::class)->except(['show'])->middleware('admin');

    Route::resource('clients', ClientController::class)->except(['show']);

    Route::resource('invoices', InvoiceController::class);
    Route::patch('invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.status');
    Route::patch('invoices/{invoice}/items', [InvoiceController::class, 'updateItems'])->name('invoices.items');

    // Master Data
    Route::prefix('master')->name('master.')->group(function () {
        Route::resource('client-categories', ClientCategoryController::class)->except(['show']);
        Route::resource('project-categories', ProjectCategoryController::class)->except(['show']);
        Route::resource('document-issuers', DocumentIssuerController::class)->except(['show']);
        Route::resource('bank-accounts', BankAccountController::class)->except(['show']);
        Route::resource('signatures', SignatureController::class)->except(['show']);
    });
});
