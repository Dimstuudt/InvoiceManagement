<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>{{ $invoice->invoice_number }}</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  @page { size: A4 portrait; margin: 0; }
  body  { margin: 0; background: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .paper { width: 794px; margin: 0 auto; background: white; font-family: Arial, sans-serif; font-size: 13px; color: #1f2937; }
  @media print { .no-print { display: none !important; } }
</style>
</head>
<body>
@php
  $BLN         = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  $tgl         = fn($d) => $d ? $d->format('d-m-Y') : '-';
  $hdr         = $imgB64($invoice->documentIssuer?->header_image_url);
  $logo        = $imgB64($invoice->bankAccount?->bank_logo_url);
  $sig         = $imgB64($invoice->signature?->signature_image_url);
  $carriedFrom        = $invoice->carriedFrom ?? null;
  $carriedTotal       = $invoice->carried_total;
  $grandTotal         = $invoice->grand_total;
  $isReaktivasiHead   = $invoice->is_reaktivasi && !$invoice->reaktivasi_chain_id;
  $reaktivasiMembers  = $isReaktivasiHead ? $invoice->reaktivasiChain->sortBy('issue_date') : collect();
  $reaktivasiTotal    = $invoice->reaktivasi_total;
  $reaktivasiGrand    = $invoice->reaktivasi_grand_total;
  $isPrepayHead       = $invoice->is_prepay && !$invoice->prepay_chain_id;
  $prepayMembers      = $isPrepayHead ? $invoice->prepayChain->sortBy('issue_date') : collect();
  $prepayTotal        = $invoice->prepay_total;
  $prepayGrand        = $invoice->prepay_grand_total;
  // Tunggakan period labels & combined subtotal
  $carryPeriodLabel = null;
  if ($carriedFrom) {
    $chainStart = $carriedFrom;
    while ($chainStart->carriedFrom) { $chainStart = $chainStart->carriedFrom; }
    $s = $chainStart->issue_date->translatedFormat('F Y');
    $e = $carriedFrom->issue_date->translatedFormat('F Y');
    $carryPeriodLabel = $s === $e ? $s : "{$s} s/d {$e}";
  }
  $reaktivasiPeriodLabel = null;
  if ($isReaktivasiHead && $reaktivasiMembers->count() > 0) {
    $s = $reaktivasiMembers->first()->issue_date->translatedFormat('F Y');
    $e = $reaktivasiMembers->last()->issue_date->translatedFormat('F Y');
    $reaktivasiPeriodLabel = $s === $e ? $s : "{$s} s/d {$e}";
  }
  $prepayPeriodLabel = null;
  if ($isPrepayHead && $prepayMembers->count() > 0) {
    $s = $prepayMembers->first()->issue_date->translatedFormat('F Y');
    $e = $prepayMembers->last()->issue_date->translatedFormat('F Y');
    $prepayPeriodLabel = $s === $e ? $s : "{$s} s/d {$e}";
  }
  $displayTotal = $isPrepayHead ? $prepayGrand : ($isReaktivasiHead ? $reaktivasiGrand : $grandTotal);
@endphp
<div class="paper">

  {{-- HEADER / KOP SURAT --}}
  @if($invoice->documentIssuer)
    @if($hdr)
      <img src="{{ $hdr }}" style="width:100%;display:block;max-height:160px;object-fit:contain" alt="Kop"/>
    @else
      <div style="padding:1.5rem 2.5rem;border-bottom:3px solid #1d4ed8">
        <p style="font-size:1.5rem;font-weight:900;color:#1d4ed8;text-transform:uppercase">{{ $invoice->documentIssuer->name }}</p>
      </div>
    @endif
  @endif

  {{-- INVOICE TO --}}
  <div style="padding:1.5rem 2.5rem 1rem 2.5rem">
    <p style="font-size:0.75rem;color:#6b7280;margin-bottom:0.35rem">Invoice To</p>
    <p style="font-size:1.35rem;font-weight:900;color:#60a5fa;text-transform:uppercase;margin-bottom:1rem;line-height:1.3;text-align:center">{{ $invoice->client?->company_name }}</p>

    {{-- Dua kolom: kiri address, kanan meta --}}
    <table style="width:100%;border-collapse:collapse">
      <tr>
        <td style="width:50%;vertical-align:top;padding-right:2rem">
          <table style="border-collapse:collapse">
            @if($invoice->client?->address)
            <tr>
              <td style="font-weight:700;color:#111827;padding-right:0.5rem;padding-bottom:0.5rem;vertical-align:top;white-space:nowrap">Address</td>
              <td style="padding-bottom:0.5rem;vertical-align:top;color:#374151">: {{ $invoice->client->address }}@if($invoice->client->city), {{ $invoice->client->city }}@endif</td>
            </tr>
            @endif
            @if($invoice->attention)
            <tr>
              <td style="font-weight:700;color:#111827;padding-right:0.5rem;vertical-align:top;white-space:nowrap">Attention</td>
              <td style="vertical-align:top;color:#374151">: {{ $invoice->attention }}</td>
            </tr>
            @endif
          </table>
        </td>
        <td style="width:50%;vertical-align:top">
          <table style="border-collapse:collapse;margin-left:auto">
            <tr>
              <td style="font-weight:700;color:#111827;padding-right:0.5rem;padding-bottom:0.4rem;white-space:nowrap">No Invoice</td>
              <td style="padding-bottom:0.4rem;color:#374151">: {{ $invoice->invoice_number }}</td>
            </tr>
            <tr>
              <td style="font-weight:700;color:#111827;padding-right:0.5rem;padding-bottom:0.4rem;white-space:nowrap">Invoice Date</td>
              <td style="padding-bottom:0.4rem;color:#374151">: {{ $tgl($invoice->issue_date) }}</td>
            </tr>
            <tr>
              <td style="font-weight:700;color:#111827;padding-right:0.5rem;padding-bottom:0.4rem;white-space:nowrap">Due Date</td>
              <td style="padding-bottom:0.4rem;color:#374151">: {{ $tgl($invoice->due_date) }}</td>
            </tr>
            <tr>
              <td style="font-weight:700;color:#111827;padding-right:0.5rem;white-space:nowrap">SPK No</td>
              <td style="color:#374151">: {{ $invoice->spk_number ?? '-' }}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </div>

  {{-- ITEMS TABLE --}}
  <div style="margin:0.5rem 2.5rem 0 2.5rem;background:#f9fafb;border-radius:0.75rem;overflow:hidden;border:1px solid #e5e7eb">
    <table style="width:100%;border-collapse:collapse">
      <thead>
        <tr>
          <th style="text-align:left;padding:0.6rem 1rem 0.5rem 1rem;font-size:0.875rem;font-weight:700;color:#2563eb">Description</th>
          <th style="text-align:right;padding:0.6rem 1rem 0.5rem 1rem;font-size:0.875rem;font-weight:700;color:#2563eb;width:10rem">Price</th>
        </tr>
        <tr>
          <td colspan="2" style="padding:0 0.75rem">
            <div style="border-bottom:2px solid #2563eb"></div>
          </td>
        </tr>
      </thead>
      <tbody>
        @foreach($invoice->items as $item)
        <tr style="border-bottom:1px solid #e5e7eb">
          <td style="padding:0.75rem 1rem;font-size:0.875rem;color:#1f2937">{{ $item->description }}</td>
          <td style="padding:0.75rem 1rem;font-size:0.875rem;text-align:right;font-family:'Courier New',monospace;color:#1f2937">Rp {{ number_format($item->amount, 2, ',', '.') }}</td>
        </tr>
        @endforeach
        {{-- Baris perpanjangan layanan (carry, reaktivasi, prepay) --}}
        @if($carriedFrom || ($isReaktivasiHead && $reaktivasiMembers->count() > 0) || ($isPrepayHead && $prepayMembers->count() > 0))
        <tr><td colspan="2" style="padding:0 0.75rem"><div style="border-top:1px dashed #cbd5e1"></div></td></tr>
        @endif
        @if($carriedFrom)
        <tr style="background:#fffbf5">
          <td style="padding:0.7rem 1rem;font-size:0.875rem;color:#78350f;font-style:italic">
            Perpanjangan Layanan — {{ $carryPeriodLabel }}
          </td>
          <td style="padding:0.7rem 1rem;font-size:0.875rem;text-align:right;font-family:'Courier New',monospace;color:#78350f;white-space:nowrap">
            Rp {{ number_format($carriedTotal, 2, ',', '.') }}
          </td>
        </tr>
        @endif
        @if($isReaktivasiHead && $reaktivasiMembers->count() > 0)
        <tr style="background:#f0fdf4">
          <td style="padding:0.7rem 1rem;font-size:0.875rem;color:#065f46;font-style:italic">
            Perpanjangan Layanan — {{ $reaktivasiPeriodLabel }}
          </td>
          <td style="padding:0.7rem 1rem;font-size:0.875rem;text-align:right;font-family:'Courier New',monospace;color:#065f46;white-space:nowrap">
            Rp {{ number_format($reaktivasiTotal, 2, ',', '.') }}
          </td>
        </tr>
        @endif
        @if($isPrepayHead && $prepayMembers->count() > 0)
        <tr style="background:#f0fdfa">
          <td style="padding:0.7rem 1rem;font-size:0.875rem;color:#0f766e;font-style:italic">
            Pembayaran di Muka — {{ $prepayPeriodLabel }}
          </td>
          <td style="padding:0.7rem 1rem;font-size:0.875rem;text-align:right;font-family:'Courier New',monospace;color:#0f766e;white-space:nowrap">
            Rp {{ number_format($prepayTotal, 2, ',', '.') }}
          </td>
        </tr>
        @endif
      </tbody>
      <tfoot>
        <tr>
          <td colspan="2" style="padding:0.5rem 1rem 0.75rem 1rem">
            <table style="width:320px;border-collapse:collapse;margin-left:auto">

              {{-- Sub Total (items periode ini saja, perpanjangan tidak masuk basis pajak) --}}
              <tr style="border-top:1px solid #e5e7eb">
                <td style="padding:0.45rem 0.75rem 0.45rem 0;font-size:0.8125rem;color:#6b7280;white-space:nowrap">
                  @if($carriedFrom || ($isReaktivasiHead && $reaktivasiMembers->count() > 0) || ($isPrepayHead && $prepayMembers->count() > 0))
                    Sub Total <span style="font-size:0.7rem;font-style:italic">(periode ini)</span>
                  @else
                    Sub Total
                  @endif
                </td>
                <td style="padding:0.45rem 0;font-size:0.8125rem;text-align:right;font-family:'Courier New',monospace;color:#1f2937;white-space:nowrap">
                  Rp {{ number_format($invoice->subtotal, 2, ',', '.') }}
                </td>
              </tr>

              {{-- Diskon --}}
              @if($invoice->discount_value)
              <tr>
                <td style="padding:0.3rem 0.75rem 0.3rem 0;font-size:0.8125rem;color:#6b7280;white-space:nowrap">
                  Diskon{{ $invoice->discount_type === 'percent' ? ' (' . $invoice->discount_value . '%)' : '' }}
                </td>
                <td style="padding:0.3rem 0;font-size:0.8125rem;text-align:right;font-family:'Courier New',monospace;color:#dc2626;white-space:nowrap">
                  - Rp {{ number_format($invoice->discount_amount, 2, ',', '.') }}
                </td>
              </tr>
              @endif

              {{-- DPP --}}
              @if($invoice->is_dpp)
              <tr>
                <td style="padding:0.3rem 0.75rem 0.3rem 0;font-size:0.8125rem;color:#6b7280;white-space:nowrap">DPP (11/12)</td>
                <td style="padding:0.3rem 0;font-size:0.8125rem;text-align:right;font-family:'Courier New',monospace;color:#1f2937;white-space:nowrap">
                  Rp {{ number_format($invoice->dpp_base, 2, ',', '.') }}
                </td>
              </tr>
              @endif

              {{-- PPN --}}
              @if($invoice->tax_percentage)
              <tr>
                <td style="padding:0.3rem 0.75rem 0.3rem 0;font-size:0.8125rem;color:#6b7280;white-space:nowrap">PPN {{ $invoice->tax_percentage }}%</td>
                <td style="padding:0.3rem 0;font-size:0.8125rem;text-align:right;font-family:'Courier New',monospace;color:#1f2937;white-space:nowrap">
                  Rp {{ number_format($invoice->tax_amount, 2, ',', '.') }}
                </td>
              </tr>
              @endif

              {{-- Grand Total --}}
              <tr style="border-top:2px solid #1d4ed8">
                <td style="padding:0.6rem 0.75rem 0.6rem 0;font-size:0.875rem;font-weight:900;color:#1d4ed8;letter-spacing:0.05em;text-transform:uppercase;white-space:nowrap">TOTAL</td>
                <td style="padding:0.6rem 0;font-size:1rem;font-weight:900;text-align:right;font-family:'Courier New',monospace;color:#1d4ed8;white-space:nowrap">
                  Rp {{ number_format($displayTotal, 2, ',', '.') }}
                </td>
              </tr>

              {{-- Note pajak perpanjangan --}}
              @if($invoice->tax_percentage && ($carriedFrom || ($isReaktivasiHead && $reaktivasiMembers->count() > 0)))
              <tr>
                <td colspan="2" style="padding:0.5rem 0 0 0;font-size:0.7rem;color:#9ca3af;font-style:italic;line-height:1.5">
                  * Biaya perpanjangan layanan merupakan nilai netto yang telah diperhitungkan {{ $invoice->is_dpp ? 'DPP/PPN' : 'PPN' }}-nya pada periode masing-masing dan tidak dikenakan pajak kembali pada invoice ini.
                </td>
              </tr>
              @endif

            </table>
          </td>
        </tr>
      </tfoot>
    </table>
  </div>

  {{-- NOTES --}}
  @if($invoice->notes)
  <div style="margin:1rem 2.5rem 0 2.5rem;padding:0.75rem 1rem;background:#f9fafb;border-radius:0.5rem;border:1px solid #e5e7eb">
    <p style="font-size:0.7rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.35rem">Catatan / Notes</p>
    <p style="font-size:0.875rem;color:#374151;white-space:pre-line;line-height:1.5">{{ $invoice->notes }}</p>
  </div>
  @endif

  {{-- PAYMENT + SIGNATURE --}}
  <div style="display:flex;align-items:flex-start;gap:2rem;padding:2rem 2.5rem 2rem 2.5rem">

    {{-- Payment Method --}}
    <div style="flex:1">
      <p style="font-weight:700;font-size:0.9rem;color:#111827;margin-bottom:0.6rem">Payment Method</p>

      <p style="font-size:0.8rem;color:#4b5563;line-height:1.5">
        Pembayaran dapat dilakukan melalui transfer atau setoran<br/>tunai ke {{ $invoice->bankAccount->bank_name }}
      </p>
      @if($invoice->bankAccount)
        <p style="font-size:0.875rem;font-weight:700;color:#111827;margin-top:0.2rem;margin-bottom:0.25rem">{{ $invoice->documentIssuer?->name ?? $invoice->bankAccount->bank_name }}</p>
        <p style="font-size:0.875rem;color:#1d4ed8;font-weight:600;margin-bottom:0.6rem;font-family:'Courier New',monospace">
          {{ $invoice->bankAccount->account_number }}
          <span style="font-family:Arial,sans-serif;font-weight:400;color:#9ca3af;font-size:0.8rem">(atas nama {{ $invoice->bankAccount->name }})</span>
        </p>
      @endif

      @if($invoice->documentIssuer?->tax_id_number)
        <p style="font-size:0.875rem;font-weight:700;color:#111827">NPWP : {{ $invoice->documentIssuer->tax_id_number }}</p>
        <p style="font-size:0.875rem;font-weight:700;color:#111827">{{ $invoice->documentIssuer->name }}</p>
      @endif
      @if($invoice->documentIssuer?->tax_id_address)
        <p style="font-size:0.75rem;color:#6b7280;margin-top:0.15rem">{{ $invoice->documentIssuer->tax_id_address }}</p>
      @endif
    </div>

    {{-- Signature --}}
    @if($invoice->with_signature && $invoice->signature)
    <div style="text-align:center;min-width:200px;flex-shrink:0">
      <div style="height:80px;display:flex;align-items:center;justify-content:center">
        @if($sig)
          <img src="{{ $sig }}" style="max-height:80px;max-width:200px;object-fit:contain" alt="TTD"/>
        @endif
      </div>
      <div style="border-bottom:1px solid #374151;margin:0.25rem 1rem 0.4rem 1rem"></div>
      <p style="font-weight:700;font-size:0.875rem;color:#111827">{{ $invoice->signature->name }}</p>
      <p style="font-size:0.75rem;color:#6b7280;margin-top:0.1rem">{{ $invoice->signature->position }}</p>
      @if($invoice->documentIssuer)
      <p style="font-size:0.75rem;color:#6b7280;margin-top:0.1rem">{{ $invoice->documentIssuer->name }}</p>
      @endif
    </div>
    @endif

  </div>

</div>

@if($printMode ?? false)
<div class="no-print" style="position:fixed;bottom:2rem;right:2rem;z-index:999">
  <button onclick="window.print()"
    style="display:inline-flex;align-items:center;gap:0.5rem;background:#1d4ed8;color:white;font-family:Arial,sans-serif;font-size:0.875rem;font-weight:600;padding:0.625rem 1.25rem;border-radius:0.75rem;border:none;cursor:pointer;box-shadow:0 4px 14px rgba(29,78,216,0.4)">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
    </svg>
    Print Invoice
  </button>
</div>
@endif

</body>
</html>
