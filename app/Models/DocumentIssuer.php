<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class DocumentIssuer extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'header_image', 'tax_id_name', 'tax_id_address', 'tax_id_number', 'sender_domain_id'];

    protected static function boot()
    {
        parent::boot();
        static::forceDeleting(function (DocumentIssuer $model) {
            if ($model->header_image) Storage::disk('public')->delete($model->header_image);
        });
    }

    public function senderDomain()
    {
        return $this->belongsTo(SenderDomain::class);
    }
    protected $appends  = ['header_image_url'];

    public function getHeaderImageUrlAttribute(): ?string
    {
        return $this->header_image ? Storage::url($this->header_image) : null;
    }
}
