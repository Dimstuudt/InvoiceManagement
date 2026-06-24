<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'user_id', 'client_id', 'project_category_id', 'document_issuer_id',
        'bank_account_id', 'signature_id', 'with_signature', 'spk_number',
        'invoice_number', 'issue_date', 'due_date', 'attention', 'notes', 'status',
    ];

    protected $casts = [
        'issue_date'     => 'date',
        'due_date'       => 'date',
        'with_signature' => 'boolean',
    ];

    public function user()            { return $this->belongsTo(User::class); }
    public function client()          { return $this->belongsTo(Client::class); }
    public function projectCategory() { return $this->belongsTo(ProjectCategory::class); }
    public function documentIssuer()  { return $this->belongsTo(DocumentIssuer::class); }
    public function bankAccount()     { return $this->belongsTo(BankAccount::class); }
    public function signature()       { return $this->belongsTo(Signature::class); }
    public function items()           { return $this->hasMany(InvoiceItem::class)->orderBy('sort_order'); }

    public function getTotalAttribute(): float
    {
        return $this->items->sum('amount');
    }

    public static function generateNumber(string $categoryCode, \DateTimeInterface $issueDate): string
    {
        $month = (int) $issueDate->format('n');
        $year  = (int) $issueDate->format('Y');

        $romanMonths = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        $roman = $romanMonths[$month - 1];

        $count = static::whereYear('issue_date', $year)
            ->whereMonth('issue_date', $month)
            ->count();

        $seq = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return "{$seq}/{$categoryCode}/INV/MVC/{$roman}/{$year}";
    }
}
