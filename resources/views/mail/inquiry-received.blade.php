<x-mail::message>
# Terima kasih, {{ $inquiry->name }}

Permintaan penawaran Anda sudah kami terima dengan nomor referensi **{{ $reference }}**. Tim kami akan menghubungi Anda dalam 1×24 jam kerja untuk membahas lokasi, jadwal, dan lingkup pekerjaan.

<x-mail::table>
| | |
|:--|:--|
| Layanan | {{ $inquiry->service?->title ?: '-' }} |
| Lokasi / site | {{ $inquiry->site ?: '-' }} |
| Tanggal dibutuhkan | {{ $inquiry->needed_date?->translatedFormat('d F Y') ?: '-' }} |
</x-mail::table>

@if ($inquiry->brief)
**Catatan Anda**

{{ $inquiry->brief }}
@endif

Ada tambahan informasi? Balas email ini dengan menyebut nomor referensi.

Salam,<br>
{{ config('site.name') }}
</x-mail::message>
