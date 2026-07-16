<?php

use App\Http\Controllers\Master\BankAccountController;
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
    Route::resource('client-categories', ClientCategoryController::class)->except(['show']);
    Route::resource('project-categories', ProjectCategoryController::class)->except(['show']);
    Route::resource('document-issuers', DocumentIssuerController::class)->except(['show']);
    Route::resource('bank-accounts', BankAccountController::class)->except(['show']);
    Route::resource('sender-domains', SenderDomainController::class)->except(['show', 'create', 'edit']);
    Route::resource('signatures', SignatureController::class)->except(['show']);
    Route::get('email-template-groups/preview-wrapper/{type}', [EmailTemplateGroupController::class, 'previewWrapper'])->name('email-template-groups.preview-wrapper');
    Route::resource('email-template-groups', EmailTemplateGroupController::class)->except(['show']);
});
