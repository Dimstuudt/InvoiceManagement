<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('payment_status',  ['unpaid', 'paid'])
                  ->default('unpaid')->after('status');
            $table->enum('document_status', ['draft', 'verified', 'frozen', 'carried'])
                  ->default('draft')->after('payment_status');
            $table->enum('send_status',     ['unsent', 'send1', 'send2', 'send3', 'send4', 'send5'])
                  ->default('unsent')->after('document_status');
        });

        // Migrate existing data
        DB::statement("
            UPDATE invoices SET
                payment_status  = CASE
                    WHEN status = 'paid'    THEN 'paid'
                    ELSE                         'unpaid'
                END,
                document_status = CASE
                    WHEN status = 'frozen'  THEN 'frozen'
                    WHEN status = 'carried' THEN 'carried'
                    WHEN status = 'draft' AND is_marked = 0 THEN 'draft'
                    ELSE                         'verified'
                END,
                send_status     = CASE
                    WHEN status IN ('frozen', 'carried') THEN 'unsent'
                    WHEN status = 'draft' AND is_marked = 0 THEN 'unsent'
                    WHEN status IN ('sent', 'paid', 'unpaid') THEN 'send1'
                    ELSE 'unsent'
                END
        ");

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['status', 'is_marked']);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('status', ['draft', 'sent', 'paid', 'unpaid', 'frozen', 'carried'])
                  ->default('draft')->after('notes');
            $table->boolean('is_marked')->default(false)->after('status');
        });

        DB::statement("
            UPDATE invoices SET
                status    = CASE
                    WHEN document_status = 'frozen'   THEN 'frozen'
                    WHEN document_status = 'carried'  THEN 'carried'
                    WHEN payment_status  = 'paid'     THEN 'paid'
                    WHEN send_status     = 'unsent'   THEN 'draft'
                    ELSE                                   'sent'
                END,
                is_marked = CASE
                    WHEN document_status = 'verified' AND send_status = 'unsent' THEN 1
                    ELSE 0
                END
        ");

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'document_status', 'send_status']);
        });
    }
};
