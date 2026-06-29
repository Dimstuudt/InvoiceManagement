<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Master\BankAccountController;
use App\Http\Controllers\Master\ClientCategoryController;
use App\Http\Controllers\Master\DocumentIssuerController;
use App\Http\Controllers\Master\EmailTemplateController;
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

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('logs', [ActivityLogController::class, 'index'])->name('logs.index');

    Route::resource('users', UserController::class)->except(['show'])->middleware('admin');

    Route::resource('clients', ClientController::class)->except(['show']);

    Route::get('invoices/schedule', [InvoiceController::class, 'schedule'])->name('invoices.schedule');
    Route::get('invoices/clients/{client}', [InvoiceController::class, 'clientInvoices'])->name('invoices.client');
    Route::resource('invoices', InvoiceController::class);
    Route::patch('invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.status');
    Route::patch('invoices/{invoice}/save', [InvoiceController::class, 'saveAll'])->name('invoices.save');
    Route::patch('invoices/{invoice}/items', [InvoiceController::class, 'updateItems'])->name('invoices.items');
    Route::patch('invoices/{invoice}/mark', [InvoiceController::class, 'toggleMark'])->name('invoices.mark');
    Route::patch('invoices/{invoice}/interval', [InvoiceController::class, 'updateInterval'])->name('invoices.interval');
    Route::patch('invoices/{invoice}/meta', [InvoiceController::class, 'updateMeta'])->name('invoices.meta');
    Route::patch('invoices/{invoice}/tax', [InvoiceController::class, 'updateTax'])->name('invoices.tax');
    Route::patch('invoices/{invoice}/totals', [InvoiceController::class, 'updateTotals'])->name('invoices.totals');
    Route::get('invoices/{invoice}/receipt', [InvoiceController::class, 'receipt'])->name('invoices.receipt');
    Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::get('invoices/{invoice}/print-view', [InvoiceController::class, 'printView'])->name('invoices.print-view');
    Route::post('invoices/{invoice}/send-email', [InvoiceController::class, 'sendEmail'])->name('invoices.send-email');

    // Master Data
    Route::prefix('master')->name('master.')->group(function () {
        Route::resource('client-categories', ClientCategoryController::class)->except(['show']);
        Route::resource('project-categories', ProjectCategoryController::class)->except(['show']);
        Route::resource('document-issuers', DocumentIssuerController::class)->except(['show']);
        Route::resource('bank-accounts', BankAccountController::class)->except(['show']);
        Route::resource('signatures', SignatureController::class)->except(['show']);
        Route::resource('email-templates', EmailTemplateController::class)->except(['show']);
    });
});
