<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'project_categories',
            'client_categories',
            'bank_accounts',
            'document_issuers',
            'signatures',
            'email_template_groups',
            'sender_domains',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'project_categories',
            'client_categories',
            'bank_accounts',
            'document_issuers',
            'signatures',
            'email_template_groups',
            'sender_domains',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
