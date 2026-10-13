<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * Konten PLACEHOLDER dari docs/PLAN.md §4 agar situs publik langsung terisi.
 * Semua harga/angka/nama klien fiktif; ganti lewat admin sebelum rilis.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->services();
        $this->packages();
        $this->projects();
        $this->testimonials();
        $this->posts();
    }

    private function services(): void
    {
        $faq = fn (string $topic) => [
            ['q' => "Apakah fotografer sudah memiliki induksi K3 untuk {$topic}?", 'a' => 'Ya. Setiap site memiliki induksi sendiri; kami mengikuti seluruh prosedur dan membawa APD lengkap. (placeholder)'],
            ['q' => 'Berapa lama hasil diterima?', 'a' => 'Umumnya 3–7 hari kerja setelah pengambilan gambar, tergantung volume. (placeholder)'],
        ];

        $rows = [
            ['foto-operasional-tambang', 'Foto Operasional Tambang', 'Foto kerja yang akurat untuk laporan, HSE, dan publikasi perusahaan.',
                ['Laporan progres bulanan', 'Dokumentasi HSE', 'Potret tim & fasilitas', 'Media sosial perusahaan'],
                ['80–120 foto terkurasi per hari kerja', 'Retouch dasar & koreksi warna', 'Penamaan file terstruktur', 'Hasil ≤ 3 hari kerja via portal klien'], 15000000],
            ['foto-produk-alat-berat', 'Foto Produk & Alat Berat', 'Foto unit dan alat berat yang detail untuk katalog dealer dan kontraktor.',
                ['Katalog dealer alat berat', 'Penawaran unit', 'Profil armada kontraktor'],
                ['Foto 360°/detail per unit', 'Background bersih bila diperlukan', 'Format web & cetak'], 12000000],
            ['video-company-profile-tambang', 'Video Company Profile Perusahaan Tambang', 'Video profil sinematik yang menjelaskan operasi Anda dengan jelas dan aman.',
                ['Presentasi investor & tender', 'Pameran & seminar', 'Rekrutmen'],
                ['Video 3–5 menit (4K)', 'Voice-over & subtitle', 'Musik berlisensi', '2 kali revisi'], 45000000],
            ['video-safety-induksi', 'Video Safety & Induksi', 'Video induksi dan keselamatan yang konsisten dan mudah dipahami pekerja baru.',
                ['Induksi karyawan & tamu', 'Kampanye K3', 'Prosedur kerja aman'],
                ['Video 2–5 menit', 'Subtitle & versi bahasa', 'Format untuk layar induksi'], 30000000],
            ['aerial-drone-foto-video-site', 'Aerial Drone — Foto & Video Site', 'Foto dan video udara untuk memantau pit, stockpile, hauling road, dan pelabuhan.',
                ['Progres pit & stockpile', 'Hauling road', 'Jetty/pelabuhan', 'Reklamasi'],
                ['30–50 foto udara', 'Video 60 detik', 'Pilot bersertifikat & asuransi (isi sesuai fakta)', 'Peta orthomosaic (jika tersedia)'], 18000000],
            ['monitoring-progres-berkala', 'Monitoring Progres Berkala', 'Foto udara pada titik tetap tiap bulan dengan laporan perbandingan.',
                ['Laporan manajemen bulanan', 'Pelaporan ESG', 'Audit progres'],
                ['Foto udara titik tetap', 'Laporan perbandingan bulan ke bulan', 'Time-lapse progres', 'Kontrak ≥ 3 bulan'], 20000000],
            ['dokumentasi-event-csr-reklamasi', 'Dokumentasi Event, CSR & Reklamasi', 'Liputan acara perusahaan, program CSR, dan perkembangan reklamasi.',
                ['Program CSR', 'Penanaman & reklamasi', 'Acara internal & kunjungan'],
                ['Liputan foto/video', 'Highlight video singkat', 'Galeri privat untuk tim humas'], 10000000],
        ];

        foreach ($rows as $i => [$slug, $title, $summary, $useCases, $deliverables, $price]) {
            Service::updateOrCreate(['slug' => $slug], [
                'title' => $title,
                'summary' => $summary,
                'body' => '<p>'.$summary.' Deskripsi lengkap diisi bersama klien. (placeholder)</p>',
                'use_cases' => $useCases,
                'deliverables' => $deliverables,
                'requirements' => "Induksi K3 dan izin masuk lokasi diurus bersama klien sebelum jadwal.\nIzin terbang drone dan kondisi cuaca diperiksa sebelum pengambilan gambar. (placeholder)",
                'faq' => $faq(mb_strtolower($title)),
                'price_from' => $price,
                'status' => 'published',
                'sort_order' => ($i + 1) * 10,
            ]);
        }
    }

    private function packages(): void
    {
        $rows = [
            ['dokumentasi-harian', 'Dokumentasi Harian', '1 hari kerja di lapangan.', 15000000, null, ['1 fotografer', '80–120 foto terkurasi', 'Retouch dasar', 'Hasil ≤ 3 hari kerja'], false],
            ['aerial-survey-lite', 'Aerial Survey Lite', 'Foto dan video udara untuk 1–2 area.', 18000000, null, ['1 hari kerja', '30–50 foto udara', '1 video 60 detik', '1–2 area'], false],
            ['profile-tambang', 'Profile Tambang', 'Tim foto, video, dan drone.', 60000000, null, ['2–3 hari kerja', 'Video 3–5 menit + 150 foto', 'Voice-over & subtitle', '2 kali revisi'], true],
            ['monitoring-bulanan', 'Monitoring Bulanan', 'Pemantauan progres rutin.', 20000000, '/ bulan', ['Foto udara titik tetap', 'Laporan perbandingan', 'Kontrak ≥ 3 bulan'], false],
            ['enterprise-custom', 'Enterprise / Custom', 'Kebutuhan multi-site dan retainer.', null, null, ['Multi-site', 'Retainer tahunan', 'NDA', 'Laporan khusus'], false],
        ];

        foreach ($rows as $i => [$slug, $name, $desc, $price, $unit, $features, $popular]) {
            Package::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'description' => $desc,
                'price' => $price,
                'price_unit' => $unit,
                'price_note' => $price ? 'Belum termasuk mobilisasi/akomodasi lokasi terpencil. Harga placeholder.' : null,
                'features' => $features,
                'is_popular' => $popular,
                'is_active' => true,
                'sort_order' => ($i + 1) * 10,
            ]);
        }
    }

    private function projects(): void
    {
        $rows = [
            ['progres-pit-batubara', 'Progres Pit Batubara', 'aerial', 'batubara', 'pit', 2025, true],
            ['dokumentasi-hauling-road', 'Dokumentasi Hauling Road', 'foto', 'batubara', 'hauling', 2025, true],
            ['company-profile-tambang-nikel', 'Company Profile Tambang Nikel', 'video', 'nikel', 'plant', 2024, true],
            ['monitoring-jetty-bulanan', 'Monitoring Jetty Bulanan', 'aerial', 'nikel', 'pelabuhan', 2025, true],
            ['reklamasi-pascatambang', 'Reklamasi Pascatambang', 'foto', 'emas', 'reklamasi', 2024, true],
            ['video-induksi-k3', 'Video Induksi K3', 'video', 'lainnya', 'pit', 2024, false],
        ];

        foreach ($rows as $i => [$slug, $title, $cat, $com, $area, $year, $featured]) {
            Project::updateOrCreate(['slug' => $slug], [
                'title' => $title,
                'category' => $cat,
                'commodity' => $com,
                'work_area' => $area,
                'year' => $year,
                'client_name' => 'Klien '.($i + 1),
                'show_client' => false,
                'location' => 'Lokasi disamarkan',
                'show_location' => false,
                'challenge' => 'Akses terbatas, cuaca berubah cepat, dan jadwal operasi yang tidak boleh terganggu. (placeholder)',
                'approach' => 'Survei lokasi, induksi K3, dan jadwal pengambilan gambar di luar jam kritis produksi. (placeholder)',
                'result' => 'Materi siap pakai untuk laporan dan publikasi, diserahkan lewat galeri privat. (placeholder)',
                'specs' => '2 hari kerja · 1 fotografer + 1 pilot drone (placeholder)',
                'featured' => $featured,
                'status' => 'published',
                'sort_order' => ($i + 1) * 10,
            ]);
        }
    }

    private function testimonials(): void
    {
        foreach ([1, 2, 3] as $n) {
            Testimonial::updateOrCreate(['author' => "Nama Klien {$n}"], [
                'role' => 'Jabatan',
                'company' => "PT Contoh {$n}",
                'quote' => 'Hasilnya rapi, tepat waktu, dan tim paham aturan keselamatan di lapangan. (placeholder, ganti dengan testimoni asli)',
                'allow_public' => true,
                'sort_order' => $n * 10,
            ]);
        }
    }

    private function posts(): void
    {
        $titles = [
            'Cara Menyiapkan Dokumentasi Progres Tambang untuk Laporan Bulanan',
            'Foto Udara vs Survei Konvensional: Kapan Masing-masing Digunakan',
            'Checklist Sebelum Fotografer Masuk Site: Induksi, APD, dan Izin',
            'Mengapa Perusahaan Tambang Butuh Video Company Profile',
            'Dokumentasi Reklamasi & Pascatambang untuk Pelaporan ESG',
            'Tips Memilih Vendor Dokumentasi Tambang yang Aman',
        ];

        foreach ($titles as $i => $title) {
            $slug = \Illuminate\Support\Str::slug($title);
            Post::updateOrCreate(['slug' => $slug], [
                'type' => 'article',
                'title' => $title,
                'excerpt' => 'Ringkasan artikel placeholder. Isi lengkap ditulis bersama klien.',
                'body' => '<p>Isi artikel placeholder untuk "'.e($title).'".</p><h2>Poin utama</h2><p>Tulis isi sebenarnya di admin.</p>',
                'category' => 'Panduan',
                'status' => 'published',
                'publish_at' => now()->subDays(($i + 1) * 5),
            ]);
        }
    }
}
