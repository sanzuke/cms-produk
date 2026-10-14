@php
    use Illuminate\Support\Number;
    $order = $invoice->order;
@endphp
<x-mail::message>
# Invoice {{ $invoice->number }}

Yth. {{ $order->client_name }},

Berikut kami sampaikan invoice **{{ \App\Models\Invoice::TYPES[$invoice->type] ?? $invoice->type }}** untuk pekerjaan *{{ $order->title }}* ({{ $order->number }}). Rincian lengkap ada pada PDF terlampir.

<x-mail::table>
| | |
|:--|:--|
| Nomor invoice | {{ $invoice->number }} |
| Jumlah tagihan | **{{ Number::currency($invoice->amount, 'IDR', 'id', 0) }}** |
| Jatuh tempo | {{ $invoice->due_date?->translatedFormat('d F Y') ?: '-' }} |
</x-mail::table>

@if (config('site.bank_account'))
**Pembayaran ke rekening**

{{ config('site.bank_name') }} — {{ config('site.bank_account') }}<br>
a.n. {{ config('site.bank_holder') }}

@endif
Setelah transfer, mohon kirimkan bukti pembayaran kepada kami melalui balasan email ini{{ config('site.phone_display') ? ' atau hubungi '.config('site.phone_display') : '' }}.

Terima kasih,<br>
{{ config('site.name') }}
</x-mail::message>
