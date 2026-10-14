@php
    use Illuminate\Support\Number;
    $order = $invoice->order;
    $money = fn ($n) => Number::currency((int) $n, 'IDR', 'id', 0);
    $paidBefore = (int) $order->invoices()->where('status', 'paid')->where('id', '!=', $invoice->id)->sum('amount');
    $remaining = max(0, $order->total - $paidBefore - ($invoice->status === 'cancelled' ? 0 : $invoice->amount));
    $typeLabel = \App\Models\Invoice::TYPES[$invoice->type] ?? $invoice->type;
    $hasBank = filled(config('site.bank_name')) && filled(config('site.bank_account'));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Invoice {{ $invoice->number }}</title>
<style>
    @page { margin: 36px 42px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2933; line-height: 1.45; }
    table { border-collapse: collapse; width: 100%; }
    h1 { font-size: 24px; margin: 0; letter-spacing: 2px; color: #1f2933; }
    .brand { font-size: 16px; font-weight: bold; }
    .muted { color: #6b7280; }
    .label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 3px; }
    .rule { border-top: 2px solid #1f2933; margin: 14px 0 18px; }
    .lines th { background: #f3f4f6; text-align: left; padding: 7px 8px; font-size: 10px; border-bottom: 1px solid #d1d5db; }
    .lines td { padding: 9px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
    .right { text-align: right; }
    .sum td { padding: 4px 8px; }
    .sum .total td { border-top: 2px solid #1f2933; font-weight: bold; font-size: 13px; padding-top: 8px; }
    .box { border: 1px solid #d1d5db; padding: 10px 12px; }
    .warn { border: 1px solid #d97706; background: #fffbeb; color: #92400e; padding: 10px 12px; }
    .stamp { display: inline-block; border: 3px solid #15803d; color: #15803d; font-size: 22px; font-weight: bold; letter-spacing: 3px; padding: 4px 16px; }
    .stamp-cancel { border-color: #6b7280; color: #6b7280; }
</style>
</head>
<body>
<table>
    <tr>
        <td style="width: 55%; vertical-align: top;">
            <div class="brand">{{ config('site.name') }}</div>
            <div class="muted">
                @if (config('site.email')){{ config('site.email') }}<br>@endif
                @if (config('site.phone_display')){{ config('site.phone_display') }}@endif
            </div>
        </td>
        <td class="right" style="vertical-align: top;">
            <h1>INVOICE</h1>
            <div style="font-size: 13px; font-weight: bold; margin-top: 4px;">{{ $invoice->number }}</div>
        </td>
    </tr>
</table>

<div class="rule"></div>

<table>
    <tr>
        <td style="width: 55%; vertical-align: top;">
            <div class="label">Ditagihkan kepada</div>
            <strong>{{ $order->client_name }}</strong><br>
            @if ($order->client_company){{ $order->client_company }}<br>@endif
            @if ($order->client_email){{ $order->client_email }}<br>@endif
            @if ($order->client_phone){{ $order->client_phone }}@endif
        </td>
        <td style="vertical-align: top;">
            <table>
                <tr><td class="muted">Tanggal terbit</td><td class="right">{{ ($invoice->created_at ?? now())->translatedFormat('d F Y') }}</td></tr>
                <tr><td class="muted">Jatuh tempo</td><td class="right">{{ $invoice->due_date?->translatedFormat('d F Y') ?: '-' }}</td></tr>
                <tr><td class="muted">Order</td><td class="right">{{ $order->number }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<table class="lines" style="margin-top: 22px;">
    <tr>
        <th>Deskripsi</th>
        <th class="right" style="width: 28%;">Jumlah</th>
    </tr>
    <tr>
        <td>
            <strong>{{ $typeLabel }}</strong> — {{ $order->title }}<br>
            <span class="muted">Order {{ $order->number }}@if ($order->site) · {{ $order->site }}@endif</span>
        </td>
        <td class="right"><strong>{{ $money($invoice->amount) }}</strong></td>
    </tr>
</table>

<table style="margin-top: 16px;">
    <tr>
        <td style="width: 50%; vertical-align: top;">
            @if ($invoice->status === 'paid')
                <div class="stamp">LUNAS</div>
                @if ($invoice->paid_at)<div class="muted" style="margin-top: 6px;">Dibayar {{ $invoice->paid_at->translatedFormat('d F Y') }}@if ($invoice->payment_ref) · Ref. {{ $invoice->payment_ref }}@endif</div>@endif
            @elseif ($invoice->status === 'cancelled')
                <div class="stamp stamp-cancel">DIBATALKAN</div>
            @endif
        </td>
        <td style="vertical-align: top;">
            <table class="sum">
                <tr><td>Total order</td><td class="right">{{ $money($order->total) }}</td></tr>
                <tr><td>Sudah dibayar</td><td class="right">{{ $money($paidBefore) }}</td></tr>
                <tr><td>Invoice ini</td><td class="right">{{ $money($invoice->amount) }}</td></tr>
                <tr class="total"><td>Sisa setelah invoice ini</td><td class="right">{{ $money($remaining) }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<div style="margin-top: 26px;">
    <div class="label">Instruksi pembayaran</div>
    @if ($hasBank)
        <div class="box">
            Transfer ke rekening:<br>
            <strong>{{ config('site.bank_name') }} — {{ config('site.bank_account') }}</strong><br>
            a.n. {{ config('site.bank_holder') }}<br>
            <span class="muted">Cantumkan nomor invoice {{ $invoice->number }} pada berita transfer dan kirimkan bukti pembayaran kepada kami.</span>
        </div>
    @else
        <div class="warn">Rekening pembayaran belum diatur. Silakan hubungi kami untuk informasi rekening sebelum melakukan pembayaran.</div>
    @endif
</div>

</body>
</html>
