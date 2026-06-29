<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps  = false;
    public $updatable   = false;

    protected $fillable = [
        'user_id', 'action', 'subject_type', 'subject_id', 'subject_label', 'detail',
        'ip_address', 'user_agent',
    ];

    protected $casts = [
        'detail'     => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
