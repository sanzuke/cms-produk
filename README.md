# Strata Visual — Company Profile + CMS

Situs company profile, CMS admin, dan penjualan layanan untuk jasa foto/video/aerial pertambangan.
Nama "Strata Visual" masih placeholder.

- Rencana produk: [`docs/PLAN.md`](docs/PLAN.md)
- Prototipe desain statis (acuan visual sebelum dipindah ke Blade): [`design/`](design/README.md)

## Stack

- Laravel 13 (PHP ≥ 8.3), Blade
- Filament 5 sebagai panel admin/CMS di `/admin`
- Vite + Tailwind CSS 4 untuk aset front-end
- Database: SQLite (lokal) / MySQL atau MariaDB (hosting)

## Setup lokal

```sh
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed    # --seed mengisi konten placeholder (hanya non-production)
php artisan make:filament-user
php artisan serve             # → http://localhost:8000 (admin: /admin)
```

Situs publik memakai CSS statis di `public/site/` (tanpa build Node). Preview prototipe desain lama: `php -S 127.0.0.1:8090 -t design`.

## Deploy ke shared hosting (cPanel)

1. Build aset di lokal: `npm ci && npm run build` (hosting umumnya tanpa Node). Unggah folder `public/build/` bersama kode.
2. Unggah kode ke luar `public_html` (mis. `~/strata`), lalu arahkan document root domain ke `~/strata/public`.
   Jika document root tidak bisa diubah, salin isi `public/` ke `public_html` dan sesuaikan path `vendor/autoload.php` serta `bootstrap/app.php` di `public_html/index.php`.
3. Di server: `composer install --no-dev --optimize-autoloader` (atau unggah `vendor/` hasil build lokal jika tidak ada SSH).
4. Buat `.env` dari `.env.example`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domain-anda`, `DB_CONNECTION=mysql` + kredensial database dari cPanel, lalu `php artisan key:generate`.
5. `php artisan migrate --force`, `php artisan storage:link`, `php artisan optimize`.
6. Pastikan `storage/` dan `bootstrap/cache/` dapat ditulis web server.
7. Antrian (`QUEUE_CONNECTION=database`): tambahkan cron `* * * * * php ~/strata/artisan queue:work --stop-when-empty` di cPanel, atau set `QUEUE_CONNECTION=sync` bila hosting tidak mengizinkan cron.
8. Aset Filament (`public/css/filament`, `public/js/filament`) tidak masuk git; dibuat otomatis oleh `composer install` (script `filament:upgrade`). Jika `vendor/` diunggah dari lokal tanpa SSH, unggah juga kedua folder itu.
9. Buat akun admin pertama: `php artisan make:filament-user`.
10. Role admin: `owner` (semua akses + kelola pengguna), `editor` (konten situs), `sales` (inquiry). Akun pertama otomatis `owner`; akun berikutnya dibuat dari menu Pengaturan → Pengguna (default `editor` bila dibuat via `make:filament-user`).
11. Identitas, kontak, WhatsApp, dan stat bar situs diatur owner di admin → Pengaturan → Pengaturan Situs (disimpan di tabel `settings`). Nilai awal ada di `config/site.php` dan `.env` (`SITE_*`) dan dipakai selama pengaturan belum disimpan.
12. Email notifikasi inquiry baru: isi SMTP hosting di `.env` (`MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` dengan domain sendiri agar tidak masuk spam) dan pastikan `APP_URL` benar (dipakai untuk tautan "Buka di admin"). Penerima diatur di Pengaturan Situs; kosong = email kontak. Email dikirim setelah respons ke pengunjung (tanpa queue worker); kegagalan SMTP hanya masuk `storage/logs`, form tetap tersimpan. Default `MAIL_MAILER=log` hanya menulis ke log.
13. SEO: `/sitemap.xml` dan `/robots.txt` dibuat dinamis (hanya konten terbit; `/admin` diblokir). Setelah live, daftarkan sitemap di Google Search Console. URL memakai `APP_URL`, jadi pastikan sudah domain asli. Jangan buat `public/robots.txt` statis (akan menimpa route).
14. Alur penjualan di admin (grup Penjualan; owner dan sales): Inquiry → **Buat penawaran** → kirim PDF ke klien → **Setujui & buat order** → invoice DP (persentase di Pengaturan) → klien transfer dan unggah bukti di portal → **Tandai lunas** (order otomatis *Dijadwalkan*) → shooting/editing → unggah hasil di **Hasil klien** → kirim tautan portal → invoice pelunasan.
15. Isi rekening (bank, nomor, atas nama), DP, dan syarat penawaran di Pengaturan Situs sebelum mengirim penawaran/invoice pertama; PDF dan portal memakainya.
16. Portal klien memakai tautan acak `/klien/{token}` (tanpa akun). Tautan bisa dibuat ulang atau diperpanjang di admin; portal `noindex` dan diblokir di robots. Selama belum lunas dan opsi "tahan unduhan" aktif, klien hanya melihat pratinjau kecil (maks 640 px) dan tidak bisa mengunduh. Pratinjau butuh ekstensi PHP `gd`; tanpa itu gambar tidak tampil di portal sampai lunas.
17. Berkas hasil besar: batas unggah ditentukan PHP hosting (`upload_max_filesize`, `post_max_size`) dan `memory_limit`; naikkan di cPanel (MultiPHP INI) bila hasil lebih besar dari beberapa MB. Unduhan ZIP butuh ekstensi `zip`. Berkas hasil dan bukti bayar disimpan di `storage/app/private` (bukan `public`); jangan arahkan document root ke `storage`.
