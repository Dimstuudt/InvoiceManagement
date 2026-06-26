<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedTinyInteger('interval_months')->nullable()->after('status');
            $table->foreignId('parent_invoice_id')->nullable()->constrained('invoices')->nullOnDelete()->after('interval_months');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['parent_invoice_id']);
            $table->dropColumn(['interval_months', 'parent_invoice_id']);
        });
    }
};
