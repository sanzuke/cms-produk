@extends('layouts.site')

@section('title', 'Permintaan Diterima')
@section('description', 'Permintaan penawaran Anda sudah kami terima. Tim kami akan menghubungi Anda untuk langkah berikutnya.')
@section('canonical', route('order.thanks'))
@section('head')
<meta name="robots" content="noindex">
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('site/css/pricing.css') }}">
<link rel="stylesheet" href="{{ asset('site/css/order.css') }}">
@endpush

@section('content')
  <section class="page-hero">
    <div class="container">
      <nav class="crumbs" aria-label="Remah roti"><a href="{{ route('home') }}">Beranda</a> <span aria-hidden="true">/</span> <a href="{{ route('order.create') }}">Pesan</a> <span aria-hidden="true">/</span> <span aria-current="page">Terima kasih</span></nav>
      <span class="eyebrow">Pesan &amp; Penawaran</span>
      <h1>Permintaan Anda Tercatat.</h1>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="confirm">
        <span class="confirm__tick" aria-hidden="true">✓</span>
        <h2>Terima kasih, permintaan sudah kami terima</h2>
        @if ($ref)
          <p>Nomor referensi <strong>{{ $ref }}</strong>. Simpan nomor ini bila Anda menghubungi kami terkait permintaan ini.</p>
        @else
          <p>Permintaan Anda sudah masuk ke tim kami.</p>
        @endif

        <div class="recap card">
          <h3>Langkah berikutnya</h3>
          <ol class="steps-next">
            <li><span><strong>Tinjauan awal.</strong> Tim sales memeriksa data lokasi, tanggal, dan kebutuhan Anda, biasanya dalam 1–2 hari kerja.</span></li>
            <li><span><strong>Kami menghubungi Anda</strong> lewat WhatsApp atau email untuk konfirmasi dan melengkapi data, termasuk berkas izin masuk site.</span></li>
            <li><span><strong>Survei atau diskusi singkat</strong> bila diperlukan, terutama untuk proyek custom atau multi-site.</span></li>
            <li><span><strong>Penawaran tertulis</strong> lengkap dengan mobilisasi dan jadwal. Jadwal dikunci setelah DP dibayar.</span></li>
          </ol>
        </div>

        <div class="btn-row">
          <a href="{{ route('projects.index') }}" class="btn btn--ghost">Lihat Portofolio</a>
          <a href="{{ route('packages.index') }}" class="btn btn--primary">Kembali ke Paket</a>
        </div>
      </div>
    </div>
  </section>
@endsection
