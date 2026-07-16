<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spks', function (Blueprint $table) {
            $table->unsignedBigInteger('invoice_id')->nullable()->after('client_id');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
        });

        // Backfill: record dengan key 'inv-{id}' → set invoice_id dari angkanya
        DB::statement("
            UPDATE spks
            SET invoice_id = CAST(SUBSTRING(spk_number, 5) AS UNSIGNED)
            WHERE spk_number LIKE 'inv-%'
        ");

        // Backfill: record dengan spk_number nyata → join ke invoices
        DB::statement("
            UPDATE spks s
            JOIN invoices i ON i.client_id = s.client_id AND i.spk_number = s.spk_number
            SET s.invoice_id = i.id
            WHERE s.invoice_id IS NULL
              AND s.spk_number NOT LIKE 'inv-%'
        ");
    }

    public function down(): void
    {
        Schema::table('spks', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn('invoice_id');
        });
    }
};
