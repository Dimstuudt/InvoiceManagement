<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SenderDomain extends Model
{
    use SoftDeletes;
    protected $fillable = ['display_name', 'prefix', 'domain'];

    public function getSenderEmailAttribute(): string
    {
        return $this->prefix . '@' . $this->domain;
    }
}
