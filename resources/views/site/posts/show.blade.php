@extends('layouts.site')

@php
    use Illuminate\Support\Facades\Storage;
    $coverUrl = $post->cover ? Storage::disk('public')->url($post->cover) : null;
    $desc = $post->seo_description ?: ($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $post->body), 155));
    $ld = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $post->title,
        'datePublished' => optional($post->publish_at ?? $post->created_at)->toIso8601String(),
        'dateModified' => optional($post->updated_at)->toIso8601String(),
        'image' => $coverUrl ? [url($coverUrl)] : null,
        'description' => $desc,
        'mainEntityOfPage' => route('posts.show', $post->slug),
        'publisher' => ['@type' => 'Organization', 'name' => config('site.name')],
    ]);
@endphp

@section('title', $post->seo_title ?: $post->title)
@section('description', $desc)
@section('canonical', route('posts.show', $post->slug))
@section('og_type', 'article')
@if ($coverUrl)
  @section('og_image', url($coverUrl))
@endif

@section('head')
<script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('site/css/content.css') }}">
<link rel="stylesheet" href="{{ asset('site/css/posts.css') }}">
@endpush

@section('content')
  <section class="page-hero">
    <div class="container">
      <nav class="crumbs" aria-label="Remah roti"><a href="{{ route('home') }}">Beranda</a> <span aria-hidden="true">/</span> <a href="{{ route('posts.index') }}">Artikel</a> <span aria-hidden="true">/</span> <span aria-current="page">{{ \Illuminate\Support\Str::limit($post->title, 50) }}</span></nav>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <article class="article">
        <header class="article__head">
          <div class="post__meta">
            <span class="badge badge--accent">{{ $post->category ?: $typeLabel }}</span>
            @if ($post->category)<span>{{ $typeLabel }}</span>@endif
            @if ($post->publish_at)<time datetime="{{ $post->publish_at->toDateString() }}">{{ $post->publish_at->translatedFormat('d M Y') }}</time>@endif
          </div>
          <h1>{{ $post->title }}</h1>
          @if ($post->excerpt)<p>{{ $post->excerpt }}</p>@endif
        </header>

        <div class="article__cover">
          @if ($coverUrl)
            <img src="{{ $coverUrl }}" alt="{{ $post->title }}">
          @else
            <div class="ph ph--16x9" data-label="Cover artikel" role="img" aria-label="Cover artikel"></div>
          @endif
        </div>

        <div class="prose article__body">{!! $post->body !!}</div>

        <p class="article__back"><a class="btn btn--ghost btn--sm" href="{{ route('posts.index') }}">&larr; Kembali ke Artikel</a></p>
      </article>
    </div>
  </section>

  @if ($related->isNotEmpty())
    <section class="section section--alt">
      <div class="container">
        <h2>Artikel Terkait</h2>
        <div class="post-grid">
          @foreach ($related as $r)
            <article class="post">
              <a href="{{ route('posts.show', $r->slug) }}" aria-hidden="true" tabindex="-1">
                @if ($r->cover)
                  <img class="post__img post__img--3x2" src="{{ Storage::disk('public')->url($r->cover) }}" alt="" loading="lazy">
                @else
                  <span class="ph ph--3x2" data-label="Cover" style="display:block"></span>
                @endif
              </a>
              <div class="post__meta"><span>{{ $r->category }}</span>@if ($r->publish_at)<span>{{ $r->publish_at->translatedFormat('d M Y') }}</span>@endif</div>
              <a href="{{ route('posts.show', $r->slug) }}"><h3>{{ $r->title }}</h3></a>
              @if ($r->excerpt)<p>{{ $r->excerpt }}</p>@endif
            </article>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  <section class="section cta-band">
    <div class="container cta-band__inner">
      <h2>Butuh Dokumentasi untuk Site Anda?</h2>
      <div class="cta-band__actions"><a class="btn btn--primary" href="{{ route('order.create') }}">Minta Penawaran</a><a class="btn btn--ghost" href="{{ route('contact') }}">Hubungi Kami</a></div>
    </div>
  </section>
@endsection
