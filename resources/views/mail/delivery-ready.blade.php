<x-mail::message>
# Hasil dokumentasi Anda sudah siap

Halo {{ $order->client_name }},

hasil dokumentasi **{{ $order->title }}**@if ($order->site) di {{ $order->site }}@endif ({{ $order->number }}) kini bisa dilihat di portal klien kami: {{ $fileCount }} berkas, lengkap dengan pratinjau foto, daftar tagihan, dan kolom komentar revisi.

<x-mail::button :url="$portalUrl">
Buka portal klien
</x-mail::button>

@if ($locked)
Unduhan berkas penuh dibuka setelah tagihan pelunasan lunas. Pratinjau foto tetap bisa Anda lihat sekarang.
@endif

@if ($expiresAt)
Link ini berlaku sampai **{{ $expiresAt->translatedFormat('d F Y') }}**. Unduh berkas yang Anda butuhkan sebelum tanggal tersebut.
@endif

Link ini bersifat pribadi; mohon tidak dibagikan ke pihak lain. Bila ada pertanyaan, balas email ini atau hubungi {{ config('site.email') }}@if (config('site.phone_display')) / {{ config('site.phone_display') }}@endif.

Salam,<br>
{{ config('site.name') }}
</x-mail::message>
