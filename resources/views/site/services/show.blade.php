@extends('layouts.site')

@php
  $pageTitle = $service->seo_title ?: $service->title;
  $pageDesc = $service->seo_description ?: $service->summary;
  $orderUrl = route('order.create', ['layanan' => $service->slug]);
  $ctaLabel = $service->price_from ? 'Pesan Sekarang' : 'Minta Penawaran';
  $faqs = collect($service->faq ?? [])->filter(fn ($f) => ! empty($f['q']));
  $price = $service->price_from ? \Illuminate\Support\Number::currency($service->price_from, 'IDR', 'id', 0) : null;
  $jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => $service->title,
    'description' => $pageDesc,
    'url' => route('services.show', $service->slug),
    'areaServed' => config('site.service_area'),
    'provider' => ['@type' => 'Organization', 'name' => config('site.name'), 'url' => url('/')],
  ];
  if ($service->price_from) {
    $jsonLd['offers'] = ['@type' => 'Offer', 'priceCurrency' => 'IDR', 'price' => $service->price_from];
  }
@endphp

@section('title', $pageTitle)
@section('description', $pageDesc)
@section('canonical', route('services.show', $service->slug))
@if ($service->cover)
  @section('og_image', Storage::disk('public')->url($service->cover))
@endif

@section('head')
<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('site/css/services.css') }}">
@endpush

@section('content')
<section class="container svc-hero">
  <div>
    <nav class="crumbs" aria-label="Remah roti">
      <a href="{{ route('home') }}">Beranda</a> <span>/</span> <a href="{{ route('services.index') }}">Layanan</a> <span>/</span> <span aria-current="page">{{ $service->title }}</span>
    </nav>
    <span class="eyebrow">Layanan</span>
    <h1>{{ $service->title }}</h1>
    <p>{{ $service->summary }}</p>
    <div class="svc-hero__actions">
      <a href="{{ $orderUrl }}" class="btn btn--primary">{{ $ctaLabel }}</a>
      <a href="{{ route('packages.index') }}" class="btn btn--ghost">Lihat Paket</a>
    </div>
  </div>
  @if ($service->cover)
    <img class="svc-hero__img" src="{{ Storage::disk('public')->url($service->cover) }}" alt="{{ $service->title }}">
  @else
    <div class="ph ph--3x2" data-label="{{ $service->title }}"></div>
  @endif
</section>

@if (! empty($service->use_cases))
<section class="section section--alt">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Use Case</span>
      <h2>Cocok Untuk</h2>
    </div>
    <div class="grid grid--3">
      @foreach ($service->use_cases as $case)
        <div class="card use-card"><h4>{{ $case }}</h4></div>
      @endforeach
    </div>
  </div>
</section>
@endif

@if (! empty($service->deliverables) || filled($service->body))
<section class="section">
  <div class="container deliver">
    <div>
      <div class="section-head">
        <span class="eyebrow">Deliverable</span>
        <h2>Yang Anda Terima</h2>
      </div>
      @if (! empty($service->deliverables))
        <ul class="check-list">
          @foreach ($service->deliverables as $item)
            <li>{{ $item }}</li>
          @endforeach
        </ul>
      @endif
      @if (filled($service->body))
        <div class="prose">{!! $service->body !!}</div>
      @endif
    </div>
    <div class="card">
      <h3 style="margin-bottom:var(--s-5)">Fakta Singkat</h3>
      <dl class="deliver__facts">
        <div class="fact"><dt>Mulai dari</dt><dd>{{ $price ?? 'Via penawaran' }}</dd></div>
        <div class="fact"><dt>Deliverables</dt><dd>{{ count($service->deliverables ?? []) }} item</dd></div>
        <div class="fact"><dt>Serah terima</dt><dd>Portal klien privat</dd></div>
      </dl>
    </div>
  </div>
</section>
@endif

@if (filled($service->requirements))
<section class="section section--alt">
  <div class="container prereq">
    <div>
      <div class="section-head">
        <span class="eyebrow">Proses</span>
        <h2>Proses &amp; Prasyarat Lokasi</h2>
        <p>Supaya hari kerja tidak terbuang, ada beberapa hal yang perlu disiapkan bersama sebelum jadwal dikunci.</p>
      </div>
      <p>{!! nl2br(e($service->requirements)) !!}</p>
    </div>
    <div class="card prereq__box">
      <h4><span class="badge badge--warn">Checklist</span></h4>
      <p>Induksi K3, APD lengkap, izin masuk, dan daftar area terlarang disiapkan klien sebelum hari kerja.</p>
    </div>
  </div>
</section>
@endif

@if ($related->isNotEmpty())
<section class="section">
  <div class="container">
    <div class="section-head section-head--row">
      <div>
        <span class="eyebrow">Portofolio</span>
        <h2>Galeri Terkait</h2>
        <p>Contoh pekerjaan dengan fokus yang sama. Nama klien dan lokasi disamarkan bila belum disetujui untuk dipublikasikan.</p>
      </div>
      <a href="{{ route('projects.index') }}" class="btn btn--ghost btn--sm">Semua portofolio</a>
    </div>
    <div class="related-grid">
      @foreach ($related as $project)
        <a class="card" href="{{ route('projects.show', $project->slug) }}">
          @if ($project->cover)
            <img src="{{ Storage::disk('public')->url($project->cover) }}" alt="{{ $project->title }}" loading="lazy">
          @else
            <div class="ph ph--4x5" data-label="{{ $project->title }}"></div>
          @endif
          <h4>{{ $project->title }}</h4>
          <p>{{ collect([ucfirst((string) $project->commodity), $project->year, $project->publicLocation()])->filter()->implode(' · ') }}</p>
        </a>
      @endforeach
    </div>
  </div>
</section>
@endif

<section class="section section--alt">
  <div class="container">
    <div class="card price-box">
      <div>
        <span class="eyebrow">Harga</span>
        @if ($price)
          <span class="price-box__amt"><small>Mulai dari</small>{{ $price }}</span>
          <p class="price-box__note">Harga final bergantung pada lokasi, jumlah hari, dan output. Ongkos mobilisasi dan akomodasi lokasi terpencil dihitung terpisah.</p>
        @else
          <span class="price-box__amt">Via penawaran</span>
          <p class="price-box__note">Kirim kebutuhan Anda dan kami siapkan penawaran tertulis.</p>
        @endif
      </div>
      <div class="price-box__actions">
        <a href="{{ $orderUrl }}" class="btn btn--primary">{{ $ctaLabel }}</a>
        <a href="{{ route('packages.index') }}" class="btn btn--ghost">Lihat Paket</a>
      </div>
    </div>
  </div>
</section>

@if ($faqs->isNotEmpty())
<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">FAQ</span>
      <h2>Pertanyaan Layanan Ini</h2>
    </div>
    <div class="faq">
      @foreach ($faqs as $f)
        <details>
          <summary>{{ $f['q'] }}</summary>
          <p>{{ $f['a'] ?? '' }}</p>
        </details>
      @endforeach
    </div>
  </div>
</section>
@endif

<section class="section cta-band">
  <div class="container cta-band__inner">
    <h2>Siap mendokumentasikan area Anda?</h2>
    <div class="cta-band__actions">
      <a href="{{ $orderUrl }}" class="btn btn--primary">Minta Penawaran</a>
      <a href="{{ route('projects.index') }}" class="btn btn--ghost">Lihat Portofolio</a>
    </div>
  </div>
</section>
@endsection
