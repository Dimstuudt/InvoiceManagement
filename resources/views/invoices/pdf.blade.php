<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>{{ $invoice->invoice_number }}</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  @page { size: A4 portrait; margin: 0; }
  body { margin: 0; background: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .invoice-paper { width: 794px; margin: 0 auto; background: white; font-family: Arial, sans-serif; }
  .mono { font-family: 'Courier New', Courier, monospace; }
</style>
</head>
<body>
@php
  $BLN = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  $tgl = fn($d) => $d ? $d->format('d') . ' ' . $BLN[(int)$d->format('n')] . ' ' . $d->format('Y') : '-';
  $headerImg = $imgB64($invoice->documentIssuer?->header_image_url);
  $logoImg   = $imgB64($invoice->bankAccount?->bank_logo_url);
  $sigImg    = $imgB64($invoice->signature?->signature_image_url);
@endphp
<div class="invoice-paper">

  {{-- KOP SURAT --}}
  @if($invoice->documentIssuer)
    @if($headerImg)
      <img src="{{ $headerImg }}" class="w-full object-contain" style="max-height:160px" alt="Kop"/>
      <div class="border-b-4 border-gray-800"></div>
    @else
      <div class="border-b-2 border-gray-800 py-6 px-20">
        <p class="text-2xl font-black text-gray-900 tracking-wide uppercase">{{ $invoice->documentIssuer->name }}</p>
        <div class="flex gap-x-4 mt-1 text-xs text-gray-500">
          @if($invoice->documentIssuer->tax_id_name)<span>{{ $invoice->documentIssuer->tax_id_name }}</span>@endif
          @if($invoice->documentIssuer->tax_id_address)<span>{{ $invoice->documentIssuer->tax_id_address }}</span>@endif
        </div>
      </div>
    @endif
  @endif

  {{-- BODY --}}
  <div style="padding: 2rem 5rem 3rem 5rem">

    {{-- Title + Meta --}}
    <div class="flex items-start justify-between gap-6 mb-8">
      <div>
        <h1 class="text-4xl font-black text-gray-900 tracking-widest uppercase">Invoice</h1>
        <p class="mono text-sm text-gray-500 mt-1.5 tracking-wider">{{ $invoice->invoice_number }}</p>
        @if($invoice->spk_number)
          <p class="text-xs text-gray-400 mt-0.5">No. SPK: {{ $invoice->spk_number }}</p>
        @endif
      </div>
      <div class="shrink-0 text-right">
        <table class="text-sm" style="border-spacing:0 5px;border-collapse:separate;">
          <tr>
            <td class="text-xs text-gray-400 font-medium pr-4 text-right whitespace-nowrap">Issue Date</td>
            <td class="font-semibold text-gray-700">{{ $tgl($invoice->issue_date) }}</td>
          </tr>
          <tr>
            <td class="text-xs text-gray-400 font-medium pr-4 text-right whitespace-nowrap">Due Date</td>
            <td class="font-bold text-red-600">{{ $tgl($invoice->due_date) }}</td>
          </tr>
          @if($invoice->projectCategory)
          <tr>
            <td class="text-xs text-gray-400 font-medium pr-4 text-right whitespace-nowrap">Project</td>
            <td class="text-gray-600">{{ $invoice->projectCategory->name }}</td>
          </tr>
          @endif
        </table>
      </div>
    </div>

    <hr class="border-gray-300 mb-7"/>

    {{-- Kepada --}}
    <div class="mb-7">
      <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Kepada Yth.</p>
      <p class="text-lg font-bold text-gray-900">{{ $invoice->client?->company_name }}</p>
      @if($invoice->client?->address)
        <p class="text-sm text-gray-600 mt-1 leading-relaxed">{{ $invoice->client->address }}@if($invoice->client->city), {{ $invoice->client->city }}@endif</p>
      @endif
      @if($invoice->attention)
        <p class="text-sm text-gray-500 mt-2">u.p. <span class="font-semibold text-gray-700">{{ $invoice->attention }}</span></p>
      @endif
    </div>

    {{-- Notes --}}
    @if($invoice->notes)
    <div class="mb-7">
      <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Keterangan</p>
      <div class="border-l-4 border-gray-200 pl-4">
        <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $invoice->notes }}</p>
      </div>
    </div>
    @endif

    <hr class="border-gray-200 mb-7"/>

    {{-- Items --}}
    <div class="mb-8">
      <table class="w-full">
        <thead>
          <tr class="border-b-2 border-gray-800">
            <th class="text-left text-xs font-bold text-gray-500 uppercase tracking-wider pb-2.5 w-8">No</th>
            <th class="text-left text-xs font-bold text-gray-500 uppercase tracking-wider pb-2.5">Deskripsi</th>
            <th class="text-right text-xs font-bold text-gray-500 uppercase tracking-wider pb-2.5 w-48">Harga</th>
          </tr>
        </thead>
        <tbody>
          @foreach($invoice->items as $i => $item)
          <tr class="border-b border-gray-100{{ $loop->last ? ' border-gray-300' : '' }}">
            <td class="py-3 text-sm text-gray-400 pr-3">{{ $i + 1 }}</td>
            <td class="py-3 text-sm text-gray-800 pr-6">{{ $item->description }}</td>
            <td class="py-3 text-sm text-right mono text-gray-800">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <div class="mt-5 pt-4 border-t-2 border-gray-800 flex justify-end">
        <div class="text-right">
          <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total</p>
          <p class="text-2xl font-black text-gray-900">Rp {{ number_format($invoice->total, 0, ',', '.') }}</p>
        </div>
      </div>
    </div>

    <hr class="border-gray-200 mb-7"/>

    {{-- Bank + Signature --}}
    <div class="flex items-end justify-between gap-10">

      @if($invoice->bankAccount)
      <div>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Pembayaran Ke</p>
        <div class="flex items-center gap-3 mb-2">
          @if($logoImg)
          <div class="w-12 h-12 rounded-lg border border-gray-100 bg-gray-50 flex items-center justify-center p-1.5 shrink-0">
            <img src="{{ $logoImg }}" class="w-full h-full object-contain"/>
          </div>
          @endif
          <div>
            <p class="font-bold text-gray-900">{{ $invoice->bankAccount->bank_name }}</p>
            <p class="text-xs text-gray-500">a/n {{ $invoice->bankAccount->name }}</p>
          </div>
        </div>
        <div class="inline-flex items-center gap-2 border border-gray-200 rounded-lg px-4 py-2 bg-gray-50">
          <span class="text-xs font-black text-gray-300">#</span>
          <span class="mono font-bold text-gray-800 tracking-widest">{{ $invoice->bankAccount->account_number }}</span>
        </div>
      </div>
      @else
      <div></div>
      @endif

      @if($invoice->with_signature && $invoice->signature)
      <div class="text-center" style="min-width:180px">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Hormat Kami,</p>
        <div class="flex items-center justify-center" style="height:80px">
          @if($sigImg)
            <img src="{{ $sigImg }}" class="max-h-full object-contain" style="max-width:180px" alt="TTD"/>
          @endif
        </div>
        <div class="border-b border-gray-500 mt-1 mb-2 mx-4"></div>
        <p class="font-bold text-gray-900 text-sm">{{ $invoice->signature->name }}</p>
        <p class="text-xs text-gray-500 mt-0.5">{{ $invoice->signature->position }}</p>
      </div>
      @endif

    </div>
  </div>

  {{-- Footer --}}
  @if($invoice->documentIssuer && ($invoice->documentIssuer->tax_id_name || $invoice->documentIssuer->tax_id_address))
  <div class="border-t-2 border-gray-800 py-3 text-center px-20">
    <p class="text-xs text-gray-500 leading-relaxed">
      @if($invoice->documentIssuer->tax_id_name){{ $invoice->documentIssuer->tax_id_name }}@endif
      @if($invoice->documentIssuer->tax_id_name && $invoice->documentIssuer->tax_id_address) &nbsp;|&nbsp; @endif
      @if($invoice->documentIssuer->tax_id_address){{ $invoice->documentIssuer->tax_id_address }}@endif
      @if($invoice->documentIssuer->tax_id_number)&nbsp;&nbsp;NPWP: {{ $invoice->documentIssuer->tax_id_number }}@endif
    </p>
  </div>
  @endif

</div>
</body>
</html>
