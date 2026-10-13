@extends('layouts.site')

@section('title', 'Kontak')
@section('description', 'Hubungi '.config('site.name').': WhatsApp, email, jam kerja, dan area layanan untuk permintaan penawaran dokumentasi tambang.')
@section('canonical', route('contact'))

@push('styles')
<link rel="stylesheet" href="{{ asset('site/css/pricing.css') }}">
<link rel="stylesheet" href="{{ asset('site/css/order.css') }}">
@endpush

@php
    $wa = config('site.whatsapp');
    $waUrl = $wa ? 'https://wa.me/'.$wa.'?text='.rawurlencode('Halo '.config('site.name').', saya ingin berdiskusi tentang dokumentasi tambang.') : null;
@endphp

@section('content')
  <section class="page-hero">
    <div class="container">
      <nav class="crumbs" aria-label="Remah roti"><a href="{{ route('home') }}">Beranda</a> <span aria-hidden="true">/</span> <span aria-current="page">Kontak</span></nav>
      <span class="eyebrow">Kontak</span>
      <h1>Respons ≤ 1× 24 Jam Kerja.</h1>
      <p>Chat WhatsApp untuk pertanyaan cepat, atau kirim permintaan penawaran lewat halaman <a href="{{ route('order.create') }}" class="link-accent">Pesan</a> agar kami bisa menyiapkan konteks sebelum menghubungi Anda.</p>
    </div>
  </section>

  <section class="section" aria-labelledby="h-contact">
    <div class="container contact-grid">

      <div>
        <span class="eyebrow">Informasi</span>
        <h2 id="h-contact">Hubungi Kami</h2>
        <ul class="info-list">
          <li><b>WhatsApp</b><span>@if ($waUrl)<a href="{{ $waUrl }}" class="link-accent" target="_blank" rel="noopener">{{ config('site.phone_display') }}</a>@else{{ config('site.phone_display') }}@endif</span></li>
          <li><b>Email</b><span><a href="mailto:{{ config('site.email') }}" class="link-accent">{{ config('site.email') }}</a></span></li>
          <li><b>Jam kerja</b><span>{{ config('site.hours') }} WIB</span></li>
          <li><b>Area layanan</b><span>{{ config('site.service_area') }} — di luar area tersebut ada ongkos mobilisasi tambahan</span></li>
        </ul>

        <h3>Sebelum Menghubungi</h3>
        <ul class="hint-list">
          <li>Sebutkan nama site dan komoditas utamanya.</li>
          <li>Jelaskan target hasil: laporan bulanan, company profile, atau materi promosi.</li>
          <li>Ingatkan tanggal shooting bila sudah pasti.</li>
        </ul>

        <div class="card" style="margin-top: var(--s-5)">
          <h3>Jam Kerja</h3>
          <p class="form-note">Di luar jam kerja, pesan WhatsApp tetap masuk dan akan dibalas pada hari kerja berikutnya.</p>
        </div>
      </div>

      <div>
        <div class="card">
          <span class="eyebrow">Penawaran</span>
          <h2>Minta Penawaran</h2>
          <p>Isi formulir pemesanan singkat berisi layanan, lokasi, dan kebutuhan Anda. Kami membalas dengan penawaran tertulis tanpa komitmen.</p>
          <div class="btn-row">
            <a href="{{ route('order.create') }}" class="btn btn--primary">Minta Penawaran</a>
            @if ($waUrl)<a href="{{ $waUrl }}" class="btn btn--ghost" target="_blank" rel="noopener">Chat WhatsApp</a>@endif
          </div>
          <p class="form-note" style="margin-top: var(--s-3)">Ingin melihat paket dan harga lebih dulu? Buka <a href="{{ route('packages.index') }}" class="link-accent">halaman Paket</a>.</p>
        </div>
      </div>

    </div>
  </section>

  <section class="section section--alt" aria-labelledby="h-map">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow">Lokasi</span>
        <h2 id="h-map">Area Kerja</h2>
        <p>Seluruh shooting dilakukan langsung di lapangan, bukan di studio. Alamat dan peta dapat dimuat di sini setelah lokasi disepakati.</p>
      </div>
      <div class="ph ph--16x9 map-ph" data-label="Peta placeholder 16:9" role="img" aria-label="Placeholder peta lokasi"></div>
    </div>
  </section>
@endsection
