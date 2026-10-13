@extends('layouts.site')

@section('title', 'Layanan Dokumentasi Tambang')
@section('description', 'Layanan fotografi, videografi, dan aerial drone untuk industri pertambangan: foto operasional, company profile, video safety, monitoring progres, sampai dokumentasi reklamasi.')
@section('canonical', route('services.index'))

@push('styles')
<link rel="stylesheet" href="{{ asset('site/css/services.css') }}">
@endpush

@section('content')
@php $wa = config('site.whatsapp'); @endphp
<section class="page-hero">
  <div class="container">
    <nav class="crumbs" aria-label="Remah roti">
      <a href="{{ route('home') }}">Beranda</a> <span>/</span> <span aria-current="page">Layanan</span>
    </nav>
    <span class="eyebrow">Fotografi · Videografi · Aerial</span>
    <h1>Layanan Dokumentasi Tambang</h1>
    <p>Layanan yang bisa dipesan terpisah atau digabung menjadi satu paket produksi — dari foto harian sampai monitoring bulanan.</p>
    <div class="svc-hero__actions">
      <a href="{{ route('order.create') }}" class="btn btn--primary">Minta Penawaran</a>
      <a href="{{ route('packages.index') }}" class="btn btn--ghost">Lihat Paket &amp; Harga</a>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head section-head--row">
      <div>
        <span class="eyebrow">Katalog</span>
        <h2>{{ $services->count() }} Layanan</h2>
        <p>Setiap layanan punya daftar deliverables, prasyarat lokasi, dan harga mulai dari yang jelas.</p>
      </div>
      <a href="{{ route('packages.index') }}" class="btn btn--ghost btn--sm">Butuh paket lengkap?</a>
    </div>

    <div class="svc-list">
      @forelse ($services as $service)
        <article class="card svc">
          @if ($service->cover)
            <div class="svc__media"><img src="{{ Storage::disk('public')->url($service->cover) }}" alt="{{ $service->title }}" loading="lazy"></div>
          @else
            <div class="ph ph--3x2 svc__media" data-label="{{ $service->title }}"></div>
          @endif
          <div class="svc__body">
            <span class="svc__num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
            <h3>{{ $service->title }}</h3>
            <p>{{ $service->summary }}</p>
            <div class="svc__foot">
              <span class="price">
                @if ($service->price_from)
                  <small>Mulai dari</small>{{ \Illuminate\Support\Number::currency($service->price_from, 'IDR', 'id', 0) }}
                @else
                  Via penawaran
                @endif
              </span>
              <a class="btn btn--ghost btn--sm" href="{{ route('services.show', $service->slug) }}">Lihat detail</a>
            </div>
          </div>
        </article>
      @empty
        <p>Layanan belum tersedia. Silakan hubungi kami untuk kebutuhan dokumentasi Anda.</p>
      @endforelse
    </div>
  </div>
</section>

<section class="section section--alt">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Alur</span>
      <h2>Cara Kerja</h2>
      <p>Lima langkah yang berlaku untuk semua layanan di halaman ini.</p>
    </div>
    <ol class="steps">
      <li><div><h4>Konsultasi &amp; Scope</h4><p>Diskusi tujuan, area, jumlah output, dan batasan privasi lewat WhatsApp atau penawaran tertulis.</p></div></li>
      <li><div><h4>Penawaran</h4><p>Rincian item, harga, ongkos mobilisasi terpisah, dan masa berlaku penawaran.</p></div></li>
      <li><div><h4>Perizinan &amp; Induksi</h4><p>Pengurusan izin masuk area dan izin terbang drone; kami mengikuti induksi K3 lokasi.</p></div></li>
      <li><div><h4>Pengambilan Gambar</h4><p>Eksekusi sesuai jadwal, dengan checklist izin dan kondisi sebelum mulai.</p></div></li>
      <li><div><h4>Penyerahan Hasil</h4><p>Hasil dikirim lewat portal klien privat, lengkap dengan penamaan file terstruktur.</p></div></li>
    </ol>
  </div>
</section>

<section class="section cta-band">
  <div class="container cta-band__inner">
    <h2>Belum yakin layanan mana yang pas?</h2>
    <div class="cta-band__actions">
      <a href="{{ route('order.create') }}" class="btn btn--primary">Minta Penawaran</a>
      <a href="{{ route('contact') }}" class="btn btn--ghost">Hubungi Kami</a>
    </div>
  </div>
</section>
@endsection
