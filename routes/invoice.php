<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

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
Route::post('invoices/{invoice}/deactivate', [InvoiceController::class, 'deactivate'])->name('invoices.deactivate');
Route::post('invoices/{invoice}/reactivate', [InvoiceController::class, 'reactivate'])->name('invoices.reactivate');
Route::post('invoices/{invoice}/rollback-reaktivasi', [InvoiceController::class, 'rollbackReaktivasi'])->name('invoices.rollbackReaktivasi');
