<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientSocialMedia extends Model
{
    public $timestamps = false;
    protected $fillable = ['client_id', 'url'];
}
