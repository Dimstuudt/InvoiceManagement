<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<!--[if mso]><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml><![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#eef2f7;font-family:'Helvetica Neue',Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%">
@php
  $issuerName    = $invoice->documentIssuer?->name ?? config('app.name');
  $taxAddress    = $invoice->documentIssuer?->tax_id_address;
  $brandLogoPath = public_path('4vm1.png');
  $brandLogo     = file_exists($brandLogoPath)
      ? 'data:image/png;base64,' . base64_encode(file_get_contents($brandLogoPath))
      : null;
  $monogram  = mb_strtoupper(mb_substr($issuerName, 0, 2));
  $isReceipt = str_starts_with($filename, 'RCP-');

  $headerFrom = $isReceipt ? '#064e3b' : '#1e1b4b';
  $headerTo   = $isReceipt ? '#059669' : '#4338ca';
  $accentHex  = $isReceipt ? '#10b981' : '#6366f1';
  $chipBg     = $isReceipt ? '#d1fae5' : '#ede9fe';
  $chipText   = $isReceipt ? '#064e3b' : '#4338ca';
  $chipBorder = $isReceipt ? '#6ee7b7' : '#c4b5fd';
  $typeLabel  = $isReceipt ? 'BUKTI PENERIMAAN PEMBAYARAN' : 'INVOICE';
@endphp

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef2f7">
  <tr>
    <td align="center" style="padding:40px 16px">

      <!--[if mso]><table width="600" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
      <table cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,0.12)">

        {{-- ── TOP ACCENT BAR ─────────────────────────────── --}}
        <tr>
          <td style="background:linear-gradient(90deg,{{ $headerFrom }},{{ $accentHex }});height:4px;font-size:0;line-height:0">&nbsp;</td>
        </tr>

        {{-- ── HEADER ─────────────────────────────────────── --}}
        <tr>
          <td style="background:linear-gradient(135deg,{{ $headerFrom }} 0%,{{ $headerTo }} 100%);padding:36px 40px 28px;text-align:center">

            @if($brandLogo)
              <img src="{{ $brandLogo }}" alt="{{ $issuerName }}"
                   style="display:block;margin:0 auto;max-height:76px;max-width:240px;object-fit:contain;border:0">
            @else
              <div style="display:inline-block;width:64px;height:64px;background:rgba(255,255,255,0.15);border-radius:50%;font-size:26px;font-weight:700;color:#ffffff;line-height:64px;text-align:center;letter-spacing:-1px;border:2px solid rgba(255,255,255,0.25)">{{ $monogram }}</div>
              <p style="margin:10px 0 0;font-size:18px;font-weight:700;color:#ffffff;letter-spacing:0.02em">{{ $issuerName }}</p>
            @endif

            {{-- Decorative dot row --}}
            <table cellpadding="0" cellspacing="0" border="0" align="center" style="margin-top:20px">
              <tr>
                <td style="padding:0 3px"><div style="width:4px;height:4px;background:rgba(255,255,255,0.25);border-radius:50%"></div></td>
                <td style="padding:0 3px"><div style="width:4px;height:4px;background:rgba(255,255,255,0.50);border-radius:50%"></div></td>
                <td style="padding:0 3px"><div style="width:6px;height:6px;background:rgba(255,255,255,0.70);border-radius:50%"></div></td>
                <td style="padding:0 3px"><div style="width:4px;height:4px;background:rgba(255,255,255,0.50);border-radius:50%"></div></td>
                <td style="padding:0 3px"><div style="width:4px;height:4px;background:rgba(255,255,255,0.25);border-radius:50%"></div></td>
              </tr>
            </table>

          </td>
        </tr>

        {{-- ── TYPE LABEL + INVOICE NUMBER ────────────────── --}}
        <tr>
          <td style="padding:28px 40px 0;text-align:center">
            <p style="margin:0 0 12px;font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:#94a3b8;font-weight:700">{{ $typeLabel }}</p>
            <span style="display:inline-block;background:{{ $chipBg }};color:{{ $chipText }};font-size:14px;font-weight:700;padding:8px 24px;border-radius:999px;letter-spacing:0.05em;font-family:'Courier New',Courier,monospace;border:1.5px solid {{ $chipBorder }}">
              {{ $invoice->invoice_number }}
            </span>
          </td>
        </tr>

        {{-- ── DIVIDER ─────────────────────────────────────── --}}
        <tr>
          <td style="padding:24px 40px 0">
            <div style="border-top:1px solid #e2e8f0"></div>
          </td>
        </tr>

        {{-- ── BODY ────────────────────────────────────────── --}}
        <tr>
          <td style="padding:28px 40px 32px">
            <table cellpadding="0" cellspacing="0" border="0" width="100%">
              <tr>
                <td style="width:3px;background:{{ $accentHex }};border-radius:999px;vertical-align:top;font-size:0;line-height:1">&nbsp;</td>
                <td style="padding-left:16px;font-size:14.5px;line-height:1.9;color:#374151;vertical-align:top">
                  {!! nl2br(e($body)) !!}
                </td>
              </tr>
            </table>
          </td>
        </tr>

        {{-- ── FOOTER ──────────────────────────────────────── --}}
        <tr>
          <td style="background:#f8fafc;border-top:1px solid #e5e7eb;padding:28px 40px;text-align:center">

            {{-- Triple dash accent --}}
            <table cellpadding="0" cellspacing="0" border="0" align="center" style="margin-bottom:16px">
              <tr>
                <td style="width:16px;height:3px;background:{{ $accentHex }};border-radius:999px;opacity:0.4"></td>
                <td style="width:8px"></td>
                <td style="width:32px;height:3px;background:{{ $accentHex }};border-radius:999px"></td>
                <td style="width:8px"></td>
                <td style="width:16px;height:3px;background:{{ $accentHex }};border-radius:999px;opacity:0.4"></td>
              </tr>
            </table>

            <p style="margin:0 0 4px;font-size:13px;font-weight:700;color:#1e293b;letter-spacing:0.02em">{{ $issuerName }}</p>

            @if($taxAddress)
              <p style="margin:0 0 16px;font-size:11px;color:#94a3b8;line-height:1.7">{{ $taxAddress }}</p>
            @else
              <div style="margin-bottom:16px"></div>
            @endif

            <p style="margin:0;font-size:11px;color:#cbd5e1;line-height:1.7">
              Email ini dikirim secara otomatis &mdash; mohon tidak membalas langsung ke email ini.
            </p>
          </td>
        </tr>

        {{-- ── BOTTOM ACCENT BAR ───────────────────────────── --}}
        <tr>
          <td style="background:linear-gradient(90deg,{{ $accentHex }},{{ $headerFrom }});height:3px;font-size:0;line-height:0">&nbsp;</td>
        </tr>

      </table>
      <!--[if mso]></td></tr></table><![endif]-->

    </td>
  </tr>
</table>
</body>
</html>
