@extends('layouts.site')

@section('title', 'Syarat & Ketentuan')
@section('description', 'Syarat dan ketentuan pemesanan jasa dokumentasi: ruang lingkup, pembayaran, revisi, jadwal, kerahasiaan, dan hak penggunaan.')
@section('canonical', route('legal.terms'))

@push('styles')
<link rel="stylesheet" href="{{ asset('site/css/content.css') }}">
@endpush

@section('content')
  <section class="page-hero">
    <div class="container">
      <nav class="crumbs" aria-label="Remah roti"><a href="{{ route('home') }}">Beranda</a> <span aria-hidden="true">/</span> <span aria-current="page">Syarat &amp; Ketentuan</span></nav>
      <span class="eyebrow">Legal</span>
      <h1>Syarat &amp; Ketentuan.</h1>
      <p>Terakhir diperbarui: XX Oktober 2026 (placeholder).</p>
    </div>
  </section>

  <section class="section">
    <div class="container legal">
      <nav class="legal__toc" aria-label="Daftar isi">
        <ol>
          <li><a href="#ruang-lingkup">Ruang lingkup</a></li>
          <li><a href="#pemesanan">Pemesanan &amp; pembayaran</a></li>
          <li><a href="#jadwal">Jadwal &amp; perubahan</a></li>
          <li><a href="#lokasi">Akses lokasi, K3 &amp; izin</a></li>
          <li><a href="#revisi">Revisi &amp; penyerahan</a></li>
          <li><a href="#kerahasiaan">Kerahasiaan</a></li>
          <li><a href="#hak">Hak penggunaan</a></li>
          <li><a href="#tanggung-jawab">Batas tanggung jawab</a></li>
        </ol>
      </nav>
      <article class="prose">
        <p class="legal__note"><strong>Draf desain.</strong> Teks di bawah hanya contoh struktur dan angka placeholder. Wajib ditinjau penasihat hukum sebelum dipublikasikan atau dipakai sebagai kontrak.</p>

        <h2 id="ruang-lingkup">1. Ruang lingkup</h2>
        <p>Layanan mencakup pengambilan foto, video, dan aerial sesuai paket atau penawaran tertulis yang disetujui. Hal di luar penawaran dikerjakan sebagai tambahan berbayar atas persetujuan kedua pihak.</p>

        <h2 id="pemesanan">2. Pemesanan &amp; pembayaran</h2>
        <ul>
          <li>Jadwal terkunci setelah uang muka (XX% dari nilai pesanan) diterima.</li>
          <li>Pelunasan dilakukan sebelum berkas resolusi penuh diunduh, kecuali disepakati lain.</li>
          <li>Biaya mobilisasi dan akomodasi lokasi terpencil ditagihkan terpisah dan dicantumkan di penawaran.</li>
          <li>Harga belum termasuk pajak kecuali disebutkan.</li>
        </ul>

        <h2 id="jadwal">3. Jadwal &amp; perubahan</h2>
        <p>Pengubahan jadwal oleh klien diberitahukan paling lambat X hari kerja sebelumnya. Penundaan karena cuaca, larangan terbang, atau izin lokasi yang belum terbit tidak menghanguskan uang muka; jadwal diatur ulang dalam X hari kerja. <em>[Kebijakan final ditentukan pemilik usaha.]</em></p>

        <h2 id="lokasi">4. Akses lokasi, K3 &amp; izin</h2>
        <ul>
          <li>Klien menyediakan izin masuk lokasi, induksi K3, dan pendamping lapangan bila dipersyaratkan.</li>
          <li>Tim mengikuti aturan K3 dan perintah petugas HSE. Pekerjaan dihentikan bila kondisi dinilai tidak aman.</li>
          <li>Izin terbang drone mengikuti regulasi yang berlaku saat pekerjaan; klien membantu koordinasi di lokasi.</li>
        </ul>

        <h2 id="revisi">5. Revisi &amp; penyerahan</h2>
        <p>Setiap paket mencakup X kali revisi pada tahap penyuntingan. Revisi tambahan dikenai biaya. Hasil diserahkan melalui portal klien dan tersedia selama XX hari sebelum arsip dipindahkan.</p>

        <h2 id="kerahasiaan">6. Kerahasiaan</h2>
        <p>Seluruh materi dan informasi operasional diperlakukan rahasia. Kami tidak mempublikasikan materi, termasuk di portofolio, tanpa persetujuan tertulis. Perjanjian kerahasiaan (NDA) khusus dapat ditandatangani atas permintaan.</p>

        <h2 id="hak">7. Hak penggunaan</h2>
        <p>Klien mendapat lisensi untuk menggunakan hasil bagi keperluan internal dan komunikasi perusahaan sesuai lingkup di penawaran. Hak cipta tetap pada pembuat karya kecuali dialihkan secara tertulis. Penggunaan komersial di luar lingkup memerlukan persetujuan.</p>

        <h2 id="tanggung-jawab">8. Batas tanggung jawab</h2>
        <p>Tanggung jawab kami terbatas pada nilai pesanan terkait. Kami tidak bertanggung jawab atas keterlambatan akibat keadaan di luar kendali, termasuk cuaca ekstrem, pembatasan akses, dan kebijakan lokasi.</p>

        <p style="margin-top:var(--s-7)">Pertanyaan? <a href="{{ route('contact') }}" style="color:var(--c-accent)">Hubungi kami</a> atau lihat <a href="{{ route('faq') }}" style="color:var(--c-accent)">FAQ</a>.</p>
      </article>
    </div>
  </section>
@endsection
