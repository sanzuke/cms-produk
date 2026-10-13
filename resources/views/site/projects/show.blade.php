@extends('layouts.site')
@php
    use App\Filament\Resources\Projects\Schemas\ProjectForm as PF;
    $catLabel = PF::CATEGORIES[$project->category] ?? ucfirst($project->category);
    $short = explode(' / ', $catLabel)[0];
    $comLabel = PF::COMMODITIES[$project->commodity] ?? null;
    $areaLabel = PF::WORK_AREAS[$project->work_area] ?? null;
    $client = $project->publicClientName();
    $location = $project->publicLocation();
    $coverUrl = $project->cover ? Storage::disk('public')->url($project->cover) : null;
    $gallery = array_values(array_filter((array) $project->gallery));
    $intro = \Illuminate\Support\Str::limit(strip_tags((string) $project->challenge), 155);
@endphp
@section('title', $project->title.' — Studi Kasus')
@section('description', $intro ?: 'Studi kasus dokumentasi tambang: tantangan, pendekatan, dan hasil pekerjaan.')
@section('canonical', route('projects.show', $project->slug))
@if ($coverUrl)
  @section('og_image', $coverUrl)
@endif
@push('styles')<link rel="stylesheet" href="{{ asset('site/css/portfolio.css') }}">@endpush

@section('content')
<main>
  <section class="container case-hero">
    <nav class="crumbs" aria-label="Remah roti">
      <a href="{{ route('home') }}">Beranda</a> <span>/</span> <a href="{{ route('projects.index') }}">Portofolio</a> <span>/</span> <span aria-current="page">{{ $project->title }}</span>
    </nav>
    <div class="case-hero__meta">
      <span class="badge badge--accent">{{ $short }}</span>
      @if ($comLabel)<span class="badge">{{ $comLabel }}</span>@endif
      @if ($areaLabel)<span class="badge">{{ $areaLabel }}</span>@endif
      @if ($project->year)<span class="badge">{{ $project->year }}</span>@endif
    </div>
    <h1>{{ $project->title }}</h1>
    @if ($intro)<p>{{ $intro }}</p>@endif
  </section>

  <div class="container case-cover">
    @if ($coverUrl)
      <img src="{{ $coverUrl }}" alt="{{ $project->title }}">
    @else
      <div class="ph ph--16x9" data-label="Cover 16x9 — {{ $project->title }}"></div>
    @endif
  </div>

  <section class="section" style="padding-top:0">
    <div class="container case-layout">
      <div class="story">
        @foreach ([['Tantangan', $project->challenge], ['Pendekatan', $project->approach], ['Hasil', $project->result]] as $i => [$heading, $text])
          @if (filled($text))
            <div class="story__item">
              <span class="story__no" aria-hidden="true">{{ sprintf('%02d', $i + 1) }}</span>
              <div>
                <h3>{{ $heading }}</h3>
                <p>{!! nl2br(e($text)) !!}</p>
              </div>
            </div>
          @endif
        @endforeach

        <div>
          <div class="section-head">
            <span class="eyebrow">Galeri</span>
            <h2>Cuplikan Pekerjaan</h2>
          </div>
          <div class="gallery">
            @if ($gallery)
              @foreach ($gallery as $i => $path)
                <img class="{{ $i % 5 === 0 ? 'gallery__wide' : '' }}" src="{{ Storage::disk('public')->url($path) }}" alt="{{ $project->title }} — foto {{ $i + 1 }}" loading="lazy">
              @endforeach
            @else
              @foreach ([['gallery__wide', 'overview'], ['gallery__tall', 'detail'], ['', 'area kerja'], ['', 'peralatan'], ['gallery__wide', 'hasil']] as [$cls, $lbl])
                <div class="ph ph--3x2 {{ $cls }}" data-label="Foto 3x2 — {{ $lbl }}"></div>
              @endforeach
            @endif
          </div>
        </div>
      </div>

      <aside class="case-aside">
        <div class="card spec">
          <h4>Spesifikasi Pekerjaan</h4>
          <table class="table">
            <tbody>
              <tr><th scope="row">Jenis</th><td>{{ $catLabel }}</td></tr>
              @if ($comLabel)<tr><th scope="row">Komoditas</th><td>{{ $comLabel }}</td></tr>@endif
              @if ($areaLabel)<tr><th scope="row">Area</th><td>{{ $areaLabel }}</td></tr>@endif
              @if ($location)<tr><th scope="row">Lokasi</th><td>{{ $location }}</td></tr>@endif
              @if ($client)<tr><th scope="row">Klien</th><td>{{ $client }}</td></tr>@endif
              @if (filled($project->specs))<tr><th scope="row">Pelaksanaan</th><td>{{ $project->specs }}</td></tr>@endif
              @if ($project->year)<tr><th scope="row">Tahun</th><td>{{ $project->year }}</td></tr>@endif
            </tbody>
          </table>
          @if (! $client || ! $location)
            <p class="compare-cap" style="margin-top:var(--s-4)">Privasi: nama klien dan lokasi tidak ditampilkan atas permintaan.</p>
          @endif
        </div>
        <a href="{{ route('order.create') }}" class="btn btn--primary btn--block">Minta Penawaran Serupa</a>
        <a href="{{ route('projects.index') }}" class="btn btn--ghost btn--block" style="margin-top:var(--s-3)">Kembali ke Portofolio</a>
      </aside>
    </div>
  </section>

  @if ($related->isNotEmpty())
    <section class="section section--alt">
      <div class="container">
        <div class="section-head section-head--row">
          <div>
            <span class="eyebrow">Proyek Lain</span>
            <h2>Proyek Terkait</h2>
          </div>
          <a href="{{ route('projects.index') }}" class="btn btn--ghost btn--sm">Semua proyek</a>
        </div>
        <div class="related">
          @foreach ($related as $rel)
            @include('site.projects._card', ['project' => $rel])
          @endforeach
        </div>
      </div>
    </section>
  @endif

  <section class="section cta-band">
    <div class="container cta-band__inner">
      <h2>Butuh dokumentasi rutin seperti ini?</h2>
      <div class="cta-band__actions">
        <a href="{{ route('order.create') }}" class="btn btn--primary">Minta Penawaran</a>
        <a href="{{ route('services.index') }}" class="btn btn--ghost">Lihat Layanan</a>
      </div>
    </div>
  </section>
</main>
@endsection
