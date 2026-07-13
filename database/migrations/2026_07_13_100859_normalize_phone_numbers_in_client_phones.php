<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $phones = DB::table('client_phones')->get();
        foreach ($phones as $row) {
            $normalized = $this->normalize($row->phone_number);
            if ($normalized !== $row->phone_number) {
                DB::table('client_phones')
                    ->where('id', $row->id)
                    ->update(['phone_number' => $normalized]);
            }
        }
    }

    public function down(): void {}

    private function normalize(string $raw): string
    {
        $digits = preg_replace('/[^\d]/', '', $raw);
        if ($digits === '') return $raw;
        if (str_starts_with($digits, '62'))  return $digits;
        if (str_starts_with($digits, '0'))   return '62' . substr($digits, 1);
        if (str_starts_with($digits, '8'))   return '62' . $digits;
        return $digits;
    }
};
