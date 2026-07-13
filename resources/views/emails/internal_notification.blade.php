<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body style="margin:0;padding:0;background-color:#eef2f7;font-family:'Helvetica Neue',Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%">
@php
  $issuerName    = $invoice->documentIssuer?->name ?? config('app.name');
  $brandLogoPath = public_path('4vm1.png');
  $brandLogo     = file_exists($brandLogoPath)
      ? 'data:image/png;base64,' . base64_encode(file_get_contents($brandLogoPath))
      : null;
  $monogram = mb_strtoupper(mb_substr($issuerName, 0, 2));

  $isPaid = $stage === 'paid';

  $stageLabels = [
      'send1' => 'Pengiriman Pertama',
      'send2' => 'Pengiriman Kedua',
      'send3' => 'Pengiriman Ketiga',
      'send4' => 'Pengiriman Keempat',
      'send5' => 'Pengiriman Kelima (Terakhir)',
      'paid'  => 'Pembayaran Diterima',
  ];
  $stageLabel = $stageLabels[$stage] ?? $stage;

  $accentHex = $isPaid ? '#10b981' : '#f59e0b';
  $stageBg   = $isPaid ? '#d1fae5' : '#fef3c7';
  $stageText = $isPaid ? '#064e3b' : '#92400e';

  $fmtDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->locale('id')->translatedFormat('j F Y') : '-';
  $fmtRp   = fn($v) => $v !== null ? 'Rp ' . number_format((float) $v, 0, ',', '.') : '-';

  $clientEmails = $invoice->client->emails->where('is_active', true)->pluck('email')->toArray();
@endphp

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef2f7">
  <tr>
    <td align="center" style="padding:40px 16px">
      <table cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,0.12)">

        {{-- TOP ACCENT BAR --}}
        <tr>
          <td style="background:linear-gradient(90deg,#0f172a,{{ $accentHex }});height:4px;font-size:0;line-height:0">&nbsp;</td>
        </tr>

        {{-- HEADER --}}
        <tr>
          <td style="background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);padding:32px 40px 28px;text-align:center">
            @if($brandLogo)
              <img src="{{ $brandLogo }}" alt="{{ $issuerName }}"
                   style="display:block;margin:0 auto;max-height:72px;max-width:220px;object-fit:contain;border:0">
            @else
              <div style="display:inline-block;width:60px;height:60px;background:rgba(255,255,255,0.1);border-radius:50%;font-size:24px;font-weight:700;color:#ffffff;line-height:60px;text-align:center;border:2px solid rgba(255,255,255,0.2)">{{ $monogram }}</div>
              <p style="margin:8px 0 0;font-size:16px;font-weight:700;color:#ffffff">{{ $issuerName }}</p>
            @endif

            {{-- Dot row --}}
            <table cellpadding="0" cellspacing="0" border="0" align="center" style="margin-top:18px">
              <tr>
                <td style="padding:0 3px"><div style="width:4px;height:4px;background:rgba(255,255,255,0.2);border-radius:50%"></div></td>
                <td style="padding:0 3px"><div style="width:4px;height:4px;background:rgba(255,255,255,0.4);border-radius:50%"></div></td>
                <td style="padding:0 3px"><div style="width:6px;height:6px;background:{{ $accentHex }};border-radius:50%;opacity:0.8"></div></td>
                <td style="padding:0 3px"><div style="width:4px;height:4px;background:rgba(255,255,255,0.4);border-radius:50%"></div></td>
                <td style="padding:0 3px"><div style="width:4px;height:4px;background:rgba(255,255,255,0.2);border-radius:50%"></div></td>
              </tr>
            </table>
          </td>
        </tr>

        {{-- INTERNAL BADGE + STAGE --}}
        <tr>
          <td style="padding:28px 40px 0;text-align:center">
            <p style="margin:0 0 12px;font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:#94a3b8;font-weight:700">Notifikasi Internal</p>
            <span style="display:inline-block;background:{{ $stageBg }};color:{{ $stageText }};font-size:13px;font-weight:700;padding:7px 22px;border-radius:999px;letter-spacing:0.04em">
              {{ $stageLabel }}
            </span>
            <p style="margin:10px 0 0;font-size:11px;color:#94a3b8">
              {{ $fmtDate(now()) }} &bull; {{ now()->format('H:i') }} WIB
            </p>
          </td>
        </tr>

        {{-- DIVIDER --}}
        <tr>
          <td style="padding:24px 40px 0">
            <div style="border-top:1px solid #e2e8f0"></div>
          </td>
        </tr>

        {{-- DETAIL INVOICE --}}
        <tr>
          <td style="padding:24px 40px 0">
            <p style="margin:0 0 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.09em;color:#64748b">Detail Invoice</p>
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="padding:9px 0;border-bottom:1px solid #f1f5f9;width:42%;font-size:12px;color:#94a3b8;vertical-align:top">Nomor Invoice</td>
                <td style="padding:9px 0;border-bottom:1px solid #f1f5f9;font-size:13px;font-weight:700;color:#0f172a;font-family:'Courier New',Courier,monospace">{{ $invoice->invoice_number }}</td>
              </tr>
              <tr>
                <td style="padding:9px 0;border-bottom:1px solid #f1f5f9;font-size:12px;color:#94a3b8;vertical-align:top">Client</td>
                <td style="padding:9px 0;border-bottom:1px solid #f1f5f9;font-size:13px;font-weight:600;color:#1e293b">{{ $invoice->client->company_name }}</td>
              </tr>
              <tr>
                <td style="padding:9px 0;border-bottom:1px solid #f1f5f9;font-size:12px;color:#94a3b8;vertical-align:top">Proyek</td>
                <td style="padding:9px 0;border-bottom:1px solid #f1f5f9;font-size:13px;color:#1e293b">{{ $invoice->projectCategory?->name ?? '-' }}</td>
              </tr>
              <tr>
                <td style="padding:9px 0;border-bottom:1px solid #f1f5f9;font-size:12px;color:#94a3b8;vertical-align:top">Total</td>
                <td style="padding:9px 0;border-bottom:1px solid #f1f5f9;font-size:15px;font-weight:700;color:{{ $accentHex }}">{{ $fmtRp($invoice->total) }}</td>
              </tr>
              <tr>
                <td style="padding:9px 0;border-bottom:1px solid #f1f5f9;font-size:12px;color:#94a3b8;vertical-align:top">Tanggal Invoice</td>
                <td style="padding:9px 0;border-bottom:1px solid #f1f5f9;font-size:13px;color:#1e293b">{{ $fmtDate($invoice->issue_date) }}</td>
              </tr>
              @if(!$isPaid)
              <tr>
                <td style="padding:9px 0;border-bottom:1px solid #f1f5f9;font-size:12px;color:#94a3b8;vertical-align:top">Jatuh Tempo</td>
                <td style="padding:9px 0;border-bottom:1px solid #f1f5f9;font-size:13px;color:#1e293b">{{ $fmtDate($invoice->due_date) }}</td>
              </tr>
              @endif
              <tr>
                <td style="padding:9px 0;font-size:12px;color:#94a3b8;vertical-align:top">Dibuat oleh</td>
                <td style="padding:9px 0;font-size:13px;color:#1e293b">{{ $invoice->user?->name ?? '-' }}</td>
              </tr>
            </table>
          </td>
        </tr>

        {{-- SENT TO (client emails) --}}
        <tr>
          <td style="padding:20px 40px 0">
            <p style="margin:0 0 10px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.09em;color:#64748b">
              {{ $isPaid ? 'Receipt dikirim ke' : 'Email invoice dikirim ke' }}
            </p>
            @forelse($clientEmails as $em)
              <span style="display:inline-block;background:#f1f5f9;color:#475569;font-size:11px;padding:4px 10px;border-radius:6px;margin:3px 4px 3px 0;font-family:'Courier New',Courier,monospace">{{ $em }}</span>
            @empty
              <span style="font-size:12px;color:#cbd5e1">-</span>
            @endforelse
          </td>
        </tr>

        {{-- PAID CALLOUT --}}
        @if($isPaid)
        <tr>
          <td style="padding:20px 40px 0">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#d1fae5;border-radius:10px;border-left:4px solid #10b981">
              <tr>
                <td style="padding:14px 18px">
                  <p style="margin:0 0 3px;font-size:10px;color:#064e3b;font-weight:700;text-transform:uppercase;letter-spacing:0.07em">Invoice Lunas</p>
                  <p style="margin:0;font-size:13px;font-weight:600;color:#065f46">{{ $invoice->client->company_name }} telah melakukan pembayaran.</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        @endif

        <tr><td style="height:32px"></td></tr>

        {{-- FOOTER --}}
        <tr>
          <td style="background:#f8fafc;border-top:1px solid #e5e7eb;padding:24px 40px;text-align:center">
            <table cellpadding="0" cellspacing="0" border="0" align="center" style="margin-bottom:14px">
              <tr>
                <td style="width:16px;height:3px;background:{{ $accentHex }};border-radius:999px;opacity:0.4"></td>
                <td style="width:8px"></td>
                <td style="width:32px;height:3px;background:{{ $accentHex }};border-radius:999px"></td>
                <td style="width:8px"></td>
                <td style="width:16px;height:3px;background:{{ $accentHex }};border-radius:999px;opacity:0.4"></td>
              </tr>
            </table>
            <p style="margin:0 0 4px;font-size:12px;font-weight:700;color:#1e293b">{{ $issuerName }}</p>
            <p style="margin:0;font-size:11px;color:#cbd5e1;line-height:1.7">
              Ini adalah notifikasi otomatis untuk tim internal &mdash; mohon tidak membalas email ini.
            </p>
          </td>
        </tr>

        {{-- BOTTOM ACCENT BAR --}}
        <tr>
          <td style="background:linear-gradient(90deg,{{ $accentHex }},#0f172a);height:3px;font-size:0;line-height:0">&nbsp;</td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>
