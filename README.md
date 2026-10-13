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
php artisan migrate
npm install
npm run dev        # terminal 1
php artisan serve  # terminal 2 → http://localhost:8000
```

Preview prototipe desain: `php -S 127.0.0.1:8090 -t design`.

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
