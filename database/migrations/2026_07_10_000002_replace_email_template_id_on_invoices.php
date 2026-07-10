<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'email_template_id')) {
                $table->dropForeign(['email_template_id']);
                $table->dropColumn('email_template_id');
            }

            $table->foreignId('email_template_group_id')
                ->nullable()
                ->after('send_status')
                ->constrained('email_template_groups')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['email_template_group_id']);
            $table->dropColumn('email_template_group_id');

            $table->foreignId('email_template_id')
                ->nullable()
                ->after('status')
                ->constrained('email_templates')
                ->nullOnDelete();
        });
    }
};
