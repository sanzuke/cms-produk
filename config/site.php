<?php

/*
 | Identitas & kontak situs publik. Semua nilai PLACEHOLDER sampai klien
 | memberi data asli; ubah lewat .env, bukan di kode.
 | (Nantinya dipindah ke modul Pengaturan di admin, PLAN §6.15.)
 */
return [
    'name' => env('SITE_NAME', 'Strata Visual'),
    'tagline' => 'Fotografi & Videografi Pertambangan',
    'description' => 'Dokumentasi foto, video, dan aerial untuk laporan progres, company profile, K3, dan keberlanjutan — dikerjakan oleh fotografer yang terbiasa bekerja di area tambang.',

    'email' => env('SITE_EMAIL', 'halo@stratavisual.id'),
    // Penerima notifikasi inquiry baru (pisahkan koma). Kosong = pakai 'email' di atas.
    'notification_emails' => array_values(array_filter(array_map('trim', explode(',', (string) env('SITE_NOTIFY_EMAILS', ''))))),
    // Format internasional tanpa +, untuk tautan wa.me. Contoh: 628123456789
    'whatsapp' => env('SITE_WHATSAPP', ''),
    'phone_display' => env('SITE_PHONE', '+62 8XX-XXXX-XXXX'),
    'hours' => env('SITE_HOURS', 'Senin–Sabtu, 08.00–17.00'),
    'service_area' => env('SITE_SERVICE_AREA', 'Kalimantan · Sulawesi · (isi sesuai klien)'),
    // Format schema.org (mis. "Mo-Sa 08:00-17:00"); kosong = tidak dikirim ke mesin pencari.
    'hours_schema' => env('SITE_HOURS_SCHEMA', ''),
    // Path di disk public (diisi lewat admin) untuk og:image default.
    'og_image' => null,

    // Penjualan & pembayaran (transfer manual, PLAN §8). Rekening diisi lewat Pengaturan.
    'bank_name' => env('SITE_BANK_NAME', ''),
    'bank_account' => env('SITE_BANK_ACCOUNT', ''),
    'bank_holder' => env('SITE_BANK_HOLDER', ''),
    'dp_percent' => (int) env('SITE_DP_PERCENT', 50),
    'quotation_valid_days' => (int) env('SITE_QUOTATION_VALID_DAYS', 14),
    'quotation_terms' => "1. Harga belum termasuk mobilisasi/akomodasi kecuali tercantum.\n2. DP dibayar sebelum jadwal dikunci; pelunasan sebelum unduhan hasil resolusi penuh.\n3. Revisi sesuai jumlah yang tercantum; revisi tambahan dikenakan biaya.\n4. Penjadwalan ulang karena cuaca atau izin lokasi tidak menghanguskan DP.\n5. Hak penggunaan hasil dan kerahasiaan mengikuti kesepakatan tertulis (draf — sesuaikan dengan klien).",

    // Stat bar beranda (placeholder, PLAN §4.1.3).
    'stats' => [
        ['value' => 'XXX', 'label' => 'jam terbang'],
        ['value' => 'XX', 'label' => 'site didokumentasikan'],
        ['value' => 'X.XXX+', 'label' => 'foto terkirim'],
        ['value' => '0', 'label' => 'insiden'],
    ],
];
