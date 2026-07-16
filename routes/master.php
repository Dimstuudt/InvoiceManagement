<?php

use App\Http\Controllers\Master\BankAccountController;
use App\Http\Controllers\Master\TrashController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Master\ClientCategoryController;
use App\Http\Controllers\Master\CompanyController;
use App\Http\Controllers\Master\DocumentIssuerController;
use App\Http\Controllers\Master\EmailTemplateGroupController;
use App\Http\Controllers\Master\ProjectCategoryController;
use App\Http\Controllers\Master\SenderDomainController;
use App\Http\Controllers\Master\SignatureController;

Route::prefix('master')->name('master.')->group(function () {
    Route::resource('companies', CompanyController::class)->except(['show', 'create', 'edit']);
    Route::patch('companies/{company}/assign', [CompanyController::class, 'assign'])->name('companies.assign');

    // Bulk destroy harus di atas resource agar tidak konflik dengan route model binding
    Route::delete('client-categories/bulk-destroy', [ClientCategoryController::class, 'bulkDestroy'])->name('client-categories.bulk-destroy');
    Route::resource('client-categories', ClientCategoryController::class)->except(['show']);

    Route::delete('project-categories/bulk-destroy', [ProjectCategoryController::class, 'bulkDestroy'])->name('project-categories.bulk-destroy');
    Route::resource('project-categories', ProjectCategoryController::class)->except(['show']);

    Route::delete('document-issuers/bulk-destroy', [DocumentIssuerController::class, 'bulkDestroy'])->name('document-issuers.bulk-destroy');
    Route::resource('document-issuers', DocumentIssuerController::class)->except(['show']);

    Route::delete('bank-accounts/bulk-destroy', [BankAccountController::class, 'bulkDestroy'])->name('bank-accounts.bulk-destroy');
    Route::resource('bank-accounts', BankAccountController::class)->except(['show']);

    Route::delete('sender-domains/bulk-destroy', [SenderDomainController::class, 'bulkDestroy'])->name('sender-domains.bulk-destroy');
    Route::resource('sender-domains', SenderDomainController::class)->except(['show', 'create', 'edit']);

    Route::delete('signatures/bulk-destroy', [SignatureController::class, 'bulkDestroy'])->name('signatures.bulk-destroy');
    Route::resource('signatures', SignatureController::class)->except(['show']);

    Route::get('email-template-groups/preview-wrapper/{type}', [EmailTemplateGroupController::class, 'previewWrapper'])->name('email-template-groups.preview-wrapper');
    Route::delete('email-template-groups/bulk-destroy', [EmailTemplateGroupController::class, 'bulkDestroy'])->name('email-template-groups.bulk-destroy');
    Route::resource('email-template-groups', EmailTemplateGroupController::class)->except(['show']);

    // Trash — operasi per tipe
    Route::post('trash/{type}/{id}/restore', [TrashController::class, 'restore'])->name('trash.restore');
    Route::delete('trash/{type}/{id}/force-delete', [TrashController::class, 'forceDelete'])->name('trash.force-delete');
    Route::post('trash/{type}/bulk-restore', [TrashController::class, 'bulkRestore'])->name('trash.bulk-restore');
    Route::delete('trash/{type}/bulk-force-delete', [TrashController::class, 'bulkForceDelete'])->name('trash.bulk-force-delete');
});
