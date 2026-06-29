<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    public $timestamps = false;

    protected $fillable = ['invoice_id', 'description', 'amount', 'discount', 'sort_order'];

    protected $casts = ['amount' => 'float', 'discount' => 'float'];

    public function invoice() { return $this->belongsTo(Invoice::class); }

    public function getItemTotalAttribute(): float
    {
        return max(0.0, (float) $this->amount - (float) ($this->discount ?? 0));
    }
}
