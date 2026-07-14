<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sender_domains', function (Blueprint $table) {
            $table->id();
            $table->string('display_name');
            $table->string('prefix');
            $table->string('domain');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sender_domains');
    }
};
