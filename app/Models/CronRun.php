<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CronRun extends Model
{
    protected $fillable = [
        'triggered_by', 'invoices_found', 'invoices_sent',
        'invoices_failed', 'details', 'status', 'error', 'duration_ms',
    ];

    protected $casts = [
        'details' => 'array',
    ];
}
