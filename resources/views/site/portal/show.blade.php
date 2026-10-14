@extends('layouts.site')

@section('title', 'Portal Klien — '.$order->number)
@section('description', 'Portal klien: hasil dokumentasi, tagihan, dan revisi untuk order Anda.')
@section('canonical', route('portal.show', $token))
@section('head')
<meta name="robots" content="noindex, nofollow, noarchive">
<meta name="referrer" content="no-referrer">
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('site/css/portal.css') }}">
@endpush

@use('Illuminate\Support\Number')

@section('content')
  @php
    $money = fn (int $n) => Number::currency($n, 'IDR', 'id', 0);
    $cancelled = $order->status === 'cancelled';
  @endphp
  <section class="portal-hero">
    <div class="container">
      <span class="eyebrow">Portal klien · {{ $order->number }}</span>
      <h1>{{ $order->title }}</h1>
      <p class="portal-hero__meta">
        {{ $order->client_name }}@if ($order->client_company), {{ $order->client_company }}@endif
        @if ($order->site) · Lokasi: {{ $order->site }}@endif
        @if ($delivery->expires_at) · Link berlaku sampai {{ $delivery->expires_at->translatedFormat('d F Y') }}@endif
      </p>
    </div>
  </section>

  <div class="container portal">
    @if (session('portal_status'))
      <p class="portal-flash portal-flash--ok" role="status">{{ session('portal_status') }}</p>
    @endif
    @if (session('portal_error'))
      <p class="portal-flash portal-flash--err" role="alert">{{ session('portal_error') }}</p>
    @endif

    <section class="portal-block" aria-labelledby="status-h">
      <h2 id="status-h">Status pekerjaan</h2>
      @if ($cancelled)
        <p><span class="badge badge--err">Dibatalkan</span> Order ini dibatalkan. Hubungi kami bila ada pertanyaan.</p>
      @else
        <ol class="timeline">
          @foreach ($statuses as $key => $label)
            @php $state = $loop->index < $currentIndex ? 'done' : ($loop->index === $currentIndex ? 'current' : 'todo'); @endphp
            <li class="timeline__step timeline__step--{{ $state }}" @if ($state === 'current') aria-current="step" @endif>
              <span class="timeline__dot" aria-hidden="true">{{ $state === 'done' ? '✓' : $loop->iteration }}</span>
              <span class="timeline__label">{{ $label }}</span>
            </li>
          @endforeach
        </ol>
      @endif
    </section>

    <section class="portal-block" id="tagihan" aria-labelledby="tagihan-h">
      <h2 id="tagihan-h">Tagihan</h2>
      @forelse ($invoices as $invoice)
        @php
            $badge = ['paid' => 'ok', 'proof_submitted' => 'info', 'unpaid' => $invoice->isOverdue() ? 'err' : 'warn'][$invoice->status] ?? '';
            $open = in_array($invoice->status, ['unpaid', 'proof_submitted'], true);
            $bag = 'proof-'.$invoice->id;
        @endphp
        <article class="card invoice" id="invoice-{{ $invoice->id }}">
          <div class="invoice__head">
            <div>
              <strong>{{ $invoice->number }}</strong>
              <span class="invoice__type">{{ \App\Models\Invoice::TYPES[$invoice->type] ?? $invoice->type }}</span>
            </div>
            <span class="badge badge--{{ $badge }}">{{ \App\Models\Invoice::STATUSES[$invoice->status] ?? $invoice->status }}@if ($invoice->isOverdue()), lewat jatuh tempo @endif</span>
          </div>
          <dl class="invoice__meta">
            <div><dt>Jumlah</dt><dd class="invoice__amount">{{ $money($invoice->amount) }}</dd></div>
            <div><dt>Jatuh tempo</dt><dd>{{ $invoice->due_date?->translatedFormat('d F Y') ?? '–' }}</dd></div>
            @if ($invoice->paid_at)<div><dt>Dibayar</dt><dd>{{ $invoice->paid_at->translatedFormat('d F Y') }}</dd></div>@endif
          </dl>

          @if ($open)
            @if ($bank['account'])
              <div class="invoice__bank">
                <h3>Transfer ke</h3>
                <p><strong>{{ $bank['name'] }}</strong> · <span class="mono">{{ $bank['account'] }}</span>@if ($bank['holder']) · a.n. {{ $bank['holder'] }}@endif</p>
                <p class="portal-note">Cantumkan nomor invoice <strong>{{ $invoice->number }}</strong> pada berita transfer.</p>
              </div>
            @else
              <div class="invoice__bank">
                <p class="portal-note">Detail rekening tujuan transfer akan kami kirimkan lewat email atau WhatsApp. Hubungi {{ config('site.email') }} bila belum menerimanya.</p>
              </div>
            @endif

            <form method="post" action="{{ route('portal.proof', [$token, $invoice->id]) }}" enctype="multipart/form-data" class="proof-form">
              @csrf
              <div class="field">
                <label for="proof-{{ $invoice->id }}">{{ $invoice->status === 'proof_submitted' ? 'Ganti bukti pembayaran' : 'Unggah bukti pembayaran' }}</label>
                <input type="file" id="proof-{{ $invoice->id }}" name="proof" class="input" accept="image/jpeg,image/png,application/pdf" required aria-describedby="proof-help-{{ $invoice->id }}">
                <small id="proof-help-{{ $invoice->id }}" class="portal-note">JPG, PNG, atau PDF, maksimal 5 MB.</small>
                @if ($errors->hasBag($bag) && $errors->getBag($bag)->has('proof'))
                  <small class="field-error" role="alert">{{ $errors->getBag($bag)->first('proof') }}</small>
                @endif
              </div>
              <button type="submit" class="btn btn--primary btn--sm">Kirim bukti</button>
              @if ($invoice->status === 'proof_submitted')
                <span class="portal-note">Bukti sudah kami terima dan sedang diperiksa.</span>
              @endif
            </form>
          @endif
        </article>
      @empty
        <p class="portal-note">Belum ada tagihan untuk order ini.</p>
      @endforelse
    </section>

    <section class="portal-block" id="berkas" aria-labelledby="berkas-h">
      <h2 id="berkas-h">Hasil dokumentasi</h2>

      @if ($locked)
        <p class="portal-flash portal-flash--warn" role="note">Unduhan berkas dibuka setelah tagihan pelunasan lunas. Pratinjau foto tetap bisa dilihat di bawah.</p>
      @endif

      @if (count($files) === 0)
        <p class="portal-note">Berkas belum diunggah.</p>
      @else
        <div class="portal-actions">
          @if ($locked)
            <span class="btn btn--ghost btn--sm is-disabled" aria-disabled="true">Unduh semua (ZIP) · terkunci</span>
            <span class="btn btn--ghost btn--sm is-disabled" aria-disabled="true">Unduh favorit (ZIP) · terkunci</span>
          @else
            <a class="btn btn--primary btn--sm" href="{{ route('portal.zip', $token) }}">Unduh semua (ZIP)</a>
            @if ($favoriteCount > 0)
              <a class="btn btn--ghost btn--sm" href="{{ route('portal.zip', [$token, 'favorit' => 1]) }}">Unduh favorit (ZIP) · <span data-fav-count>{{ $favoriteCount }}</span></a>
            @else
              <span class="btn btn--ghost btn--sm is-disabled" aria-disabled="true">Unduh favorit (ZIP) · belum ada</span>
            @endif
          @endif
        </div>

        <ul class="gallery">
          @foreach ($files as $file)
            <li class="gallery__item" id="berkas-{{ $file['index'] }}">
              @if ($file['is_image'])
                <img class="gallery__thumb" loading="lazy" src="{{ route('portal.file', [$token, $file['index'], 'preview' => 1]) }}" alt="{{ $file['name'] }}">
              @else
                <div class="gallery__doc" aria-hidden="true">{{ strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'FILE') }}</div>
              @endif
              <div class="gallery__body">
                <span class="gallery__name" title="{{ $file['name'] }}">{{ $file['name'] }}</span>
                <span class="gallery__size">{{ Number::fileSize($file['size']) }}</span>
                <div class="gallery__tools">
                  <form method="post" action="{{ route('portal.favorite', $token) }}" class="js-fav">
                    @csrf
                    <input type="hidden" name="index" value="{{ $file['index'] }}">
                    <button type="submit" class="fav" aria-pressed="{{ $file['favorite'] ? 'true' : 'false' }}" aria-label="Tandai favorit: {{ $file['name'] }}">
                      <span aria-hidden="true" data-fav-icon>{{ $file['favorite'] ? '★' : '☆' }}</span> Favorit
                    </button>
                  </form>
                  @if ($locked)
                    <span class="portal-note">Unduhan terkunci</span>
                  @else
                    <a class="btn btn--ghost btn--sm" href="{{ route('portal.file', [$token, $file['index']]) }}">Unduh</a>
                  @endif
                </div>
              </div>
            </li>
          @endforeach
        </ul>
      @endif
    </section>

    <section class="portal-block" id="komentar" aria-labelledby="komentar-h">
      <h2 id="komentar-h">Revisi &amp; komentar</h2>

      @forelse ($comments as $comment)
        <article class="comment comment--{{ $comment->author }}">
          <header>
            <strong>{{ $comment->author === 'staff' ? config('site.name').' · '.$comment->author_name : $comment->author_name }}</strong>
            <time datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->translatedFormat('d F Y H:i') }}</time>
          </header>
          <p>{!! nl2br(e($comment->body)) !!}</p>
        </article>
      @empty
        <p class="portal-note">Belum ada komentar. Tulis catatan revisi Anda di bawah.</p>
      @endforelse

      <form method="post" action="{{ route('portal.comment', $token) }}" class="comment-form">
        @csrf
        <div class="field">
          <label for="comment-body">Tulis komentar atau permintaan revisi</label>
          <textarea id="comment-body" name="body" class="textarea" rows="4" minlength="3" maxlength="2000" required>{{ old('body') }}</textarea>
          @if ($errors->hasBag('comment') && $errors->getBag('comment')->has('body'))
            <small class="field-error" role="alert">{{ $errors->getBag('comment')->first('body') }}</small>
          @endif
        </div>
        <button type="submit" class="btn btn--primary btn--sm">Kirim komentar</button>
      </form>
    </section>
  </div>
@endsection

@push('scripts')
<script src="{{ asset('site/js/portal.js') }}" defer></script>
@endpush
