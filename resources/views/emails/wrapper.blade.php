<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<!--[if mso]><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml><![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:'Helvetica Neue',Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%">
@php
  $issuerName  = $invoice->documentIssuer?->name ?? config('app.name');
  $taxAddress  = $invoice->documentIssuer?->tax_id_address;
  $brandLogoPath = public_path('4vm1.png');
  $brandLogo     = file_exists($brandLogoPath)
      ? 'data:image/png;base64,' . base64_encode(file_get_contents($brandLogoPath))
      : null;
  $monogram    = mb_strtoupper(mb_substr($issuerName, 0, 2));
  $isReceipt   = str_starts_with($filename, 'RCP-');
  $headerBg    = $isReceipt ? '#065f46' : '#312e81';
  $chipBg      = $isReceipt ? '#d1fae5' : '#ede9fe';
  $chipText    = $isReceipt ? '#064e3b' : '#4338ca';
  $accentHex   = $isReceipt ? '#059669' : '#4f46e5';
  $typeLabel   = $isReceipt ? 'BUKTI PENERIMAAN PEMBAYARAN' : 'INVOICE';
@endphp

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f1f5f9">
  <tr>
    <td align="center" style="padding:36px 16px">

      <!--[if mso]><table width="600" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
      <table cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.10)">

        {{-- ── HEADER ─────────────────────────────────── --}}
        <tr>
          <td style="background-color:{{ $headerBg }};padding:32px 40px;text-align:center">

            @if($brandLogo)
            <img src="{{ $brandLogo }}" alt="{{ $issuerName }}"
                 style="display:block;margin:0 auto;max-height:72px;max-width:240px;object-fit:contain;border:0">
            @else
            <div style="display:inline-block;width:56px;height:56px;background:rgba(255,255,255,0.15);border-radius:50%;font-size:22px;font-weight:700;color:#ffffff;line-height:56px;text-align:center;letter-spacing:-1px">{{ $monogram }}</div>
            <p style="margin:10px 0 0;font-size:18px;font-weight:700;color:#ffffff">{{ $issuerName }}</p>
            @endif

          </td>
        </tr>

        {{-- ── TYPE + INVOICE NUMBER ──────────────────── --}}
        <tr>
          <td style="padding:28px 40px 0;text-align:center">
            <p style="margin:0 0 10px;font-size:10px;letter-spacing:0.12em;text-transform:uppercase;color:#94a3b8;font-weight:600">{{ $typeLabel }}</p>
            <span style="display:inline-block;background:{{ $chipBg }};color:{{ $chipText }};font-size:13px;font-weight:700;padding:6px 18px;border-radius:999px;letter-spacing:0.04em;font-family:'Courier New',Courier,monospace">
              {{ $invoice->invoice_number }}
            </span>
          </td>
        </tr>

        {{-- ── DIVIDER ────────────────────────────────── --}}
        <tr>
          <td style="padding:24px 40px 0">
            <div style="border-top:1px solid #e2e8f0"></div>
          </td>
        </tr>

        {{-- ── BODY ────────────────────────────────────── --}}
        <tr>
          <td style="padding:28px 40px 32px">
            <div style="font-size:14.5px;line-height:1.8;color:#374151">
              {!! nl2br(e($body)) !!}
            </div>
          </td>
        </tr>

        {{-- ── ATTACHMENT CALLOUT ──────────────────────── --}}
        <tr>
          <td style="padding:0 40px 32px">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px">
              <tr>
                <td style="padding:14px 20px">
                  <table cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td style="font-size:20px;padding-right:12px;vertical-align:middle">📎</td>
                      <td style="vertical-align:middle">
                        <p style="margin:0 0 2px;font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em;font-weight:600">Lampiran</p>
                        <p style="margin:0;font-size:13px;color:#1e293b;font-weight:600;font-family:'Courier New',Courier,monospace">{{ $filename }}</p>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        {{-- ── FOOTER ──────────────────────────────────── --}}
        <tr>
          <td style="background:#f8fafc;border-top:1px solid #e5e7eb;padding:24px 40px;text-align:center">
            {{-- Accent line --}}
            <div style="width:32px;height:3px;background:{{ $accentHex }};border-radius:999px;margin:0 auto 16px"></div>

            <p style="margin:0 0 4px;font-size:12px;font-weight:700;color:#475569;letter-spacing:0.03em">{{ $issuerName }}</p>

            @if($taxAddress)
              <p style="margin:0 0 14px;font-size:11px;color:#94a3b8;line-height:1.6">{{ $taxAddress }}</p>
            @else
              <div style="margin-bottom:14px"></div>
            @endif

            <p style="margin:0;font-size:11px;color:#cbd5e1;line-height:1.6">
              Email ini dikirim secara otomatis &mdash; mohon tidak membalas langsung ke email ini.
            </p>
          </td>
        </tr>

      </table>
      <!--[if mso]></td></tr></table><![endif]-->

    </td>
  </tr>
</table>
</body>
</html>
