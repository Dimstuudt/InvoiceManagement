<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_issuers', function (Blueprint $table) {
            $table->foreignId('sender_domain_id')->nullable()->constrained('sender_domains')->nullOnDelete()->after('tax_id_number');
        });
    }

    public function down(): void
    {
        Schema::table('document_issuers', function (Blueprint $table) {
            $table->dropForeign(['sender_domain_id']);
            $table->dropColumn('sender_domain_id');
        });
    }
};
