<x-mail::message>
# Penawaran {{ $quotation->number }}

Yth. {{ $quotation->client_name }}@if ($quotation->client_company), {{ $quotation->client_company }}@endif,

Terima kasih atas minat Anda. Terlampir penawaran untuk **{{ $quotation->title }}**.

<x-mail::table>
| | |
|:--|:--|
| Nomor | {{ $quotation->number }} |
| Total | **{{ \Illuminate\Support\Number::currency($quotation->total, 'IDR', 'id', 0) }}** |
@if ($quotation->valid_until)
| Berlaku sampai | {{ $quotation->valid_until->translatedFormat('d F Y') }} |
@endif
</x-mail::table>

Untuk menyetujui penawaran ini atau bila ada yang ingin didiskusikan, cukup balas email ini. Setelah Anda konfirmasi, kami akan menerbitkan invoice DP dan mengunci jadwal.

Salam,<br>
{{ config('site.name') }}@if (config('site.phone_display')) · {{ config('site.phone_display') }}@endif
</x-mail::message>
