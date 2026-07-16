<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Hapus Spk orphan (tanpa invoice_id) — file fisik + record
        $orphans = DB::table('spks')->whereNull('invoice_id')->get();
        foreach ($orphans as $spk) {
            if ($spk->file_path) {
                Storage::disk('public')->delete($spk->file_path);
            }
        }
        DB::table('spks')->whereNull('invoice_id')->delete();

        // 2. Ganti FK dari set null → cascade
        Schema::table('spks', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->foreign('invoice_id')
                  ->references('id')->on('invoices')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('spks', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->foreign('invoice_id')
                  ->references('id')->on('invoices')
                  ->onDelete('set null');
        });
    }
};
