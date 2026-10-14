@extends('layouts.site')

@section('description', config('site.description'))
@section('canonical', route('home'))

@php
    $phone = config('site.phone_display');
    $businessLd = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'ProfessionalService',
        'name' => config('site.name'),
        'description' => config('site.description'),
        'url' => url('/'),
        'email' => config('site.email'),
        // Nomor placeholder ("8XX") tidak boleh masuk data terstruktur.
        'telephone' => ($phone && ! preg_match('/x/i', $phone)) ? $phone : null,
        'areaServed' => config('site.service_area'),
        // Hanya jam dengan format schema.org valid (diisi lewat Pengaturan), bukan teks bebas.
        'openingHours' => config('site.hours_schema') ?: null,
        'image' => config('site.og_image') ? \Illuminate\Support\Facades\Storage::disk('public')->url(config('site.og_image')) : null,
    ]);
@endphp
@section('head')
<script type="application/ld+json">{!! json_encode($businessLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('site/css/home.css') }}">
@endpush

@php
    $wa = config('site.whatsapp');
    $waUrl = $wa ? 'https://wa.me/'.$wa.'?text='.rawurlencode('Halo '.config('site.name').', saya ingin berdiskusi tentang dokumentasi tambang.') : null;
@endphp

@section('content')
  <section class="hero" aria-labelledby="hero-title">
    <div class="ph ph--16x9 hero__bg" data-label="Video aerial loop / fallback foto" aria-hidden="true"></div>
    <div class="hero__overlay" aria-hidden="true"></div>
    <div class="container hero__inner">
      <span class="eyebrow">Fotografi &amp; Videografi Pertambangan</span>
      <h1 id="hero-title">Lihat Tambang Anda dengan Jelas.</h1>
      <p class="hero__sub">{{ config('site.description') }}</p>
      <div class="hero__actions">
        <a class="btn btn--primary" href="{{ route('order.create') }}">Minta Penawaran</a>
        <a class="btn btn--ghost" href="{{ route('projects.index') }}">Lihat Portofolio</a>
      </div>
      <ul class="hero__tags">
        <li>Pit</li><li>Hauling Road</li><li>Plant</li><li>Pelabuhan</li><li>Reklamasi</li>
      </ul>
    </div>
  </section>

  <section class="stats" aria-label="Statistik">
    <div class="container">
      <dl class="stats__grid">
        @foreach (config('site.stats', []) as $stat)
        <div class="stats__item"><dt>{{ ucfirst($stat['label']) }}</dt><dd><span class="stats__num">{{ $stat['value'] }}</span></dd></div>
        @endforeach
      </dl>
      <p class="stats__note">Angka sementara (placeholder) — akan diperbarui setelah data riil terkumpul.</p>
    </div>
  </section>

  <section class="marquee-band" aria-labelledby="klien-title">
    <div class="container marquee-band__head">
      <h2 id="klien-title" class="marquee-band__title">Dipercaya oleh</h2>
      <p>Kontraktor, operator tambang, dan vendor alat berat yang mengizinkan logo mereka tampil. Ditambah pengalaman bekerja bersama kontraktor &amp; vendor tambang.</p>
    </div>
    <div class="marquee">
      <div class="marquee__track">
        <ul class="marquee__group">
          <li>Klien 1</li><li>Klien 2</li><li>Klien 3</li><li>Klien 4</li><li>Klien 5</li><li>Klien 6</li>
        </ul>
        <ul class="marquee__group" aria-hidden="true">
          <li>Klien 1</li><li>Klien 2</li><li>Klien 3</li><li>Klien 4</li><li>Klien 5</li><li>Klien 6</li>
        </ul>
      </div>
    </div>
  </section>

  <section class="section" aria-labelledby="reel-title">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow">Showreel</span>
        <h2 id="reel-title">Bekerja di Lapangan</h2>
        <p><em>Cuplikan 60 detik dari pit, hauling road, plant, hingga area reklamasi.</em></p>
      </div>
      <div class="reel">
        <div class="ph ph--16x9 reel__ph" data-label="Video showreel 60 detik"></div>
        <div class="reel__play" aria-hidden="true">
          <span class="reel__icon"></span>
          <span class="reel__label">Putar Showreel</span>
        </div>
      </div>
    </div>
  </section>

  <section class="section section--alt" aria-labelledby="pillars-title">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow">Layanan Utama</span>
        <h2 id="pillars-title">Tiga Pilar Dokumentasi</h2>
        <p>Satu tim, satu jadwal lapangan, satu standar hasil yang seragam. Pilih satu pilar atau gabungkan ketiganya.</p>
      </div>
      <div class="grid grid--3">
        <article class="card pillar">
          <div class="ph ph--3x2 pillar__media" data-label="Foto: alat berat"></div>
          <h3>Foto Operasional</h3>
          <p>Alat berat, aktivitas produksi, proses, potret tim, dan fasilitas.</p>
          <ul class="pillar__list">
            <li>Foto kerja &amp; HSE</li>
            <li>Foto produk &amp; alat</li>
            <li>Potret korporat</li>
            <li>Retouch &amp; penamaan file terstruktur</li>
          </ul>
          <a class="pillar__link" href="{{ route('services.index') }}">Lihat layanan<span aria-hidden="true"> →</span></a>
        </article>
        <article class="card pillar">
          <div class="ph ph--3x2 pillar__media" data-label="Video: company profile"></div>
          <h3>Video Perusahaan &amp; Dokumentasi</h3>
          <p>Company profile, video safety/induction, proses operasi, dan highlight event.</p>
          <ul class="pillar__list">
            <li>Tampilan sinematik</li>
            <li>Voice over</li>
            <li>Subtitle</li>
            <li>Motion graphic</li>
          </ul>
          <a class="pillar__link" href="{{ route('services.index') }}">Lihat layanan<span aria-hidden="true"> →</span></a>
        </article>
        <article class="card pillar">
          <div class="ph ph--3x2 pillar__media" data-label="Aerial: pit &amp; stockpile"></div>
          <h3>Aerial &amp; Drone</h3>
          <p>Progres pit, stockpile, hauling road, jetty/pelabuhan, dan reklamasi.</p>
          <ul class="pillar__list">
            <li>Foto &amp; video udara</li>
            <li>Time-lapse progres bulanan</li>
            <li>Peta orthomosaic<span class="pillar__flag">*</span></li>
          </ul>
          <a class="pillar__link" href="{{ route('services.index') }}">Lihat layanan<span aria-hidden="true"> →</span></a>
        </article>
      </div>
      <p class="pillars__note">* Fitur orthomosaic hanya ditampilkan bila benar-benar ditawarkan.</p>
    </div>
  </section>

  <section class="section" aria-labelledby="proses-title">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow">Cara Kerja</span>
        <h2 id="proses-title">Lima Langkah dari Brief ke Berkas</h2>
        <p>Alur baku agar jadwal lapangan tidak meleset dan hasil sampai tepat waktu.</p>
      </div>
      <ol class="process">
        @foreach ([
          ['Konsultasi & Scope', 'Diskusi tujuan dokumen: laporan progres, company profile, atau materi K3. Kami susun daftar momen yang harus tertangkap.'],
          ['Penawaran', 'Rincian cakupan, durasi, jumlah foto, durasi video, serta biaya mobilisasi area terpencil yang ditulis terpisah.'],
          ['Perizinan & Induksi Lokasi', 'Koordinasi izin masuk, induksi K3, alat pelindung diri, serta izin terbang drone beserta batas wilayahnya.'],
          ['Pengambilan Gambar', 'Pengambilan gambar di lapangan sesuai jalur dan ritme kerja tim Anda. Dokumentasi tetap berjalan meski operasi berlangsung.'],
          ['Penyerahan Hasil', 'Hasil dikirim lewat portal klien: galeri privat, pilih favorit, unduh berkas, dan catatan revisi.'],
        ] as $i => [$stepTitle, $stepText])
        <li class="process__step">
          <span class="process__num" aria-hidden="true">{{ sprintf('%02d', $i + 1) }}</span>
          <div class="process__body">
            <h3>{{ $stepTitle }}</h3>
            <p>{{ $stepText }}</p>
          </div>
        </li>
        @endforeach
      </ol>
    </div>
  </section>

  <section class="section section--alt" aria-labelledby="portofolio-title">
    <div class="container">
      <div class="section-head section-head--row">
        <div>
          <span class="eyebrow">Portofolio Pilihan</span>
          <h2 id="portofolio-title">Proyek Terpilih</h2>
        </div>
        <a class="btn btn--ghost btn--sm" href="{{ route('projects.index') }}">Lihat semua portofolio</a>
      </div>
      @if ($projects->isNotEmpty())
      <div class="grid grid--3 work-grid">
        @foreach ($projects as $project)
        @php
            $meta = collect([$project->publicClientName(), $project->publicLocation() ?? 'Lokasi disamarkan', $project->year])->filter()->implode(' · ');
        @endphp
        <a class="card work" href="{{ route('projects.show', $project->slug) }}">
          @if ($project->cover)
          <img class="work__media" src="{{ Storage::disk('public')->url($project->cover) }}" alt="{{ $project->title }}" loading="lazy" style="aspect-ratio:4/5;object-fit:cover;width:100%">
          @else
          <div class="ph ph--4x5 work__media" data-label="{{ $project->category }}"></div>
          @endif
          <div class="work__body">
            @if ($project->category)<span class="badge badge--accent">{{ $project->category }}</span>@endif
            <h3>{{ $project->title }}</h3>
            <p class="work__meta">{{ $meta }}</p>
          </div>
        </a>
        @endforeach
      </div>
      @else
      <p>Portofolio akan segera hadir.</p>
      @endif
    </div>
  </section>

  <section class="section" aria-labelledby="safety-title">
    <div class="container safety__inner">
      <div class="safety__intro">
        <span class="eyebrow">Keselamatan &amp; Kerahasiaan</span>
        <h2 id="safety-title">Dokumentasi Tidak Boleh Mengganggu Pekerjaan Anda</h2>
        <p>Setiap orang yang masuk area tambang mengikuti aturan site. Data operasi yang tidak boleh publik tetap tidak dipublikasikan.</p>
        <a class="btn btn--ghost btn--sm" href="{{ route('faq') }}">Baca FAQ keamanan</a>
      </div>
      <ul class="safety__list">
        <li><strong>Induksi K3 setiap site.</strong> Kami mengikuti induksi dan prosedur apa pun yang berlaku di lokasi Anda.</li>
        <li><strong>APD lengkap.</strong> Helm, safety shoes, rompi, dan perlengkapan lain disiapkan sebelum masuk area.</li>
        <li><strong>Tidak ada publikasi tanpa persetujuan klien.</strong> Nama perusahaan, lokasi, dan hasil kerja tidak tayang sebelum disetujui.</li>
        <li><strong>Opsi watermark &amp; galeri privat.</strong> Pratinjau diberi watermark; berkas penuh hanya lewat portal klien.</li>
        <li><strong>NDA tersedia.</strong> Kesepakatan kerahasiaan dapat ditandatangani sebelum shoot dimulai.</li>
      </ul>
    </div>
  </section>

  @if ($testimonials->isNotEmpty())
  <section class="section section--alt" aria-labelledby="testimoni-title">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow">Testimoni</span>
        <h2 id="testimoni-title">Kata Mereka yang Sudah di Lapangan</h2>
      </div>
      <div class="grid grid--3">
        @foreach ($testimonials as $t)
        <figure class="card quote">
          <blockquote><p>“{{ $t->quote }}”</p></blockquote>
          <figcaption><strong>{{ $t->author }}</strong><span>{{ collect([$t->role, $t->company])->filter()->implode(' · ') }}</span></figcaption>
        </figure>
        @endforeach
      </div>
    </div>
  </section>
  @endif

  @if ($posts->isNotEmpty())
  <section class="section" aria-labelledby="artikel-title">
    <div class="container">
      <div class="section-head section-head--row">
        <div>
          <span class="eyebrow">Artikel Terbaru</span>
          <h2 id="artikel-title">Catatan dari Lapangan</h2>
        </div>
        <a class="btn btn--ghost btn--sm" href="{{ route('posts.index') }}">Semua artikel</a>
      </div>
      <div class="grid grid--3">
        @foreach ($posts as $post)
        <a class="card post" href="{{ route('posts.show', $post->slug) }}">
          @if ($post->cover)
          <img class="post__media" src="{{ Storage::disk('public')->url($post->cover) }}" alt="{{ $post->title }}" loading="lazy" style="aspect-ratio:3/2;object-fit:cover;width:100%">
          @else
          <div class="ph ph--3x2 post__media" data-label="Cover: {{ $post->category ?? 'Artikel' }}"></div>
          @endif
          <h3>{{ $post->title }}</h3>
          @if ($post->excerpt)<p>{{ $post->excerpt }}</p>@endif
          @if ($post->category)<span class="post__meta">{{ $post->category }}</span>@endif
        </a>
        @endforeach
      </div>
    </div>
  </section>
  @endif

  <section class="final-cta" aria-labelledby="cta-title">
    <div class="container final-cta__inner">
      <div class="final-cta__text">
        <span class="eyebrow">Mulai Proyek</span>
        <h2 id="cta-title">Siap mendokumentasikan site Anda?</h2>
        <ul class="final-cta__points">
          <li>Respons ≤ 1×24 jam kerja</li>
          <li>Survei lokasi bila diperlukan</li>
          <li>Tanpa komitmen</li>
        </ul>
      </div>
      <div class="final-cta__actions">
        @if ($waUrl)<a class="btn btn--primary" href="{{ $waUrl }}" target="_blank" rel="noopener">Chat WhatsApp</a>@endif
        <a class="btn {{ $waUrl ? 'btn--ghost' : 'btn--primary' }}" href="{{ route('order.create') }}">Minta Penawaran</a>
      </div>
    </div>
  </section>
@endsection
