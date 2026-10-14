@php
    $footerServices = \App\Models\Service::published()->limit(4)->get(['slug', 'title']);
@endphp
<footer class="footer">
  <div class="container">
    <div class="footer__grid">
      <div>
        <a href="{{ route('home') }}" class="logo">@include('site.partials.logo')</a>
        <p style="margin-top:12px;max-width:320px">Fotografi, videografi, dan aerial untuk industri pertambangan.</p>
      </div>
      <div>
        <h4>Layanan</h4>
        <ul>
          @forelse ($footerServices as $s)
            <li><a href="{{ route('services.show', $s->slug) }}">{{ $s->title }}</a></li>
          @empty
            <li><a href="{{ route('services.index') }}">Semua layanan</a></li>
          @endforelse
        </ul>
      </div>
      <div>
        <h4>Perusahaan</h4>
        <ul>
          <li><a href="{{ route('about') }}">Tentang</a></li>
          <li><a href="{{ route('projects.index') }}">Portofolio</a></li>
          <li><a href="{{ route('posts.index') }}">Artikel</a></li>
          <li><a href="{{ route('faq') }}">FAQ</a></li>
        </ul>
      </div>
      <div>
        <h4>Kontak</h4>
        <ul>
          <li>{{ config('site.email') }}</li>
          <li>{{ config('site.phone_display') }}</li>
          <li>{{ config('site.hours') }}</li>
        </ul>
      </div>
    </div>
    <div class="footer__bottom">
      <span>© {{ date('Y') }} {{ config('site.name') }}</span>
      <span><a href="{{ route('legal.terms') }}">Syarat</a> · <a href="{{ route('legal.privacy') }}">Privasi</a></span>
    </div>
  </div>
</footer>
