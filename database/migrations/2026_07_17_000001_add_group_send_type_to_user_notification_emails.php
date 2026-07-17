<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_notification_emails', function (Blueprint $table) {
            $table->unsignedTinyInteger('group_id')->default(1)->after('is_default');
            $table->enum('send_type', ['to', 'cc'])->default('to')->after('group_id');
        });

        // Existing data: semua masuk group 1
        // is_default = true → TO, sisanya → CC (preserve perilaku lama)
        DB::table('user_notification_emails')->update(['group_id' => 1]);
        DB::table('user_notification_emails')->where('is_default', true)->update(['send_type' => 'to']);
        DB::table('user_notification_emails')->where('is_default', false)->update(['send_type' => 'cc']);
    }

    public function down(): void
    {
        Schema::table('user_notification_emails', function (Blueprint $table) {
            $table->dropColumn(['group_id', 'send_type']);
        });
    }
};
