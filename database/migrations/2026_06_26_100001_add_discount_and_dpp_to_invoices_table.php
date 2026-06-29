<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('discount_type', ['percent', 'amount'])->nullable()->after('tax_percentage');
            $table->decimal('discount_value', 15, 2)->nullable()->after('discount_type');
            $table->boolean('is_dpp')->default(false)->after('discount_value');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value', 'is_dpp']);
        });
    }
};
