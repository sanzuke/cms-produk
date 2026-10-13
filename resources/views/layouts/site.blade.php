@php
    $siteName = config('site.name');
    $pageTitle = trim($__env->yieldContent('title'));
    $fullTitle = $pageTitle !== '' ? $pageTitle.' — '.$siteName : $siteName.' — '.config('site.tagline');
    $metaDescription = trim($__env->yieldContent('description')) ?: config('site.description');
    $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();
    $waNumber = config('site.whatsapp');
    $waUrl = $waNumber ? 'https://wa.me/'.$waNumber.'?text='.rawurlencode('Halo '.$siteName.', saya ingin berdiskusi tentang dokumentasi tambang.') : null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<link rel="canonical" href="{{ $canonical }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:type" content="@yield('og_type', 'website')">
<meta property="og:url" content="{{ $canonical }}">
@hasSection('og_image')<meta property="og:image" content="@yield('og_image')">@endif
@yield('head')
<link rel="stylesheet" href="{{ asset('site/css/tokens.css') }}">
<link rel="stylesheet" href="{{ asset('site/css/base.css') }}">
@stack('styles')
</head>
<body>
<a href="#main" class="skip-link">Lewati ke konten</a>

@include('site.partials.nav')

<main id="main">
@yield('content')
</main>

@include('site.partials.footer')

@if ($waUrl)
<a class="wa-float" href="{{ $waUrl }}" target="_blank" rel="noopener" aria-label="Chat WhatsApp">Chat WhatsApp</a>
@endif

@stack('scripts')
</body>
</html>
