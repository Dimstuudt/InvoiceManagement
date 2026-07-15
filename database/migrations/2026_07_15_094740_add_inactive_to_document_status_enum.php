<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE invoices MODIFY COLUMN document_status ENUM('draft','verified','frozen','carried','inactive') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE invoices MODIFY COLUMN document_status ENUM('draft','verified','frozen','carried') NOT NULL DEFAULT 'draft'");
    }
};
