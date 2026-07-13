<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SpkController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Master\BankAccountController;
use App\Http\Controllers\Master\ClientCategoryController;
use App\Http\Controllers\Master\DocumentIssuerController;
use App\Http\Controllers\Master\EmailTemplateGroupController;
use App\Http\Controllers\Master\CompanyController;
use App\Http\Controllers\Master\ProjectCategoryController;
use App\Http\Controllers\Master\SignatureController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ArtisanController;
use App\Http\Controllers\CronController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// Panduan penggunaan — static, no auth required
Route::get('/artifact-design', fn() => view('artifact-design'))->name('artifact-design');

// Public cron trigger — no auth, protected by secret query param
Route::get('/cron/run', [CronController::class, 'run'])->name('cron.run');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

// App
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // 2FA routes — no 2fa middleware, accessible right after password auth
    Route::get('/2fa/setup',      [TwoFactorController::class, 'setup'])->name('2fa.setup');
    Route::post('/2fa/setup',     [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::get('/2fa/challenge',  [TwoFactorController::class, 'challenge'])->name('2fa.challenge');
    Route::post('/2fa/challenge', [TwoFactorController::class, 'verify'])->name('2fa.verify');

    // All routes below require 2FA to be verified
    Route::middleware('2fa')->group(function () {
        Route::delete('/2fa', [TwoFactorController::class, 'disable'])->name('2fa.disable');

        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');

        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

        Route::get('logs', [ActivityLogController::class, 'index'])->name('logs.index');
        Route::get('logs/cron', [ActivityLogController::class, 'cronLogs'])->name('logs.cron');
        Route::delete('logs/cron', [ActivityLogController::class, 'deleteCronLogs'])->name('logs.cron.delete');

        Route::resource('users', UserController::class)->except(['show'])->middleware('admin');

        // Admin-only panels
        Route::middleware('admin')->group(function () {
            Route::get('/admin/artisan', [ArtisanController::class, 'dashboard'])->name('admin.artisan');
            Route::post('/admin/artisan/run', [ArtisanController::class, 'run'])->name('admin.artisan.run');

            Route::get('/admin/cron', [CronController::class, 'dashboard'])->name('admin.cron');
            Route::post('/admin/cron/run', [CronController::class, 'manualRun'])->name('admin.cron.run');
            Route::post('/admin/cron/demo', [CronController::class, 'createDemo'])->name('admin.cron.demo');
        });

        Route::resource('clients', ClientController::class)->except(['show']);
        Route::patch('clients/{client}/emails/{email}/toggle', [ClientController::class, 'toggleEmail'])->name('clients.email.toggle');

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

        Route::get('invoices/number-preview', [InvoiceController::class, 'numberPreview'])->name('invoices.number-preview');
        Route::get('invoices/numbering/export', [InvoiceController::class, 'numberingExport'])->name('invoices.numbering.export');
        Route::get('invoices/numbering', [InvoiceController::class, 'numbering'])->name('invoices.numbering');
        Route::get('invoices/export', [InvoiceController::class, 'export'])->name('invoices.export');
        Route::get('invoices/schedule', [InvoiceController::class, 'schedule'])->name('invoices.schedule');
        Route::get('invoices/clients/{client}', [InvoiceController::class, 'clientInvoices'])->name('invoices.client');
        Route::delete('invoices/reset-all', [InvoiceController::class, 'resetAll'])->name('invoices.reset-all');
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
        Route::post('invoices/{invoice}/freeze', [InvoiceController::class, 'freeze'])->name('invoices.freeze');
        Route::post('invoices/{invoice}/resume', [InvoiceController::class, 'resume'])->name('invoices.resume');
        Route::post('invoices/{invoice}/carry', [InvoiceController::class, 'carry'])->name('invoices.carry');
        Route::post('invoices/{invoice}/prepay', [InvoiceController::class, 'prepay'])->name('invoices.prepay');
        Route::post('invoices/{invoice}/reactivate', [InvoiceController::class, 'reactivate'])->name('invoices.reactivate');
        Route::post('invoices/{invoice}/rollback-reaktivasi', [InvoiceController::class, 'rollbackReaktivasi'])->name('invoices.rollbackReaktivasi');

        // Master Data
        Route::prefix('master')->name('master.')->group(function () {
            Route::resource('companies', CompanyController::class)->except(['show', 'create', 'edit']);
            Route::patch('companies/{company}/assign', [CompanyController::class, 'assign'])->name('companies.assign');
            Route::resource('client-categories', ClientCategoryController::class)->except(['show']);
            Route::resource('project-categories', ProjectCategoryController::class)->except(['show']);
            Route::resource('document-issuers', DocumentIssuerController::class)->except(['show']);
            Route::resource('bank-accounts', BankAccountController::class)->except(['show']);
            Route::resource('signatures', SignatureController::class)->except(['show']);
            Route::get('email-template-groups/preview-wrapper/{type}', [EmailTemplateGroupController::class, 'previewWrapper'])->name('email-template-groups.preview-wrapper');
            Route::resource('email-template-groups', EmailTemplateGroupController::class)->except(['show']);
        });
    });
});
