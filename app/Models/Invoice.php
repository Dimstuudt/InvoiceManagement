<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'user_id', 'client_id', 'project_category_id', 'document_issuer_id',
        'bank_account_id', 'signature_id', 'email_template_id', 'with_signature', 'spk_number',
        'invoice_number', 'issue_date', 'due_date', 'attention', 'notes', 'status', 'is_marked',
        'tax_percentage', 'discount_type', 'discount_value', 'is_dpp',
        'interval_months', 'parent_invoice_id', 'carried_from_id',
        'is_reaktivasi', 'reaktivasi_chain_id', 'invoice_type',
    ];

    protected $casts = [
        'issue_date'         => 'date',
        'due_date'           => 'date',
        'with_signature'     => 'boolean',
        'is_marked'          => 'boolean',
        'is_dpp'             => 'boolean',
        'is_reaktivasi'      => 'boolean',
        'interval_months'    => 'integer',
        'discount_value'     => 'float',
    ];

    public function user()            { return $this->belongsTo(User::class); }
    public function client()          { return $this->belongsTo(Client::class); }
    public function projectCategory() { return $this->belongsTo(ProjectCategory::class); }
    public function documentIssuer()  { return $this->belongsTo(DocumentIssuer::class); }
    public function bankAccount()     { return $this->belongsTo(BankAccount::class); }
    public function signature()       { return $this->belongsTo(Signature::class); }
    public function emailTemplate()   { return $this->belongsTo(EmailTemplate::class); }
    public function items()           { return $this->hasMany(InvoiceItem::class)->orderBy('sort_order'); }
    public function parent()          { return $this->belongsTo(Invoice::class, 'parent_invoice_id'); }
    public function children()        { return $this->hasMany(Invoice::class, 'parent_invoice_id'); }
    public function carriedFrom()      { return $this->belongsTo(Invoice::class, 'carried_from_id'); }
    public function reaktivasiChain()  { return $this->hasMany(Invoice::class, 'reaktivasi_chain_id'); }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(function ($item) {
            return max(0.0, (float) $item->amount - (float) ($item->discount ?? 0));
        });
    }

    public function getDiscountAmountAttribute(): float
    {
        if (! $this->discount_value) return 0;
        if ($this->discount_type === 'percent') return $this->subtotal * ($this->discount_value / 100);
        return (float) $this->discount_value;
    }

    public function getAfterDiscountAttribute(): float
    {
        return max(0, $this->subtotal - $this->discount_amount);
    }

    public function getDppBaseAttribute(): float
    {
        return $this->is_dpp ? $this->after_discount * (11 / 12) : $this->after_discount;
    }

    public function getTaxAmountAttribute(): float
    {
        if (! $this->tax_percentage) return 0;
        return $this->dpp_base * ($this->tax_percentage / 100);
    }

    public function getTotalAttribute(): float
    {
        return $this->after_discount + $this->tax_amount;
    }

    public function getCarriedTotalAttribute(): float
    {
        if (!$this->carried_from_id) return 0.0;
        $from = $this->relationLoaded('carriedFrom')
            ? $this->carriedFrom
            : static::with(['items', 'carriedFrom.items'])->find($this->carried_from_id);
        if (!$from) return 0.0;
        return $from->total + $from->carried_total;
    }

    public function getGrandTotalAttribute(): float
    {
        return $this->total + $this->carried_total;
    }

    public function getReaktivasiTotalAttribute(): float
    {
        if (!$this->is_reaktivasi || $this->reaktivasi_chain_id) return 0.0;
        $members = $this->relationLoaded('reaktivasiChain')
            ? $this->reaktivasiChain
            : static::with('items')->where('reaktivasi_chain_id', $this->id)->get();
        return (float) $members->sum(fn($inv) => $inv->total);
    }

    public function getReaktivasiGrandTotalAttribute(): float
    {
        return $this->total + $this->reaktivasi_total;
    }

    public static function generateNumber(string $categoryCode, \DateTimeInterface $issueDate, string $type = 'monthly'): string
    {
        $year        = (int) $issueDate->format('Y');
        $romanMonths = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        $roman       = $romanMonths[(int) $issueDate->format('n') - 1];

        // Sequence global per tahun — semua tipe share counter yang sama
        $maxSeq = static::whereYear('issue_date', $year)
            ->where('invoice_number', 'like', "%/INV/MVC/%/{$year}")
            ->get()
            ->map(fn($inv) => (int) explode('/', $inv->invoice_number)[0])
            ->filter(fn($n) => $n > 0) // exclude C-xxx dan R-xxx (cast ke 0)
            ->max() ?? 0;

        $seq = str_pad($maxSeq + 1, 3, '0', STR_PAD_LEFT);

        return "{$seq}/{$categoryCode}/INV/MVC/{$roman}/{$year}";
    }
}
