<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Spk extends Model
{
    protected $fillable = [
        'client_id', 'user_id', 'invoice_id', 'project_category_id', 'spk_number',
        'service_name', 'contract_value', 'pic_name', 'start_date', 'end_date',
        'duration_months', 'file_path', 'notes',
    ];

    protected $casts = [
        'start_date'     => 'date',
        'end_date'       => 'date',
        'contract_value' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        // Hapus file fisik saat record Spk dihapus via model
        static::deleting(function (Spk $spk) {
            if ($spk->file_path) {
                Storage::disk('public')->delete($spk->file_path);
            }
        });
    }

    public function client()          { return $this->belongsTo(Client::class); }
    public function invoice()         { return $this->belongsTo(Invoice::class); }
    public function projectCategory() { return $this->belongsTo(ProjectCategory::class); }
    public function user()            { return $this->belongsTo(\App\Models\User::class); }

    public static function generateNumber(string $categoryCode, \DateTimeInterface $date): string
    {
        $year        = (int) $date->format('Y');
        $romanMonths = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        $roman       = $romanMonths[(int) $date->format('n') - 1];

        // Counter global — tidak reset per tahun, ambil max dari semua SPK
        $maxSeq = static::where('spk_number', 'like', '%/SPK/MVC/%')
            ->get()
            ->map(fn($s) => (int) explode('/', $s->spk_number)[0])
            ->filter(fn($n) => $n > 0)
            ->max() ?? 0;

        $seq = str_pad($maxSeq + 1, 3, '0', STR_PAD_LEFT);

        return "{$seq}/{$categoryCode}/SPK/MVC/{$roman}/{$year}";
    }
}
