<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft','sent','paid','unpaid','frozen') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft','sent','paid','unpaid') NOT NULL DEFAULT 'draft'");
    }
};
