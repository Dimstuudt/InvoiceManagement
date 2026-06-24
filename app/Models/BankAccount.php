<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BankAccount extends Model
{
    protected $fillable = ['name', 'account_number', 'bank_name', 'bank_logo'];
    protected $appends  = ['bank_logo_url'];

    public function getBankLogoUrlAttribute(): ?string
    {
        return $this->bank_logo ? Storage::url($this->bank_logo) : null;
    }
}
