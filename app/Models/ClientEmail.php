<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientEmail extends Model
{
    public $timestamps = false;
    protected $fillable = ['client_id', 'email'];
}
