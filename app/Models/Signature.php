<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Signature extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'position', 'signature_image'];
    protected $appends  = ['signature_image_url'];

    protected static function boot()
    {
        parent::boot();
        static::forceDeleting(function (Signature $model) {
            if ($model->signature_image) Storage::disk('public')->delete($model->signature_image);
        });
    }

    public function getSignatureImageUrlAttribute(): ?string
    {
        return $this->signature_image ? Storage::url($this->signature_image) : null;
    }
}
