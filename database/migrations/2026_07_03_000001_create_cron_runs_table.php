<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cron_runs', function (Blueprint $table) {
            $table->id();
            $table->string('triggered_by')->default('schedule'); // schedule | http | manual
            $table->unsignedInteger('invoices_found')->default(0);
            $table->unsignedInteger('invoices_sent')->default(0);
            $table->unsignedInteger('invoices_failed')->default(0);
            $table->json('details')->nullable();
            $table->string('status')->default('empty'); // success | partial | error | empty
            $table->text('error')->nullable();
            $table->unsignedInteger('duration_ms')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cron_runs');
    }
};
