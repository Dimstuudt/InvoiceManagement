<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotificationEmail extends Model
{
    protected $fillable = ['user_id', 'email', 'label', 'is_active', 'is_default'];

    protected $casts = [
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
