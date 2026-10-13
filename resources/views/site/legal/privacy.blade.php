@extends('layouts.site')

@section('title', 'Kebijakan Privasi')
@section('description', 'Data apa yang kami kumpulkan lewat situs dan formulir, untuk apa dipakai, bagaimana disimpan, dan hak Anda atas data tersebut.')
@section('canonical', route('legal.privacy'))

@push('styles')
<link rel="stylesheet" href="{{ asset('site/css/content.css') }}">
@endpush

@section('content')
  <section class="page-hero">
    <div class="container">
      <nav class="crumbs" aria-label="Remah roti"><a href="{{ route('home') }}">Beranda</a> <span aria-hidden="true">/</span> <span aria-current="page">Privasi</span></nav>
      <span class="eyebrow">Legal</span>
      <h1>Kebijakan Privasi.</h1>
      <p>Terakhir diperbarui: XX Oktober 2026 (placeholder).</p>
    </div>
  </section>

  <section class="section">
    <div class="container legal">
      <nav class="legal__toc" aria-label="Daftar isi">
        <ol>
          <li><a href="#data">Data yang dikumpulkan</a></li>
          <li><a href="#tujuan">Tujuan penggunaan</a></li>
          <li><a href="#materi">Materi proyek klien</a></li>
          <li><a href="#penyimpanan">Penyimpanan &amp; keamanan</a></li>
          <li><a href="#pihak-ketiga">Pihak ketiga</a></li>
          <li><a href="#hak">Hak Anda</a></li>
          <li><a href="#kontak">Kontak</a></li>
        </ol>
      </nav>
      <article class="prose">
        <p class="legal__note"><strong>Draf desain.</strong> Teks ini contoh struktur. Wajib disesuaikan dengan praktik nyata dan ditinjau penasihat hukum (termasuk kesesuaian dengan UU Pelindungan Data Pribadi) sebelum dipublikasikan.</p>

        <h2 id="data">1. Data yang dikumpulkan</h2>
        <ul>
          <li>Data formulir: nama, perusahaan, jabatan, email, nomor WhatsApp, lokasi site, dan isi brief.</li>
          <li>Berkas lampiran yang Anda unggah (brief, dokumen izin).</li>
          <li>Data teknis dasar: alamat IP, jenis peramban, dan halaman yang dibuka untuk keamanan dan analitik.</li>
        </ul>

        <h2 id="tujuan">2. Tujuan penggunaan</h2>
        <p>Data dipakai untuk menanggapi permintaan penawaran, menyiapkan penawaran dan invoice, menjadwalkan pekerjaan, mengirim hasil melalui portal klien, serta meningkatkan situs. Kami tidak menjual data pribadi.</p>

        <h2 id="materi">3. Materi proyek klien</h2>
        <p>Foto, video, dan data operasional dari lokasi klien diperlakukan sebagai informasi rahasia. Materi disimpan di galeri privat, tautan unduh dibatasi waktu, dan tidak ditampilkan di portofolio tanpa persetujuan tertulis.</p>

        <h2 id="penyimpanan">4. Penyimpanan &amp; keamanan</h2>
        <p>Data disimpan di penyedia penyimpanan terenkripsi dengan akses berbasis peran. Aktivitas admin dicatat. Data permintaan penawaran disimpan selama XX bulan, dan data order selama masa yang diwajibkan untuk pembukuan. <em>[Sesuaikan.]</em></p>

        <h2 id="pihak-ketiga">5. Pihak ketiga</h2>
        <ul>
          <li>Penyedia hosting dan penyimpanan berkas.</li>
          <li>Penyedia email dan, bila dipakai, gerbang pembayaran.</li>
          <li>Layanan analitik situs.</li>
        </ul>
        <p>Pihak-pihak tersebut hanya memproses data sebatas kebutuhan layanan.</p>

        <h2 id="hak">6. Hak Anda</h2>
        <p>Anda dapat meminta akses, perbaikan, atau penghapusan data pribadi Anda, serta menarik persetujuan, dengan menghubungi kami. Permintaan ditanggapi dalam X hari kerja.</p>

        <h2 id="kontak">7. Kontak</h2>
        <p>Pertanyaan soal privasi: <a href="{{ route('contact') }}" style="color:var(--c-accent)">halaman kontak</a> atau {{ config('site.email') }}.</p>
      </article>
    </div>
  </section>
@endsection
