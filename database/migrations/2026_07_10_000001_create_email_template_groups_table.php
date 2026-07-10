<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_template_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_default')->default(false);

            $table->string('subject_send1')->nullable();
            $table->text('body_send1')->nullable();

            $table->string('subject_send2')->nullable();
            $table->text('body_send2')->nullable();

            $table->string('subject_send3')->nullable();
            $table->text('body_send3')->nullable();

            $table->string('subject_send4')->nullable();
            $table->text('body_send4')->nullable();

            $table->string('subject_send5')->nullable();
            $table->text('body_send5')->nullable();

            $table->string('subject_paid')->nullable();
            $table->text('body_paid')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_template_groups');
    }
};
