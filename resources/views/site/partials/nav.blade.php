@php
    $links = [
        ['Layanan', route('services.index'), 'services.*'],
        ['Portofolio', route('projects.index'), 'projects.*'],
        ['Paket', route('packages.index'), 'packages.*'],
        ['Artikel', route('posts.index'), 'posts.*'],
        ['Tentang', route('about'), 'about'],
        ['Kontak', route('contact'), 'contact'],
    ];
@endphp
<header class="nav">
  <div class="container nav__inner">
    <a href="{{ route('home') }}" class="logo" aria-label="{{ config('site.name') }}, beranda">@include('site.partials.logo')</a>
    <nav aria-label="Utama">
      <ul class="nav__links">
        @foreach ($links as [$label, $url, $pattern])
          <li><a href="{{ $url }}" @if (request()->routeIs($pattern)) aria-current="page" @endif>{{ $label }}</a></li>
        @endforeach
      </ul>
    </nav>
    <a href="{{ route('order.create') }}" class="btn btn--primary btn--sm nav__cta">Minta Penawaran</a>
    <details class="nav__menu">
      <summary aria-label="Buka menu">Menu</summary>
      <ul>
        @foreach ($links as [$label, $url, $pattern])
          <li><a href="{{ $url }}" @if (request()->routeIs($pattern)) aria-current="page" @endif>{{ $label }}</a></li>
        @endforeach
        <li><a href="{{ route('order.create') }}">Minta Penawaran</a></li>
      </ul>
    </details>
  </div>
</header>
