<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplateGroup extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name', 'is_default',
        'subject_send1', 'body_send1',
        'subject_send2', 'body_send2',
        'subject_send3', 'body_send3',
        'subject_send4', 'body_send4',
        'subject_send5', 'body_send5',
        'subject_paid',  'body_paid',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /** Ambil subject & body untuk stage tertentu (send1–send5 atau paid). */
    public function getStage(string $stage): array
    {
        return [
            'subject' => $this->{"subject_{$stage}"} ?? '',
            'body'    => $this->{"body_{$stage}"}    ?? '',
        ];
    }
}
