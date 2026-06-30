<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop existing FK constraints
            $table->dropForeign(['project_category_id']);
            $table->dropForeign(['document_issuer_id']);
            $table->dropForeign(['bank_account_id']);

            // Make columns nullable so nullOnDelete works
            $table->unsignedBigInteger('project_category_id')->nullable()->change();
            $table->unsignedBigInteger('document_issuer_id')->nullable()->change();
            $table->unsignedBigInteger('bank_account_id')->nullable()->change();

            // Re-add FK with nullOnDelete — master data dihapus, invoice tetap ada
            $table->foreign('project_category_id')->references('id')->on('project_categories')->nullOnDelete();
            $table->foreign('document_issuer_id')->references('id')->on('document_issuers')->nullOnDelete();
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['project_category_id']);
            $table->dropForeign(['document_issuer_id']);
            $table->dropForeign(['bank_account_id']);

            $table->unsignedBigInteger('project_category_id')->nullable(false)->change();
            $table->unsignedBigInteger('document_issuer_id')->nullable(false)->change();
            $table->unsignedBigInteger('bank_account_id')->nullable(false)->change();

            $table->foreign('project_category_id')->references('id')->on('project_categories')->cascadeOnDelete();
            $table->foreign('document_issuer_id')->references('id')->on('document_issuers')->cascadeOnDelete();
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->cascadeOnDelete();
        });
    }
};
