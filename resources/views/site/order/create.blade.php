@extends('layouts.site')

@use('Illuminate\Support\Number')

@section('title', 'Pesan / Minta Penawaran')
@section('description', 'Pesan paket dokumentasi atau minta penawaran custom dalam empat langkah: pilih jalur, paket dan add-on, data lokasi, konfirmasi.')
@section('canonical', route('order.create'))

@push('styles')
<link rel="stylesheet" href="{{ asset('site/css/pricing.css') }}">
<link rel="stylesheet" href="{{ asset('site/css/order.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('site/js/order.js') }}" defer></script>
@endpush

@section('content')
  @php
      $hasOld = session()->hasOldInput();
      $val = fn (string $key, $default = null) => $hasOld ? old($key) : $default;
      $jalur = $val('jalur', $defaultJalur) ?: 'paket';
      $selectedPackage = $val('paket', $defaultPackage);
      $selectedService = $val('service_id', $defaultService);
      $selectedAddons = (array) $val('addon', []);
      $commodityLabels = ['batubara' => 'Batubara', 'nikel' => 'Nikel', 'emas' => 'Emas', 'lainnya' => 'Lainnya'];
      $attachmentErrors = collect($errors->get('attachments.*'))->flatten()->merge($errors->get('attachments'))->unique()->values();
      $err = fn (string $key) => $errors->has($key);
  @endphp

  <section class="page-hero">
    <div class="container">
      <nav class="crumbs" aria-label="Remah roti"><a href="{{ route('home') }}">Beranda</a> <span aria-hidden="true">/</span> <a href="{{ route('packages.index') }}">Paket</a> <span aria-hidden="true">/</span> <span aria-current="page">Pesan</span></nav>
      <span class="eyebrow">Pesan &amp; Penawaran</span>
      <h1>Empat Langkah sampai Diterjemahkan ke Penawaran.</h1>
      <p>Isi paket sendiri atau minta penawaran custom. Ringkasan di samping ikut berubah mengikuti pilihan Anda. Tim kami menghubungi Anda untuk konfirmasi setelah permintaan diterima.</p>
    </div>
  </section>

  <section class="section" aria-labelledby="h-order">
    <div class="container">
      <h2 id="h-order" class="sr-only">Form pemesanan bertahap</h2>

      @if ($errors->any())
        <div class="form-alert" role="alert">
          <strong>Permintaan belum terkirim. Periksa {{ $errors->count() }} isian berikut:</strong>
          <ul>
            @foreach ($errors->all() as $message)
              <li>{{ $message }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div id="wizard" class="wizard">
        <form id="order-form" method="post" action="{{ route('order.store') }}" enctype="multipart/form-data">
          @csrf

          <div class="hp" aria-hidden="true">
            <label for="f-website">Jangan diisi bila Anda manusia</label>
            <input id="f-website" name="website" type="text" tabindex="-1" autocomplete="off" value="">
          </div>

          <ol class="stepper">
            <li data-state="active"><span class="stepper__n">1</span> <span>Jalur pemesanan</span></li>
            <li data-state="todo"><span class="stepper__n">2</span> <span>Paket &amp; add-on</span></li>
            <li data-state="todo"><span class="stepper__n">3</span> <span>Data site &amp; tanggal</span></li>
            <li data-state="todo"><span class="stepper__n">4</span> <span>Ringkasan &amp; konfirmasi</span></li>
          </ol>

          <!-- PANEL 1 -->
          <section class="wiz-panel is-active" aria-labelledby="s1">
            <h2 id="s1"><span class="wiz-panel__num">Langkah 1</span> Pilih Jalur Pemesanan</h2>
            <p>Paket standar bisa langsung dijadwalkan setelah data Anda lengkap. Jalur custom akan melewati survei singkat dan Penawaran tertulis.</p>

            <fieldset class="choice-grid choice-grid--2">
              <legend class="sr-only">Jalur pemesanan</legend>

              <label class="choice">
                <input type="radio" name="jalur" value="paket" @checked($jalur === 'paket')>
                <span class="choice__box">
                  <span class="choice__title">Paket Standar <span class="badge badge--accent">Self-service</span></span>
                  <span class="choice__desc">Harga sudah jelas, jadwal bisa langsung dipesan. Tim kami menghubungi Anda untuk konfirmasi dan berkas izin masuk.</span>
                </span>
              </label>

              <label class="choice">
                <input type="radio" name="jalur" value="custom" @checked($jalur === 'custom')>
                <span class="choice__box">
                  <span class="choice__title">Penawaran Custom <span class="badge">Multi-site</span></span>
                  <span class="choice__desc">Untuk proyek besar, retainer, atau beberapa lokasi sekaligus. Kami siapkan Penawaran tertulis setelah survei atau diskusi singkat.</span>
                </span>
              </label>
            </fieldset>
            @error('jalur')<p class="field-error" role="alert">{{ $message }}</p>@enderror

            <div class="wiz-nav">
              <button type="button" class="btn btn--primary btn--next" data-next>Lanjut ke Paket</button>
            </div>
          </section>

          <!-- PANEL 2 -->
          <section class="wiz-panel" aria-labelledby="s2">
            <h2 id="s2"><span class="wiz-panel__num">Langkah 2</span> Pilih Paket dan Add-on</h2>

            <div id="box-paket">
              <h3 class="wiz-sub">Paket standar</h3>
              @if ($standard->isEmpty())
                <p class="form-note">Paket standar belum tersedia. Pilih jalur custom atau jelaskan kebutuhan Anda pada langkah 3.</p>
              @else
                <fieldset class="choice-grid choice-grid--2">
                  <legend class="sr-only">Paket</legend>
                  @foreach ($standard as $package)
                    <label class="choice">
                      <input type="radio" name="paket" value="{{ $package->slug }}"
                             data-label="{{ $package->name }}" data-price="{{ $package->price }}" data-unit="{{ $package->price_unit }}"
                             @checked($selectedPackage === $package->slug)>
                      <span class="choice__box">
                        <span class="choice__title">{{ $package->name }} @if ($package->is_popular)<span class="badge badge--accent">Populer</span>@endif<span class="choice__price">{{ Number::currency($package->price, 'IDR', 'id', 0) }}@if ($package->price_unit) {{ $package->price_unit }}@endif</span></span>
                        <span class="choice__desc">{{ $package->description }}@if (! empty($package->features)) {{ $package->description ? ' ' : '' }}{{ implode(', ', $package->features) }}.@endif</span>
                      </span>
                    </label>
                  @endforeach
                </fieldset>
              @endif
              @error('paket')<p class="field-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div id="box-custom" @if ($jalur !== 'custom') hidden @endif>
              <div class="card">
                <h3>Apa yang perlu dari kami?</h3>
                <p class="pkg__desc">Tuliskan cakupan pekerjaan, jumlah lokasi, target hasil, dan tenggat pada kolom <strong>Kebutuhan khusus</strong> di langkah 3. Tim kami menyusun Penawaran tertulis lengkap dengan mobilisasi.</p>
              </div>
            </div>

            <div id="box-addon">
              <h3 class="wiz-sub">Add-on <span class="form-note" style="text-transform:none">— opsional; harga placeholder</span></h3>
              <fieldset class="choice-grid choice-grid--2">
                <legend class="sr-only">Pilih add-on</legend>
                @foreach ($addons as $key => $addon)
                  <label class="choice">
                    <input type="checkbox" name="addon[]" value="{{ $key }}" data-label="{{ $addon['label'] }}" data-price="{{ $addon['price'] }}" @if ($addon['qty']) data-qty @endif @checked(in_array($key, $selectedAddons, true))>
                    <span class="choice__box">
                      <span class="choice__title">{{ $addon['label'] }} <span class="choice__price">Rp X{{ $addon['unit'] ? ' '.$addon['unit'] : '' }}</span></span>
                      <span class="choice__desc">
                        {{ $addon['desc'] }}
                        @if ($addon['qty'])
                          <span class="qty-row">Jumlah: <input class="input qty" id="qty-{{ $key }}" name="addon_qty[{{ $key }}]" type="number" min="1" max="{{ $addon['qty'] }}" value="{{ $hasOld ? (old('addon_qty.'.$key) ?: 1) : 1 }}" aria-label="Jumlah {{ strtolower($addon['label']) }}"></span>
                        @endif
                      </span>
                    </span>
                  </label>
                @endforeach
              </fieldset>
              @if ($errors->has('addon') || $errors->has('addon.*') || $errors->has('addon_qty.*'))
                <p class="field-error" role="alert">{{ collect($errors->get('addon'))->merge(collect($errors->get('addon.*'))->flatten())->merge(collect($errors->get('addon_qty.*'))->flatten())->first() }}</p>
              @endif
            </div>

            <p class="form-note">Biaya mobilisasi dan akomodasi lokasi terpencil dihitung terpisah dan selalu dicantumkan di penawaran tertulis.</p>

            <div class="wiz-nav">
              <button type="button" class="btn btn--ghost" data-prev>Kembali</button>
              <button type="button" class="btn btn--primary btn--next" data-next>Lanjut ke Data Site</button>
            </div>
          </section>

          <!-- PANEL 3 -->
          <section class="wiz-panel" aria-labelledby="s3">
            <h2 id="s3"><span class="wiz-panel__num">Langkah 3</span> Data Site dan Tanggal</h2>
            <p>Isian bertanda <span class="req" aria-hidden="true">*</span> wajib diisi. Data ini dipakai untuk menyusun Penawaran dan menjadwalkan shooting.</p>

            <div class="grid grid--2">
              <div class="field">
                <label for="f-name">Nama lengkap <span class="req" aria-hidden="true">*</span></label>
                <input class="input" id="f-name" name="name" type="text" autocomplete="name" required maxlength="120" value="{{ old('name') }}" @if ($err('name')) aria-invalid="true" aria-describedby="e-name" @endif>
                @error('name')<p class="field-error" id="e-name">{{ $message }}</p>@enderror
              </div>
              <div class="field">
                <label for="f-company">Perusahaan <span class="req" aria-hidden="true">*</span></label>
                <input class="input" id="f-company" name="company" type="text" autocomplete="organization" required maxlength="160" value="{{ old('company') }}" @if ($err('company')) aria-invalid="true" aria-describedby="e-company" @endif>
                @error('company')<p class="field-error" id="e-company">{{ $message }}</p>@enderror
              </div>
            </div>

            <div class="grid grid--2">
              <div class="field">
                <label for="f-position">Jabatan</label>
                <input class="input" id="f-position" name="position" type="text" autocomplete="organization-title" maxlength="100" value="{{ old('position') }}" @if ($err('position')) aria-invalid="true" aria-describedby="e-position" @endif>
                @error('position')<p class="field-error" id="e-position">{{ $message }}</p>@enderror
              </div>
              <div class="field">
                <label for="f-commodity">Komoditas</label>
                <select class="select" id="f-commodity" name="commodity" @if ($err('commodity')) aria-invalid="true" aria-describedby="e-commodity" @endif>
                  <option value="">Pilih jenis</option>
                  @foreach ($commodities as $commodity)
                    <option value="{{ $commodity }}" @selected(old('commodity') === $commodity)>{{ $commodityLabels[$commodity] ?? ucfirst($commodity) }}</option>
                  @endforeach
                </select>
                @error('commodity')<p class="field-error" id="e-commodity">{{ $message }}</p>@enderror
              </div>
            </div>

            <div class="grid grid--2">
              <div class="field">
                <label for="f-email">Email <span class="req" aria-hidden="true">*</span></label>
                <input class="input" id="f-email" name="email" type="email" autocomplete="email" required maxlength="190" value="{{ old('email') }}" @if ($err('email')) aria-invalid="true" aria-describedby="e-email" @endif>
                @error('email')<p class="field-error" id="e-email">{{ $message }}</p>@enderror
              </div>
              <div class="field">
                <label for="f-phone">Nomor WhatsApp <span class="req" aria-hidden="true">*</span></label>
                <input class="input" id="f-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="08XX-XXXX-XXXX" required maxlength="30" value="{{ old('phone') }}" @if ($err('phone')) aria-invalid="true" aria-describedby="e-phone" @endif>
                @error('phone')<p class="field-error" id="e-phone">{{ $message }}</p>@enderror
              </div>
            </div>

            <div class="field">
              <label for="f-service_id">Layanan yang dibutuhkan</label>
              <select class="select" id="f-service_id" name="service_id" @if ($err('service_id')) aria-invalid="true" aria-describedby="e-service_id" @endif>
                <option value="">Belum yakin / pilih nanti</option>
                @foreach ($services as $service)
                  <option value="{{ $service->id }}" @selected((string) $selectedService === (string) $service->id)>{{ $service->title }}</option>
                @endforeach
              </select>
              @error('service_id')<p class="field-error" id="e-service_id">{{ $message }}</p>@enderror
            </div>

            <div class="field">
              <label for="f-site">Lokasi / nama site <span class="req" aria-hidden="true">*</span></label>
              <input class="input" id="f-site" name="site" type="text" placeholder="Contoh: Site Kalimat, Kabupaten Kotabaru" required maxlength="255" value="{{ old('site') }}" @if ($err('site')) aria-invalid="true" aria-describedby="e-site" @endif>
              <p class="req-note">Tulis lokasi secara umum bila nama site tidak boleh dipublikasikan.</p>
              @error('site')<p class="field-error" id="e-site">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid--2">
              <div class="field">
                <label for="f-needed_date">Tanggal dibutuhkan <span class="req" aria-hidden="true">*</span></label>
                <input class="input" id="f-needed_date" name="needed_date" type="date" min="{{ now()->toDateString() }}" required value="{{ old('needed_date') }}" @if ($err('needed_date')) aria-invalid="true" aria-describedby="e-needed_date" @endif>
                @error('needed_date')<p class="field-error" id="e-needed_date">{{ $message }}</p>@enderror
              </div>
              <div class="field">
                <label for="f-duration">Perkiraan durasi</label>
                <select class="select" id="f-duration" name="duration" @if ($err('duration')) aria-invalid="true" aria-describedby="e-duration" @endif>
                  <option value="">Pilih durasi</option>
                  @foreach ($durations as $duration)
                    <option value="{{ $duration }}" @selected(old('duration') === $duration)>{{ $duration }}</option>
                  @endforeach
                </select>
                @error('duration')<p class="field-error" id="e-duration">{{ $message }}</p>@enderror
              </div>
            </div>

            <div class="field">
              <label for="f-brief">Kebutuhan khusus</label>
              <textarea class="textarea" id="f-brief" name="brief" maxlength="5000" placeholder="Contoh: dokumentasi hauling road tiap titik, format laporan bulanan, ada area terbatas yang butuh izin khusus…" @if ($err('brief')) aria-invalid="true" aria-describedby="e-brief" @endif>{{ old('brief') }}</textarea>
              @error('brief')<p class="field-error" id="e-brief">{{ $message }}</p>@enderror
            </div>

            <div class="field">
              <label for="f-attachments">Upload brief (opsional)</label>
              <input class="input" id="f-attachments" name="attachments[]" type="file" multiple accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" @if ($attachmentErrors->isNotEmpty()) aria-invalid="true" aria-describedby="e-attachments" @endif>
              <p class="req-note">PDF, JPG, atau PNG. Maks. 3 berkas, masing-masing 10 MB. Berkas perlu dipilih ulang bila formulir dimuat ulang.</p>
              @foreach ($attachmentErrors as $message)
                <p class="field-error" @if ($loop->first) id="e-attachments" @endif>{{ $message }}</p>
              @endforeach
            </div>

            <div class="field">
              <label class="check">
                <input type="checkbox" name="consent" value="1" required @checked(old('consent')) @if ($err('consent')) aria-invalid="true" aria-describedby="e-consent" @endif>
                <span>Saya setuju foto dan video yang dihasilkan tidak dipublikasikan tanpa persetujuan perusahaan saya, dan datanya dipakai hanya untuk proses penawaran sesuai <a href="{{ route('legal.privacy') }}" class="link-accent" target="_blank" rel="noopener">Kebijakan Privasi</a>. <span class="req" aria-hidden="true">*</span></span>
              </label>
              @error('consent')<p class="field-error" id="e-consent">{{ $message }}</p>@enderror
            </div>

            <div class="wiz-nav">
              <button type="button" class="btn btn--ghost" data-prev>Kembali</button>
              <button type="button" class="btn btn--primary btn--next" data-next>Lanjut ke Ringkasan</button>
            </div>
          </section>

          <!-- PANEL 4 -->
          <section class="wiz-panel" aria-labelledby="s4">
            <h2 id="s4"><span class="wiz-panel__num">Langkah 4</span> Ringkasan dan Konfirmasi</h2>
            <p>Periksa kembali data Anda. Harga bersifat indikatif sampai Penawaran resmi diterbitkan.</p>

            <div class="card js-only">
              <h3>Data pemesan</h3>
              <ul class="info-list">
                <li><b>Nama</b><span data-recap="f-name">—</span></li>
                <li><b>Perusahaan</b><span data-recap="f-company">—</span></li>
                <li><b>Layanan</b><span data-recap="f-service_id">—</span></li>
                <li><b>Lokasi</b><span data-recap="f-site">—</span></li>
                <li><b>Komoditas</b><span data-recap="f-commodity">—</span></li>
                <li><b>Tanggal</b><span data-recap="f-needed_date">—</span></li>
                <li><b>Durasi</b><span data-recap="f-duration">—</span></li>
              </ul>
            </div>

            <div class="wiz-nav">
              <button type="button" class="btn btn--ghost" data-prev>Kembali</button>
              <button type="submit" class="btn btn--primary btn--next">Kirim Permintaan</button>
            </div>
          </section>

        </form>

        <!-- RINGKASAN HARGA -->
        <aside class="card summary" aria-labelledby="h-sum">
          <h3 id="h-sum">Ringkasan Harga</h3>
          <ul class="summary__lines" id="sum-lines" aria-live="polite"></ul>
          <div class="summary__total"><span>Total indikatif</span><b id="sum-total">—</b></div>
          <p class="summary__note">Harga add-on contoh (placeholder). Belum termasuk mobilisasi, akomodasi, dan pajak. DP 30–50% dibayar sebelum jadwal dikunci.</p>
          <a href="{{ route('faq') }}" class="link-accent" style="font-size: var(--fs-14)">Baca FAQ pemesanan</a>
        </aside>

      </div>
    </div>
  </section>

  <section class="section section--alt">
    <div class="container cta-band__inner">
      <h2>Lebih nyaman bicara langsung?</h2>
      <div class="btn-row">
        @if (config('site.whatsapp'))
          <a href="https://wa.me/{{ config('site.whatsapp') }}" class="btn btn--primary" target="_blank" rel="noopener">Chat WhatsApp</a>
        @endif
        <a href="{{ route('contact') }}" class="btn @if (config('site.whatsapp')) btn--ghost @else btn--primary @endif">Halaman Kontak</a>
      </div>
    </div>
  </section>
@endsection
