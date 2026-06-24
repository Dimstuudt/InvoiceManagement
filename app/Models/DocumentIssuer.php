<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentIssuer extends Model
{
    protected $fillable = ['name', 'header_image', 'tax_id_name', 'tax_id_address', 'tax_id_number'];
}
