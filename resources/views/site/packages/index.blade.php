@extends('layouts.site')

@use('Illuminate\Support\Number')

@section('title', 'Paket & Harga')
@section('description', 'Paket dokumentasi foto, video, dan aerial untuk industri pertambangan. Harga mulai dari, catatan mobilisasi, add-on, dan jalur penawaran custom.')
@section('canonical', route('packages.index'))

@push('styles')
<link rel="stylesheet" href="{{ asset('site/css/pricing.css') }}">
@endpush

@section('content')
  <!-- HERO -->
  <section class="page-hero">
    <div class="container">
      <nav class="crumbs" aria-label="Remah roti"><a href="{{ route('home') }}">Beranda</a> <span aria-hidden="true">/</span> <span aria-current="page">Paket</span></nav>
      <span class="eyebrow">Paket &amp; Harga</span>
      <h1>Dokumentasi Tambang, Harga Jelas.</h1>
      <p>Paket berbasis hari kerja dan cakupan area — bukan paket branding generik. Harga yang tertera adalah harga mulai dari dan Penawaran tertulis tetap menjadi acuan resmi.</p>
      <div class="page-hero__actions">
        <a href="{{ route('order.create') }}" class="btn btn--primary">Pesan Paket</a>
        <a href="#custom" class="btn btn--ghost">Proyek Custom</a>
      </div>
    </div>
  </section>

  <!-- PAKET -->
  <section class="section" aria-labelledby="h-paket">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow">Paket Standar</span>
        <h2 id="h-paket">Pilih Paket</h2>
        <p>Setiap paket berlaku untuk satu lokasi. Tambahan hari kerja, revisi, atau armada udara tersedia sebagai add-on.</p>
      </div>

      <div class="pkg-note" role="note">
        <span class="badge badge--warn">Penting</span>
        <p><strong>Mobilisasi dan akomodasi tidak termasuk dalam harga paket.</strong> Biaya transportasi, akomodasi, dan perjalanan ke lokasi terpencil dihitung terpisah dan selalu dicantumkan di penawaran tertulis.</p>
      </div>

      @if ($standard->isEmpty())
        <p class="pkg-empty">Paket standar belum tersedia saat ini. Silakan <a href="{{ route('order.create', ['jalur' => 'custom']) }}" class="link-accent">minta penawaran custom</a>.</p>
      @else
      <div class="pkg-grid pkg-grid--spaced">
        @foreach ($standard as $package)
          <article class="card pkg @if ($package->is_popular) pkg--popular @endif" aria-labelledby="p-{{ $package->slug }}">
            @if ($package->is_popular)
              <span class="pkg__flag">Populer</span>
            @endif
            <div class="ph ph--16x9 pkg__media" data-label="{{ $package->name }}" role="img" aria-label="Placeholder foto paket {{ $package->name }}"></div>
            <div class="pkg__body">
              <span class="pkg__kicker">Paket {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
              <h3 id="p-{{ $package->slug }}">{{ $package->name }}</h3>
              <p class="pkg__price"><span>Mulai dari</span>{{ Number::currency($package->price, 'IDR', 'id', 0) }}@if ($package->price_unit) <em>{{ $package->price_unit }}</em>@endif</p>
              @if ($package->description)
                <p class="pkg__desc">{{ $package->description }}</p>
              @endif
              @if (! empty($package->features))
                <ul class="pkg__spec">
                  @foreach ($package->features as $feature)
                    <li>{{ $feature }}</li>
                  @endforeach
                </ul>
              @endif
              <div class="pkg__foot">
                @if ($package->price_note)
                  <p class="pkg__term">{{ $package->price_note }}</p>
                @endif
                <a href="{{ route('order.create', ['paket' => $package->slug]) }}" class="btn @if ($package->is_popular) btn--primary @else btn--ghost @endif btn--block">Pesan Paket Ini</a>
              </div>
            </div>
          </article>
        @endforeach
      </div>
      @endif
    </div>
  </section>

  <!-- ADD-ON -->
  <section class="section section--alt" aria-labelledby="h-addon">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow">Add-on</span>
        <h2 id="h-addon">Tambahan Per Order</h2>
        <p>Harga add-on masih <strong>placeholder</strong> (Rp X) dan baru dikonfirmasi pada Penawaran tertulis. Pilihan add-on bisa dicantumkan saat Anda membuat pesanan di halaman <a href="{{ route('order.create') }}" class="link-accent">Pesan</a>.</p>
      </div>
      <div class="addon-table-wrap">
        <table class="table addon-table">
          <caption class="sr-only">Daftar add-on dan harga placeholder</caption>
          <thead>
            <tr><th scope="col">Add-on</th><th scope="col">Keterangan</th><th scope="col" class="num">Harga</th></tr>
          </thead>
          <tbody>
            @foreach ($addons as $addon)
              <tr><td>{{ $addon['label'] }}</td><td>{{ $addon['desc'] }}</td><td class="num">Rp X{{ $addon['unit'] ? ' '.$addon['unit'] : '' }}</td></tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- PERBANDINGAN -->
  <section class="section" aria-labelledby="h-compare">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow">Perbandingan</span>
        <h2 id="h-compare">Apa Saja yang Anda Dapatkan</h2>
        <p class="form-note">Placeholder: rincian di bawah contoh dan akan disesuaikan dengan paket resmi.</p>
      </div>
      <div class="compare-wrap">
        <table class="table compare">
          <caption class="sr-only">Perbandingan isi empat paket</caption>
          <thead>
            <tr>
              <th scope="col">Fitur</th>
              <th scope="col">Harian</th>
              <th scope="col">Aerial Lite</th>
              <th scope="col" class="is-popular-col">Profile Tambang</th>
              <th scope="col">Bulanan</th>
            </tr>
          </thead>
          <tbody>
            <tr><th scope="row">Foto terkurasi</th><td>80–120</td><td>30–50 (udara)</td><td class="is-popular-col">150</td><td>Titik tetap tiap bulan</td></tr>
            <tr><th scope="row">Video</th><td>—</td><td>60 detik</td><td class="is-popular-col">3–5 menit</td><td>Time-lapse</td></tr>
            <tr><th scope="row">Tim</th><td>1 fotografer</td><td>1 pilot drone</td><td class="is-popular-col">Foto + video + drone</td><td>1 fotografer udara</td></tr>
            <tr><th scope="row">Revisi termasuk</th><td>1×</td><td>1×</td><td class="is-popular-col">2×</td><td>1× / bulan</td></tr>
            <tr><th scope="row">Waktu hasil</th><td>≤ 3 hari kerja</td><td>≤ 3 hari kerja</td><td class="is-popular-col">≤ 7 hari kerja</td><td>≤ 5 hari kerja</td></tr>
            <tr><th scope="row">NDA</th><td>Opsional</td><td>Opsional</td><td class="is-popular-col">Tersedia</td><td>Tersedia</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- CUSTOM / ENTERPRISE -->
  <section id="custom" class="section section--alt" aria-labelledby="h-custom">
    <div class="container">
      @php
          $customPackage = $custom->first();
          $customFeatures = ! empty($customPackage?->features) ? $customPackage->features : [
              'Multi-site dalam satu kontrak',
              'Retainer tahunan & jadwal tetap',
              'Laporan & metrik khusus (ESG, K3, progres)',
              'NDA, galeri privat, opsi watermark',
              'Tim tambahan: editor video, pilot drone tambahan',
              'Portal klien untuk unduh hasil',
          ];
      @endphp
      <article class="card pkg pkg--custom">
        <div class="pkg__body pkg__body--wide">
          <span class="pkg__kicker">Enterprise / Custom</span>
          <h3 id="h-custom">{{ $customPackage?->name ?? 'Multi-site, Retainer, atau Proyek Besar' }}</h3>
          <p class="pkg__price"><span>Hubungi kami</span>Via penawaran</p>
          <p class="pkg__desc">{{ $customPackage?->description ?: 'Untuk kebutuhan yang tidak muat dalam paket di atas: beberapa lokasi sekaligus, kontrak tahunan, laporan khusus, NDA, dan tim tetap.' }}</p>
          <ul class="pkg__spec pkg__spec--2col">
            @foreach ($customFeatures as $feature)
              <li>{{ $feature }}</li>
            @endforeach
          </ul>
          <div class="pkg__foot pkg__foot--plain">
            <p class="pkg__term">Penawaran disusun setelah survei atau diskusi singkat</p>
            <div class="btn-row">
              <a href="{{ route('order.create', ['jalur' => 'custom']) }}" class="btn btn--primary">Minta Penawaran Custom</a>
              <a href="{{ route('contact') }}" class="btn btn--ghost">Hubungi Kami</a>
            </div>
          </div>
        </div>
      </article>
    </div>
  </section>

  <!-- CTA -->
  <section class="section">
    <div class="container cta-band__inner">
      <h2>Siap pesan paket atau perlu penawaran custom?</h2>
      <div class="btn-row">
        <a href="{{ route('order.create') }}" class="btn btn--primary">Mulai Pesan</a>
        <a href="{{ route('faq') }}" class="btn btn--ghost">Baca FAQ</a>
      </div>
    </div>
  </section>
@endsection
