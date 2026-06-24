<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Signature extends Model
{
    protected $fillable = ['name', 'position', 'signature_image'];
    protected $appends  = ['signature_image_url'];

    public function getSignatureImageUrlAttribute(): ?string
    {
        return $this->signature_image ? Storage::url($this->signature_image) : null;
    }
}
