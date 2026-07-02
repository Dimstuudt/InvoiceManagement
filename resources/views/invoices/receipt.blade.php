<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Receipt – {{ $invoice->invoice_number }}</title>
<style>
  @page { size: A4 portrait; margin: 0; }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { background: white; font-family: Arial, sans-serif; font-size: 13px; color: #1f2937; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .paper { width: 794px; margin: 0 auto; padding: 0; position: relative; overflow: hidden; }

  /* STATUS STAMP */
  .stamp {
    position: absolute;
    top: 200px;
    right: 60px;
    transform: rotate(-22deg);
    padding: 10px 24px;
    border: 5px solid;
    border-radius: 6px;
    font-size: 2rem;
    font-weight: 900;
    letter-spacing: 0.28em;
    font-family: 'Courier New', Courier, monospace;
    opacity: 0.15;
    pointer-events: none;
    line-height: 1;
    z-index: 5;
  }
  .stamp-draft  { border-color: #6b7280; color: #6b7280; }
  .stamp-sent   { border-color: #2563eb; color: #2563eb; }
  .stamp-paid   { border-color: #16a34a; color: #16a34a; }
  .stamp-unpaid { border-color: #dc2626; color: #dc2626; }
  .stamp-overdue { border-color: #b91c1c; color: #b91c1c; font-size: 1.5rem; letter-spacing: 0.15em; }

  /* PRINT */
  @media print {
    body { margin: 0; }
    .no-print { display: none !important; }
  }
</style>
</head>
<body>
@php
  $tgl          = fn($d) => $d ? $d->translatedFormat('d F Y') : '-';
  $hdr          = $imgB64($invoice->documentIssuer?->header_image_url);
  $sig          = $imgB64($invoice->signature?->signature_image_url);
  $carriedFrom        = $invoice->carriedFrom ?? null;
  $carriedTotal       = $invoice->carried_total;
  $grandTotal         = $invoice->grand_total;
  $isReaktivasiHead   = $invoice->is_reaktivasi && !$invoice->reaktivasi_chain_id;
  $reaktivasiMembers  = $isReaktivasiHead ? $invoice->reaktivasiChain->sortBy('issue_date') : collect();
  $reaktivasiTotal    = $invoice->reaktivasi_total;
  $reaktivasiGrand    = $invoice->reaktivasi_grand_total;
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
  $displayTotal = $isReaktivasiHead ? $reaktivasiGrand : $grandTotal;
  $isPaid       = $invoice->status === 'paid';
  $isOverdue    = !$isPaid && $invoice->due_date && $invoice->due_date->isPast();
  $stampLabel   = [
    'draft'   => 'DRAFT',
    'sent'    => 'SENT',
    'paid'    => 'LUNAS',
    'unpaid'  => 'UNPAID',
    'carried' => 'CARRIED',
    'frozen'  => 'FROZEN',
  ];
  $stampClass = $isOverdue ? 'stamp-overdue' : 'stamp-' . $invoice->status;
  $stampText  = $isOverdue ? 'JATUH TEMPO' : ($stampLabel[$invoice->status] ?? strtoupper($invoice->status));

  $receiptNumber = 'RCP/' . $invoice->invoice_number;
@endphp

<div class="paper">

  {{-- STAMP --}}
  <div class="stamp {{ $stampClass }}">{{ $stampText }}</div>

  {{-- KOP SURAT --}}
  @if($hdr)
    <img src="{{ $hdr }}" style="width:100%;display:block;max-height:160px;object-fit:contain" alt="Kop"/>
  @elseif($invoice->documentIssuer)
    <div style="padding:1.5rem 2.5rem;border-bottom:3px solid #1d4ed8">
      <p style="font-size:1.5rem;font-weight:900;color:#1d4ed8;text-transform:uppercase">{{ $invoice->documentIssuer->name }}</p>
    </div>
  @endif

  {{-- JUDUL --}}
  <div style="padding:1.5rem 2.5rem 0.75rem 2.5rem;border-bottom:2px solid #e5e7eb">
    <p style="font-size:1.1rem;font-weight:900;color:#111827;letter-spacing:0.06em;text-transform:uppercase">Bukti Penerimaan Pembayaran</p>
    <p style="font-size:0.75rem;color:#6b7280;margin-top:0.2rem;letter-spacing:0.02em">Receipt / Acknowledgement of Payment</p>
  </div>

  {{-- INFO RECEIPT --}}
  <div style="padding:1.25rem 2.5rem;display:flex;justify-content:space-between;align-items:flex-start;gap:2rem">

    {{-- Kiri: dari siapa --}}
    <div style="flex:1">
      <table style="border-collapse:collapse">
        <tr>
          <td style="font-weight:700;color:#111827;padding-right:0.75rem;padding-bottom:0.5rem;vertical-align:top;white-space:nowrap;font-size:0.875rem">Diterima dari</td>
          <td style="padding-bottom:0.5rem;color:#1d4ed8;font-weight:700;font-size:0.875rem;vertical-align:top">: {{ $invoice->client?->company_name }}</td>
        </tr>
        @if($invoice->attention)
        <tr>
          <td style="font-weight:700;color:#111827;padding-right:0.75rem;padding-bottom:0.5rem;vertical-align:top;white-space:nowrap;font-size:0.875rem">Perhatian</td>
          <td style="padding-bottom:0.5rem;color:#374151;font-size:0.875rem;vertical-align:top">: {{ $invoice->attention }}</td>
        </tr>
        @endif
        <tr>
          <td style="font-weight:700;color:#111827;padding-right:0.75rem;padding-bottom:0.5rem;vertical-align:top;white-space:nowrap;font-size:0.875rem">Untuk Invoice</td>
          <td style="padding-bottom:0.5rem;color:#374151;font-size:0.875rem;vertical-align:top;font-family:'Courier New',monospace">: {{ $invoice->invoice_number }}</td>
        </tr>
        @if($invoice->projectCategory)
        <tr>
          <td style="font-weight:700;color:#111827;padding-right:0.75rem;vertical-align:top;white-space:nowrap;font-size:0.875rem">Kategori</td>
          <td style="color:#374151;font-size:0.875rem;vertical-align:top">: {{ $invoice->projectCategory->name }}</td>
        </tr>
        @endif
      </table>
    </div>

    {{-- Kanan: nomor & tanggal receipt --}}
    <div style="flex-shrink:0;background:#f9fafb;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1rem 1.25rem">
      <table style="border-collapse:collapse">
        <tr>
          <td style="font-weight:700;color:#6b7280;padding-right:0.75rem;padding-bottom:0.5rem;white-space:nowrap;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em">No. Receipt</td>
          <td style="padding-bottom:0.5rem;color:#111827;font-size:0.8rem;font-family:'Courier New',monospace;font-weight:700">{{ $receiptNumber }}</td>
        </tr>
        <tr>
          <td style="font-weight:700;color:#6b7280;padding-right:0.75rem;padding-bottom:0.5rem;white-space:nowrap;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em">Tanggal</td>
          <td style="padding-bottom:0.5rem;color:#374151;font-size:0.8rem">{{ $tgl(now()) }}</td>
        </tr>
        <tr>
          <td style="font-weight:700;color:#6b7280;padding-right:0.75rem;white-space:nowrap;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em">Status</td>
          <td>
            <span style="display:inline-block;padding:0.2rem 0.6rem;border-radius:0.375rem;font-size:0.75rem;font-weight:700;
              {{ $invoice->status === 'paid' ? 'background:#dcfce7;color:#15803d' : ($invoice->status === 'unpaid' ? 'background:#fee2e2;color:#dc2626' : ($invoice->status === 'sent' ? 'background:#dbeafe;color:#1d4ed8' : 'background:#f3f4f6;color:#6b7280')) }}">
              {{ strtoupper($invoice->status) }}
            </span>
          </td>
        </tr>
      </table>
    </div>

  </div>

  {{-- TABEL ITEM --}}
  <div style="margin:0 2.5rem;background:#f9fafb;border-radius:0.75rem;overflow:hidden;border:1px solid #e5e7eb">
    <table style="width:100%;border-collapse:collapse">
      <thead>
        <tr>
          <th style="text-align:left;padding:0.6rem 1rem 0.5rem 1rem;font-size:0.8rem;font-weight:700;color:#2563eb">Deskripsi Pekerjaan</th>
          <th style="text-align:right;padding:0.6rem 1rem 0.5rem 1rem;font-size:0.8rem;font-weight:700;color:#2563eb;width:11rem">Jumlah</th>
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
          <td style="padding:0.7rem 1rem;font-size:0.875rem;color:#1f2937">{{ $item->description }}</td>
          <td style="padding:0.7rem 1rem;font-size:0.875rem;text-align:right;font-family:'Courier New',monospace;color:#1f2937;white-space:nowrap">Rp {{ number_format($item->amount, 2, ',', '.') }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="2" style="padding:0.5rem 1rem 0.75rem 1rem">
            <table style="width:320px;border-collapse:collapse;margin-left:auto">

              {{-- Sub Total (items saja, tunggakan tidak masuk basis pajak) --}}
              <tr style="border-top:1px solid #e5e7eb">
                <td style="padding:0.45rem 0.75rem 0.45rem 0;font-size:0.8125rem;color:#6b7280;white-space:nowrap">Sub Total</td>
                <td style="padding:0.45rem 0;font-size:0.8125rem;text-align:right;font-family:'Courier New',monospace;color:#374151;white-space:nowrap">
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
                <td style="padding:0.3rem 0;font-size:0.8125rem;text-align:right;font-family:'Courier New',monospace;color:#374151;white-space:nowrap">
                  Rp {{ number_format($invoice->dpp_base, 2, ',', '.') }}
                </td>
              </tr>
              @endif

              {{-- PPN --}}
              @if($invoice->tax_percentage)
              <tr>
                <td style="padding:0.3rem 0.75rem 0.3rem 0;font-size:0.8125rem;color:#6b7280;white-space:nowrap">PPN {{ $invoice->tax_percentage }}%</td>
                <td style="padding:0.3rem 0;font-size:0.8125rem;text-align:right;font-family:'Courier New',monospace;color:#374151;white-space:nowrap">
                  Rp {{ number_format($invoice->tax_amount, 2, ',', '.') }}
                </td>
              </tr>
              @endif

              {{-- Total Jasa + Tunggakan (jika ada, pisahkan dari basis pajak) --}}
              @if($carriedFrom || ($isReaktivasiHead && $reaktivasiMembers->count() > 0))
              <tr style="border-top:1px solid #e5e7eb">
                <td style="padding:0.45rem 0.75rem 0.45rem 0;font-size:0.8125rem;color:#6b7280;white-space:nowrap">Total Jasa</td>
                <td style="padding:0.45rem 0;font-size:0.8125rem;text-align:right;font-family:'Courier New',monospace;color:#374151;white-space:nowrap">
                  Rp {{ number_format($invoice->total, 2, ',', '.') }}
                </td>
              </tr>
              @if($carriedFrom)
              <tr>
                <td style="padding:0.3rem 0.75rem 0.3rem 0;font-size:0.8125rem;color:#92400e;white-space:nowrap">
                  Tunggakan Layanan {{ $carryPeriodLabel }}
                </td>
                <td style="padding:0.3rem 0;font-size:0.8125rem;text-align:right;font-family:'Courier New',monospace;color:#92400e;white-space:nowrap">
                  Rp {{ number_format($carriedTotal, 2, ',', '.') }}
                </td>
              </tr>
              @endif
              @if($isReaktivasiHead && $reaktivasiMembers->count() > 0)
              <tr>
                <td style="padding:0.3rem 0.75rem 0.3rem 0;font-size:0.8125rem;color:#065f46;white-space:nowrap">
                  Tunggakan Layanan {{ $reaktivasiPeriodLabel }}
                </td>
                <td style="padding:0.3rem 0;font-size:0.8125rem;text-align:right;font-family:'Courier New',monospace;color:#065f46;white-space:nowrap">
                  Rp {{ number_format($reaktivasiTotal, 2, ',', '.') }}
                </td>
              </tr>
              @endif
              @endif

              {{-- Grand Total --}}
              <tr style="border-top:2px solid #1d4ed8">
                <td style="padding:0.6rem 0.75rem 0.6rem 0;font-size:0.875rem;font-weight:900;color:#1d4ed8;letter-spacing:0.05em;text-transform:uppercase;white-space:nowrap">TOTAL</td>
                <td style="padding:0.6rem 0;font-size:1rem;font-weight:900;text-align:right;font-family:'Courier New',monospace;color:#1d4ed8;white-space:nowrap">
                  Rp {{ number_format($displayTotal, 2, ',', '.') }}
                </td>
              </tr>

              {{-- Note pajak tunggakan --}}
              @if($invoice->tax_percentage && ($carriedFrom || ($isReaktivasiHead && $reaktivasiMembers->count() > 0)))
              <tr>
                <td colspan="2" style="padding:0.5rem 0 0 0;font-size:0.7rem;color:#9ca3af;font-style:italic;line-height:1.5">
                  * Tunggakan layanan merupakan nilai bersih yang telah diperhitungkan pajaknya pada periode sebelumnya dan tidak dikenakan {{ $invoice->is_dpp ? 'DPP/PPN' : 'PPN' }} kembali.
                </td>
              </tr>
              @endif

            </table>
          </td>
        </tr>
      </tfoot>
    </table>
  </div>

  {{-- CATATAN LUNAS --}}
  @if($isPaid)
  <div style="margin:1rem 2.5rem 0;padding:0.75rem 1rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:0.625rem;display:flex;align-items:center;gap:0.75rem">
    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2.5">
      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <p style="font-size:0.8rem;color:#15803d;font-weight:600">Pembayaran telah diterima dan dikonfirmasi. Terima kasih.</p>
  </div>
  @endif

  {{-- TANDA TANGAN --}}
  <div style="display:flex;align-items:flex-end;justify-content:flex-end;padding:1.5rem 2.5rem 2rem 2.5rem;gap:2rem">

    {{-- Info pembayaran --}}
    <div style="flex:1">
      @if($invoice->bankAccount)
      <p style="font-size:0.75rem;color:#6b7280;margin-bottom:0.25rem">Pembayaran melalui:</p>
      <p style="font-size:0.875rem;font-weight:700;color:#111827">{{ $invoice->bankAccount->bank_name }}</p>
      <p style="font-size:0.875rem;font-family:'Courier New',monospace;color:#1d4ed8;font-weight:600">
        {{ $invoice->bankAccount->account_number }}
        <span style="font-family:Arial,sans-serif;font-weight:400;color:#9ca3af;font-size:0.8rem">(a.n. {{ $invoice->bankAccount->name }})</span>
      </p>
      @endif
      @if($invoice->documentIssuer?->tax_id_number)
      <p style="font-size:0.8rem;font-weight:700;color:#111827;margin-top:0.5rem">NPWP : {{ $invoice->documentIssuer->tax_id_number }}</p>
      @endif
    </div>

    {{-- Signature --}}
    @if($invoice->with_signature && $invoice->signature)
    <div style="text-align:center;min-width:180px">
      <p style="font-size:0.75rem;color:#6b7280;margin-bottom:0.4rem">Hormat kami,</p>
      <div style="height:72px;display:flex;align-items:center;justify-content:center">
        @if($sig)
          <img src="{{ $sig }}" style="max-height:72px;max-width:180px;object-fit:contain" alt="TTD"/>
        @endif
      </div>
      <div style="border-bottom:1px solid #374151;margin:0.25rem 0.75rem 0.35rem"></div>
      <p style="font-weight:700;font-size:0.875rem;color:#111827">{{ $invoice->signature->name }}</p>
      <p style="font-size:0.75rem;color:#6b7280;margin-top:0.1rem">{{ $invoice->signature->position }}</p>
      @if($invoice->documentIssuer)
      <p style="font-size:0.75rem;color:#6b7280;margin-top:0.1rem">{{ $invoice->documentIssuer->name }}</p>
      @endif
    </div>
    @else
    <div style="text-align:center;min-width:180px">
      <p style="font-size:0.75rem;color:#6b7280;margin-bottom:0.4rem">Hormat kami,</p>
      <div style="height:72px"></div>
      <div style="border-bottom:1px solid #374151;margin:0.25rem 0.75rem 0.35rem"></div>
      @if($invoice->documentIssuer)
      <p style="font-weight:700;font-size:0.875rem;color:#111827">{{ $invoice->documentIssuer->name }}</p>
      @endif
    </div>
    @endif

  </div>

  {{-- PRINT BUTTON --}}
  <div class="no-print" style="position:fixed;bottom:1.5rem;right:1.5rem">
    <button onclick="window.print()"
      style="display:flex;align-items:center;gap:0.5rem;background:#1d4ed8;color:white;border:none;border-radius:0.625rem;padding:0.7rem 1.25rem;font-size:0.875rem;font-weight:600;cursor:pointer;box-shadow:0 4px 12px rgba(29,78,216,0.35)">
      <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
      </svg>
      Print / Simpan PDF
    </button>
  </div>

</div>
</body>
</html>
