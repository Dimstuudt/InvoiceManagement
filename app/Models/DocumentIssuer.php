<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DocumentIssuer extends Model
{
    protected $fillable = ['name', 'header_image', 'tax_id_name', 'tax_id_address', 'tax_id_number'];
    protected $appends  = ['header_image_url'];

    public function getHeaderImageUrlAttribute(): ?string
    {
        return $this->header_image ? Storage::url($this->header_image) : null;
    }
}
