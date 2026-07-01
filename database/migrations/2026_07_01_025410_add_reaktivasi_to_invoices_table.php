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
            $table->boolean('is_reaktivasi')->default(false)->after('carried_from_id');
            $table->foreignId('reaktivasi_chain_id')->nullable()->after('is_reaktivasi')
                ->constrained('invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['reaktivasi_chain_id']);
            $table->dropColumn(['is_reaktivasi', 'reaktivasi_chain_id']);
        });
    }
};
