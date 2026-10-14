<x-mail::message>
@if ($kind === 'proof')
# Klien mengunggah bukti pembayaran

**{{ $order->client_name }}**@if ($order->client_company) ({{ $order->client_company }})@endif mengunggah bukti bayar untuk invoice **{{ $invoice?->number }}** pada order {{ $order->number }} ({{ $order->title }}).

@if ($invoice)
Jumlah tagihan: {{ \Illuminate\Support\Number::currency($invoice->amount, 'IDR', 'id', 0) }}. Periksa mutasi rekening, lalu tandai invoice lunas bila dana sudah masuk.
@endif

<x-mail::button :url="$invoiceUrl ?? $adminUrl">
Buka invoice
</x-mail::button>
@else
# Komentar revisi baru dari klien

**{{ $order->client_name }}** menulis di order {{ $order->number }} ({{ $order->title }}):

<x-mail::panel>
{{ $comment }}
</x-mail::panel>

<x-mail::button :url="$adminUrl">
Buka order
</x-mail::button>
@endif

Balas email ini untuk menghubungi klien langsung.
</x-mail::message>
