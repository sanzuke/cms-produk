<x-mail::message>
# Permintaan penawaran baru

**{{ $inquiry->name }}**@if ($inquiry->position), {{ $inquiry->position }}@endif
@if ($inquiry->company)
 — {{ $inquiry->company }}
@endif

<x-mail::table>
| | |
|:--|:--|
| Email | {{ $inquiry->email }} |
| WhatsApp / Telepon | {{ $inquiry->phone ?: '-' }} |
| Layanan | {{ $inquiry->service?->title ?: '-' }} |
| Lokasi / site | {{ $inquiry->site ?: '-' }} |
| Komoditas | {{ $inquiry->commodity ? ucfirst($inquiry->commodity) : '-' }} |
| Tanggal dibutuhkan | {{ $inquiry->needed_date?->translatedFormat('d F Y') ?: '-' }} |
| Perkiraan durasi | {{ $inquiry->duration ?: '-' }} |
| Lampiran | {{ count($inquiry->attachments ?? []) }} berkas (lihat di admin) |
</x-mail::table>

@if ($inquiry->brief)
**Kebutuhan**

{{ $inquiry->brief }}
@endif

<x-mail::button :url="$adminUrl">
Buka di admin
</x-mail::button>

Balas email ini untuk langsung menghubungi {{ $inquiry->name }}.
</x-mail::message>
