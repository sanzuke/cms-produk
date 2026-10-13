@extends('layouts.site')

@section('title', 'FAQ')
@section('description', 'Pertanyaan umum soal induksi K3, izin masuk lokasi, izin terbang drone, kerahasiaan foto, revisi, cuaca, mobilisasi, dan hak penggunaan hasil dokumentasi tambang.')
@section('canonical', route('faq'))

@push('styles')
<link rel="stylesheet" href="{{ asset('site/css/pricing.css') }}">
<link rel="stylesheet" href="{{ asset('site/css/order.css') }}">
@endpush

@section('content')
  <section class="page-hero">
    <div class="container">
      <nav class="crumbs" aria-label="Remah roti"><a href="{{ route('home') }}">Beranda</a> <span aria-hidden="true">/</span> <span aria-current="page">FAQ</span></nav>
      <span class="eyebrow">Pertanyaan Umum</span>
      <h1>Yang Perlu Diketahui Sebelum Shooting.</h1>
      <p>Ketentuan soal K3, izin, dan kerahasiaan di bawah bersifat umum. Yang berlaku tetap mengikuti ketentuan site, regulasi terbaru, dan perintah petugas HSE di lokasi.</p>
    </div>
  </section>

  <section class="section">
    <div class="container faq-layout">

      <div>
        <div class="faq-group">
          <span class="eyebrow">Keamanan &amp; Izin</span>
          <h2>Keamanan dan Perizinan</h2>
          <div class="faq">

            <details open>
              <summary>Apakah fotografer sudah memiliki induksi K3 dan APD sendiri?</summary>
              <p>Setiap orang yang masuk area tambang wajib induksi K3 dari klien dan memakai APD yang sesuai. Tim kami mengikuti induksi yang sama sebelum shooting dan selalu membawa helm safety shoe, kacamata safety, dan rompi. Sertifikat serta izin yang dimiliki tim kami masih placeholder dan dapat diperlihatkan saat Anda meminta.</p>
            </details>

            <details>
              <summary>Siapa yang mengurus izin masuk lokasi dan izin terbang drone?</summary>
              <p>Izin masuk lokasi diurus oleh klien sebagai pemilik area: surat tugas, izin masuk, dan registrasi warga temporer bila diwajibkan. Untuk terbang, kami menyiapkan dokumen pendukung, tetapi penerbitan izin tetap menjadi kewenangan klien dan instansi yang bersangkutan.</p>
            </details>

            <details>
              <summary>Bagaimana cara kerja di area terbatas atau zona terlarang?</summary>
              <p>Ada area yang sama sekali tidak boleh dipotret. Sebelum masuk, kami meminta daftar area terbatas beserta titik yang boleh diakses. Peralatan tetap disimpan dalam tas sampai keluar dari area terbatas.</p>
            </details>

          </div>
        </div>

        <div class="faq-group">
          <span class="eyebrow">Privasi</span>
          <h2>Kerahasiaan dan Privasi</h2>
          <div class="faq">

            <details>
              <summary>Bagaimana kerahasiaan foto dijaga?</summary>
              <p>Semua hasil dikirim lewat portal klien privat dengan tautan bers masa berlaku, bukan lewat email atau chat terbuka. Nama klien dan lokasi tidak ditampilkan di website tanpa izin tertulis. Opsi watermark tersedia untuk setiap pratinjau.</p>
            </details>

            <details>
              <summary>Apakah NDA tersedia?</summary>
              <p>Ya. NDA dapat ditambahkan pada paket apa pun dan menjadi standar pada kontrak retainer atau proyek custom. Kami dapat memakai draf NDA untuk Anda tinjau lebih dulu, atau formulir NDA internal klien.</p>
            </details>

            <details>
              <summary>Hak penggunaan (lisensi) hasil foto dan video?</summary>
              <p>Secara default klien memperoleh hak penggunaan untuk keperluan internal perusahaan dan pelaporan. Penggunaan komersial seperti iklan atau penjualan saham perlu kesepakatan terpisah yang dihitung menurut cakupan dan jangka waktu.</p>
            </details>

          </div>
        </div>

        <div class="faq-group">
          <span class="eyebrow">Pengerjaan</span>
          <h2>Waktu, Revisi, dan Cuaca</h2>
          <div class="faq">

            <details>
              <summary>Berapa lama hasil diterima, dan berapa kali revisi?</summary>
              <p>Foto dikirim maksimal 3 hari kerja setelah shooting; video company profile 3–5 menit dikirim 5–7 hari kerja. Setiap paket sudah termasuk revisi (umumnya 1×, paket Profile Tambang 2×). Revisi di luar jatah tersebut dihitung sebagai add-on.</p>
            </details>

            <details>
              <summary>Bagaimana jika cuaca buruk atau izin terbang belum keluar?</summary>
              <p>Shooting udara selalu bergantung cuaca. Jika cuaca tidak aman pada hari yang dijadwalkan, jadwal kami pindahkan tanpa biaya tambahan dan tanpa hangusnya DP.</p>
            </details>

            <details>
              <summary>Bagaimana jika akses ke site berubah atau area ditolak masuk?</summary>
              <p>Perubahan aturan akses diinformasikan paling lambat H-3. Jika area yang disepakati tidak dapat diakses pada hari H, jadwal dipindahkan ke slot berikutnya tanpa biaya tambahan, dan DP tidak hangus.</p>
            </details>

            <details>
              <summary>Apakah mobilisasi dan akomodasi termasuk harga?</summary>
              <p>Belum. Biaya transportasi, akomodasi, dan perjalanan ke lokasi terpencil dihitung terpisah dan selalu dicantumkan di penawaran tertulis, sehingga tidak ada biaya tersembunyi setelah pekerjaan selesai. Lihat <a href="{{ route('packages.index') }}" class="link-accent">halaman Paket</a>.</p>
            </details>

            <details>
              <summary>Bagaimana cara pembayaran dan pelunasan?</summary>
              <p>Tahap pertama berupa transfer manual dengan DP 30–50% sebelum jadwal dikunci, sisanya setelah hasil disetujui. Pada implementasi sebenarnya, bukti transfer diunggah ke portal klien dan invoice dapat dilacak per order.</p>
            </details>

          </div>
        </div>
      </div>

      <aside class="faq-aside">
        <div class="card">
          <h3>Belum terjawab?</h3>
          <p>Tim kami siap menjelaskan hal yang lebih teknis tentang K3, izin, atau cakupan pekerjaan.</p>
          <div class="btn-row">
            <a href="{{ route('contact') }}" class="btn btn--primary btn--sm">Hubungi Kami</a>
            <a href="{{ route('order.create') }}" class="btn btn--ghost btn--sm">Minta Penawaran</a>
          </div>
        </div>
        <div class="card">
          <h3>Catatan Regulasi</h3>
          <p class="form-note">Halaman ini bukan rujukan hukum. Selalu verifikasi regulasi terbaru dan ketentuan internal site sebelum shooting.</p>
        </div>
      </aside>

    </div>
  </section>
@endsection
