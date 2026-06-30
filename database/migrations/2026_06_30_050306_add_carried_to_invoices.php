<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft','sent','paid','unpaid','frozen','carried') NOT NULL DEFAULT 'draft'");

        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('carried_from_id')->nullable()->after('parent_invoice_id');
            $table->foreign('carried_from_id')->references('id')->on('invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['carried_from_id']);
            $table->dropColumn('carried_from_id');
        });

        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft','sent','paid','unpaid','frozen') NOT NULL DEFAULT 'draft'");
    }
};
