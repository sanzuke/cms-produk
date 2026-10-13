@extends('layouts.site')
@section('title', 'Portofolio')
@section('description', 'Studi kasus dokumentasi tambang: foto operasional, video perusahaan, dan aerial drone. Filter berdasarkan jenis kerja, komoditas, area, dan tahun.')
@section('canonical', route('projects.index'))
@push('styles')<link rel="stylesheet" href="{{ asset('site/css/portfolio.css') }}">@endpush

@php
    $rows = [
        'jenis' => ['Jenis', ['foto' => 'Foto', 'video' => 'Video', 'aerial' => 'Aerial']],
        'komoditas' => ['Komoditas', \App\Filament\Resources\Projects\Schemas\ProjectForm::COMMODITIES],
        'area' => ['Area', ['pit' => 'Pit', 'hauling' => 'Hauling', 'plant' => 'Plant', 'pelabuhan' => 'Pelabuhan', 'reklamasi' => 'Reklamasi']],
        'tahun' => ['Tahun', collect($years)->mapWithKeys(fn ($y) => [$y => $y])->all()],
    ];
@endphp

@section('content')
<main>
  <section class="page-hero">
    <div class="container">
      <nav class="crumbs" aria-label="Remah roti">
        <a href="{{ route('home') }}">Beranda</a> <span>/</span> <span aria-current="page">Portofolio</span>
      </nav>
      <span class="eyebrow">Studi Kasus</span>
      <h1>Portofolio</h1>
      <p>Contoh pekerjaan nyata di lapangan. Beberapa nama klien dan lokasi disamarkan karena belum ada izin publikasi.</p>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="filters" role="group" aria-label="Filter portofolio">
        @foreach ($rows as $param => [$title, $options])
          <div class="filters__row">
            <span class="filters__label" id="f-{{ $param }}">{{ $title }}</span>
            <div class="chips" role="group" aria-labelledby="f-{{ $param }}">
              @php $others = \Illuminate\Support\Arr::except($active, $param); @endphp
              <a class="chip" href="{{ route('projects.index', $others) }}" aria-pressed="{{ isset($active[$param]) ? 'false' : 'true' }}">Semua</a>
              @foreach ($options as $value => $text)
                <a class="chip" href="{{ route('projects.index', $others + [$param => $value]) }}" aria-pressed="{{ ($active[$param] ?? null) === (string) $value ? 'true' : 'false' }}">{{ $text }}</a>
              @endforeach
            </div>
          </div>
        @endforeach

        <div class="filters__bar">
          <span>Menampilkan <b>{{ $projects->total() }}</b> proyek</span>
          @if ($active)
            <a class="filters__reset" href="{{ route('projects.index') }}">Reset filter</a>
          @endif
        </div>
      </div>

      @if ($projects->isEmpty())
        <div class="pf-empty">
          <h3>Tidak ada proyek yang cocok</h3>
          <p>Coba longgarkan filter atau hubungi kami untuk melihat portofolio lengkap yang tidak dipublikasikan.</p>
        </div>
      @else
        <div class="pf-grid">
          @foreach ($projects as $project)
            @include('site.projects._card', ['project' => $project, 'showArea' => true, 'showClient' => true])
          @endforeach
        </div>
        @if ($projects->hasPages())
          <div style="margin-top:var(--s-6)">{{ $projects->links() }}</div>
        @endif
      @endif
    </div>
  </section>

  <section class="section cta-band">
    <div class="container cta-band__inner">
      <h2>Punya proyek yang mirip salah satu di atas?</h2>
      <div class="cta-band__actions">
        <a href="{{ route('order.create') }}" class="btn btn--primary">Minta Penawaran</a>
        <a href="{{ route('services.index') }}" class="btn btn--ghost">Lihat Layanan</a>
      </div>
    </div>
  </section>
</main>
@endsection
