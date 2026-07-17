<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotificationEmail extends Model
{
    protected $fillable = ['user_id', 'email', 'label', 'is_active', 'is_default', 'group_id', 'send_type'];

    protected $casts = [
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
        'group_id'   => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
