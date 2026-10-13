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
    // Format internasional tanpa +, untuk tautan wa.me. Contoh: 628123456789
    'whatsapp' => env('SITE_WHATSAPP', ''),
    'phone_display' => env('SITE_PHONE', '+62 8XX-XXXX-XXXX'),
    'hours' => env('SITE_HOURS', 'Senin–Sabtu, 08.00–17.00'),
    'service_area' => env('SITE_SERVICE_AREA', 'Kalimantan · Sulawesi · (isi sesuai klien)'),

    // Stat bar beranda (placeholder, PLAN §4.1.3).
    'stats' => [
        ['value' => 'XXX', 'label' => 'jam terbang'],
        ['value' => 'XX', 'label' => 'site didokumentasikan'],
        ['value' => 'X.XXX+', 'label' => 'foto terkirim'],
        ['value' => '0', 'label' => 'insiden'],
    ],
];
