<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class BankAccount extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'account_number', 'bank_name', 'bank_logo'];
    protected $appends  = ['bank_logo_url'];

    protected static function boot()
    {
        parent::boot();
        static::forceDeleting(function (BankAccount $model) {
            if ($model->bank_logo) Storage::disk('public')->delete($model->bank_logo);
        });
    }

    public function getBankLogoUrlAttribute(): ?string
    {
        return $this->bank_logo ? Storage::url($this->bank_logo) : null;
    }
}
