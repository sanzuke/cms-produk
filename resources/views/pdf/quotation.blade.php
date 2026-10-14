@php
    use Illuminate\Support\Number;
    $rp = fn ($n) => Number::currency((int) $n, 'IDR', 'id', 0);
    $items = collect($quotation->items ?? [])->values();
    $qty = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, ',', '.'), '0'), ',');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Penawaran {{ $quotation->number }}</title>
<style>
    @page { margin: 36px 42px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1f2933; line-height: 1.45; }
    table { border-collapse: collapse; width: 100%; }
    .brand { font-size: 20px; font-weight: bold; letter-spacing: .5px; color: #111827; }
    .tagline { color: #6b7280; font-size: 9px; }
    .doc-title { font-size: 22px; font-weight: bold; text-align: right; color: #b45309; letter-spacing: 2px; }
    .muted { color: #6b7280; }
    .rule { border-top: 2px solid #111827; margin: 10px 0 14px; }
    .label { font-size: 8px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 3px; }
    .items th { background: #111827; color: #fff; font-size: 9px; text-align: left; padding: 6px 7px; }
    .items td { padding: 6px 7px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
    .r { text-align: right; }
    .c { text-align: center; }
    .totals td { padding: 4px 7px; }
    .grand td { font-size: 12px; font-weight: bold; background: #f3f4f6; border-top: 2px solid #111827; padding: 7px; }
    .box { border: 1px solid #d1d5db; padding: 8px 10px; }
    h3 { font-size: 10px; margin: 16px 0 4px; text-transform: uppercase; letter-spacing: 1px; }
    .sign { height: 54px; }
</style>
</head>
<body>

<table>
    <tr>
        <td style="width: 60%; vertical-align: top;">
            <div class="brand">{{ config('site.name') }}</div>
            <div class="tagline">{{ config('site.tagline') }}</div>
            <div class="muted" style="margin-top: 6px;">
                {{ config('site.email') }}<br>
                {{ config('site.phone_display') }}
            </div>
        </td>
        <td style="width: 40%; vertical-align: top;">
            <div class="doc-title">PENAWARAN</div>
            <table style="margin-top: 6px;">
                <tr><td class="muted">Nomor</td><td class="r"><strong>{{ $quotation->number }}</strong></td></tr>
                <tr><td class="muted">Tanggal</td><td class="r">{{ ($quotation->created_at ?? now())->translatedFormat('d F Y') }}</td></tr>
                @if ($quotation->valid_until)
                    <tr><td class="muted">Berlaku sampai</td><td class="r">{{ $quotation->valid_until->translatedFormat('d F Y') }}</td></tr>
                @endif
            </table>
        </td>
    </tr>
</table>

<div class="rule"></div>

<table>
    <tr>
        <td style="width: 55%; vertical-align: top;">
            <div class="label">Kepada</div>
            <strong>{{ $quotation->client_name }}</strong><br>
            @if ($quotation->client_company){{ $quotation->client_company }}<br>@endif
            {{ $quotation->client_email }}
            @if ($quotation->client_phone)<br>{{ $quotation->client_phone }}@endif
        </td>
        <td style="width: 45%; vertical-align: top;">
            <div class="label">Pekerjaan</div>
            <strong>{{ $quotation->title }}</strong>
        </td>
    </tr>
</table>

<table class="items" style="margin-top: 16px;">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th>Deskripsi</th>
            <th class="c" style="width: 9%;">Qty</th>
            <th style="width: 10%;">Satuan</th>
            <th class="r" style="width: 17%;">Harga satuan</th>
            <th class="r" style="width: 18%;">Jumlah</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item['description'] ?? '' }}</td>
                <td class="c">{{ $qty($item['qty'] ?? 0) }}</td>
                <td>{{ $item['unit'] ?? '' }}</td>
                <td class="r">{{ $rp($item['unit_price'] ?? 0) }}</td>
                <td class="r">{{ $rp(round((float) ($item['qty'] ?? 0) * (int) ($item['unit_price'] ?? 0))) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted c">Belum ada item.</td></tr>
        @endforelse
    </tbody>
</table>

<table class="totals" style="margin-top: 6px;">
    <tr>
        <td style="width: 55%;"></td>
        <td class="muted">Subtotal</td>
        <td class="r">{{ $rp($quotation->subtotal()) }}</td>
    </tr>
    @if ($quotation->mobilization > 0)
        <tr><td></td><td class="muted">Mobilisasi</td><td class="r">{{ $rp($quotation->mobilization) }}</td></tr>
    @endif
    @if ($quotation->discount > 0)
        <tr><td></td><td class="muted">Diskon</td><td class="r">– {{ $rp($quotation->discount) }}</td></tr>
    @endif
    <tr class="grand"><td style="background: #fff; border: 0;"></td><td>TOTAL</td><td class="r">{{ $rp($quotation->computeTotal()) }}</td></tr>
</table>

@if (filled($quotation->notes))
    <h3>Catatan</h3>
    <div>{!! nl2br(e($quotation->notes)) !!}</div>
@endif

@if (filled($quotation->terms))
    <h3>Syarat &amp; ketentuan</h3>
    <div>{!! nl2br(e($quotation->terms)) !!}</div>
@endif

@if (filled(config('site.bank_account')))
    <h3>Pembayaran</h3>
    <div class="box">
        Transfer ke <strong>{{ config('site.bank_name') }}</strong><br>
        No. rekening: <strong>{{ config('site.bank_account') }}</strong><br>
        @if (filled(config('site.bank_holder')))a.n. {{ config('site.bank_holder') }}@endif
    </div>
@endif

<table style="margin-top: 22px; page-break-inside: avoid;">
    <tr>
        <td style="width: 60%;"></td>
        <td class="c">
            Hormat kami,
            <div class="sign"></div>
            <div style="border-top: 1px solid #1f2933; padding-top: 3px;">
                <strong>{{ $quotation->creator?->name ?: config('site.name') }}</strong><br>
                <span class="muted">{{ config('site.name') }}</span>
            </div>
        </td>
    </tr>
</table>

</body>
</html>
