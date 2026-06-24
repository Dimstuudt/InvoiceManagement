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
        Schema::create('document_issuers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('header_image')->nullable();
            $table->string('tax_id_name');
            $table->text('tax_id_address');
            $table->string('tax_id_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_issuers');
    }
};
