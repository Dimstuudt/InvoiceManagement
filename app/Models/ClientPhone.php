<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPhone extends Model
{
    public $timestamps = false;
    protected $fillable = ['client_id', 'phone_number'];
}
