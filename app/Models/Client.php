<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'client_category_id', 'company_name', 'address', 'city',
        'director', 'pic', 'npwp_image', 'client_status', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function category()
    {
        return $this->belongsTo(ClientCategory::class, 'client_category_id');
    }

    public function phones()
    {
        return $this->hasMany(ClientPhone::class);
    }

    public function socialMedia()
    {
        return $this->hasMany(ClientSocialMedia::class);
    }

    public function emails()
    {
        return $this->hasMany(ClientEmail::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class)->orderByDesc('issue_date');
    }
}
