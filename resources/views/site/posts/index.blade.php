@extends('layouts.site')

@section('title', 'Artikel')
@section('description', 'Wawasan seputar dokumentasi tambang: laporan progres, foto udara, K3 di lokasi, video company profile, dan pelaporan reklamasi.')
@section('canonical', route('posts.index'))

@push('styles')
<link rel="stylesheet" href="{{ asset('site/css/content.css') }}">
<link rel="stylesheet" href="{{ asset('site/css/posts.css') }}">
@endpush
@push('scripts')
<script src="{{ asset('site/js/artikel.js') }}" defer></script>
@endpush

@php
    use Illuminate\Support\Facades\Storage;
    $keep = fn (array $over) => route('posts.index', array_filter(array_merge(['tipe' => $tipe, 'kategori' => $kategori, 'q' => $q], $over), fn ($v) => $v !== null && $v !== ''));
    $filtered = $tipe || $kategori || $q !== '';
    $first = $posts->currentPage() === 1 && ! $filtered ? $posts->first() : null;
@endphp

@section('content')
  <section class="page-hero">
    <div class="container">
      <nav class="crumbs" aria-label="Remah roti"><a href="{{ route('home') }}">Beranda</a> <span aria-hidden="true">/</span> <span aria-current="page">Artikel</span></nav>
      <span class="eyebrow">Catatan dari Lapangan</span>
      <h1>Wawasan Dokumentasi Tambang.</h1>
      <p>Panduan praktis untuk tim HSE, humas, dan manajemen yang butuh dokumentasi visual yang akurat dan aman.</p>
    </div>
  </section>

  <section class="section">
    <div class="container">
      @if ($first)
        <article class="post-featured post">
          <a href="{{ route('posts.show', $first->slug) }}" aria-label="Cover: {{ $first->title }}">
            @if ($first->cover)
              <img class="post__img post__img--16x9" src="{{ Storage::disk('public')->url($first->cover) }}" alt="" loading="lazy">
            @else
              <span class="ph ph--16x9" data-label="Cover artikel unggulan" style="display:block"></span>
            @endif
          </a>
          <div style="display:grid;gap:var(--s-3)">
            <div class="post__meta"><span class="badge badge--accent">Terbaru</span>@if ($first->category)<span>{{ $first->category }}</span>@endif @if ($first->publish_at)<span>{{ $first->publish_at->translatedFormat('d M Y') }}</span>@endif</div>
            <a href="{{ route('posts.show', $first->slug) }}"><h2>{{ $first->title }}</h2></a>
            @if ($first->excerpt)<p>{{ $first->excerpt }}</p>@endif
            <a href="{{ route('posts.show', $first->slug) }}" class="btn btn--ghost btn--sm" style="justify-self:start">Baca Artikel</a>
          </div>
        </article>
      @endif

      <form class="post-search" method="get" action="{{ route('posts.index') }}" role="search">
        @if ($tipe)<input type="hidden" name="tipe" value="{{ $tipe }}">@endif
        @if ($kategori)<input type="hidden" name="kategori" value="{{ $kategori }}">@endif
        <label class="sr-only" for="q" style="position:absolute;left:-9999px">Cari artikel</label>
        <input class="input" id="q" type="search" name="q" value="{{ $q }}" maxlength="100" placeholder="Cari artikel…">
        <button class="btn btn--primary" type="submit">Cari</button>
      </form>

      <div class="chips" role="group" aria-label="Filter tipe">
        <a class="chip" href="{{ $keep(['tipe' => null]) }}" @if (! $tipe) aria-current="true" aria-pressed="true" @else aria-pressed="false" @endif>Semua</a>
        @foreach ($types as $key => $label)
          <a class="chip" href="{{ $keep(['tipe' => $key]) }}" aria-pressed="{{ $tipe === $key ? 'true' : 'false' }}" @if ($tipe === $key) aria-current="true" @endif>{{ $label }}</a>
        @endforeach
      </div>
      @if ($categories)
        <div class="chips" role="group" aria-label="Filter kategori" id="kategori">
          <a class="chip" href="{{ $keep(['kategori' => null]) }}" aria-pressed="{{ $kategori ? 'false' : 'true' }}">Semua Kategori</a>
          @foreach ($categories as $cat)
            <a class="chip" href="{{ $keep(['kategori' => $cat]) }}" aria-pressed="{{ $kategori === $cat ? 'true' : 'false' }}">{{ $cat }}</a>
          @endforeach
        </div>
      @endif

      @php $grid = $first ? $posts->getCollection()->slice(1) : $posts->getCollection(); @endphp
      @if ($posts->isEmpty())
        <p class="empty" id="empty">Belum ada artikel yang sesuai.@if ($filtered) <a href="{{ route('posts.index') }}">Reset filter</a>@endif</p>
      @else
        <div class="post-grid" id="post-grid">
          @foreach ($grid as $post)
            <article class="post">
              <a href="{{ route('posts.show', $post->slug) }}" aria-hidden="true" tabindex="-1">
                @if ($post->cover)
                  <img class="post__img post__img--3x2" src="{{ Storage::disk('public')->url($post->cover) }}" alt="" loading="lazy">
                @else
                  <span class="ph ph--3x2" data-label="Cover" style="display:block"></span>
                @endif
              </a>
              <div class="post__meta">@if ($post->category)<span>{{ $post->category }}</span>@endif @if ($post->publish_at)<span>{{ $post->publish_at->translatedFormat('d M Y') }}</span>@endif</div>
              <a href="{{ route('posts.show', $post->slug) }}"><h3>{{ $post->title }}</h3></a>
              @if ($post->excerpt)<p>{{ $post->excerpt }}</p>@endif
            </article>
          @endforeach
        </div>
      @endif

      @if ($posts->hasPages())
        <nav class="pager" aria-label="Halaman">
          @if ($posts->onFirstPage())
            <span aria-disabled="true">Sebelumnya</span>
          @else
            <a href="{{ $posts->previousPageUrl() }}" rel="prev">Sebelumnya</a>
          @endif
          <span aria-current="page">{{ $posts->currentPage() }} / {{ $posts->lastPage() }}</span>
          @if ($posts->hasMorePages())
            <a href="{{ $posts->nextPageUrl() }}" rel="next">Berikutnya</a>
          @else
            <span aria-disabled="true">Berikutnya</span>
          @endif
        </nav>
      @endif
    </div>
  </section>

  <section class="section cta-band">
    <div class="container cta-band__inner">
      <h2>Butuh Dokumentasi untuk Site Anda?</h2>
      <div class="cta-band__actions"><a class="btn btn--primary" href="{{ route('order.create') }}">Minta Penawaran</a><a class="btn btn--ghost" href="{{ route('contact') }}">Hubungi Kami</a></div>
    </div>
  </section>
@endsection
