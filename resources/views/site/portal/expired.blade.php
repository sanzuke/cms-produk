@extends('layouts.site')

@section('title', 'Link Portal Kedaluwarsa')
@section('description', 'Link portal klien ini sudah kedaluwarsa.')
@section('head')
<meta name="robots" content="noindex, nofollow, noarchive">
<meta name="referrer" content="no-referrer">
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('site/css/portal.css') }}">
@endpush

@section('content')
  <section class="portal-hero">
    <div class="container">
      <span class="eyebrow">Portal klien</span>
      <h1>Link ini sudah kedaluwarsa.</h1>
    </div>
  </section>

  <section class="section">
    <div class="container portal-expired">
      <p>Masa akses hasil dokumentasi untuk order <strong>{{ $order->number }}</strong> telah berakhir. Tim kami dapat memperpanjang akses atau mengirim ulang berkas; hubungi kami dan sebutkan nomor order tersebut.</p>
      <ul class="portal-contact">
        @if (config('site.email'))<li>Email: <a href="mailto:{{ config('site.email') }}">{{ config('site.email') }}</a></li>@endif
        @if (config('site.phone_display'))<li>Telepon: {{ config('site.phone_display') }}</li>@endif
        @if (config('site.whatsapp'))<li>WhatsApp: <a href="https://wa.me/{{ config('site.whatsapp') }}" rel="noopener noreferrer">chat dengan kami</a></li>@endif
      </ul>
      <a href="{{ route('contact') }}" class="btn btn--primary">Halaman kontak</a>
    </div>
  </section>
@endsection
