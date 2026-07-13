<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $users = DB::table('users')->get(['id']);
        foreach ($users as $user) {
            $exists = DB::table('user_notification_emails')
                ->where('user_id', $user->id)
                ->where('is_default', true)
                ->exists();
            if (!$exists) {
                DB::table('user_notification_emails')->insert([
                    'user_id'    => $user->id,
                    'email'      => null,
                    'label'      => 'This User',
                    'is_active'  => true,
                    'is_default' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void {}
};
