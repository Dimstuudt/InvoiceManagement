<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('is_prepay')->default(false)->after('is_reaktivasi');
            $table->unsignedBigInteger('prepay_chain_id')->nullable()->after('is_prepay');
            $table->foreign('prepay_chain_id')->references('id')->on('invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['prepay_chain_id']);
            $table->dropColumn(['is_prepay', 'prepay_chain_id']);
        });
    }
};
