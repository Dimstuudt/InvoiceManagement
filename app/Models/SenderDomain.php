<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SenderDomain extends Model
{
    protected $fillable = ['display_name', 'prefix', 'domain'];

    public function getSenderEmailAttribute(): string
    {
        return $this->prefix . '@' . $this->domain;
    }
}
