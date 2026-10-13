<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\View\View;

class PackageController extends Controller
{
    /**
     * Add-on per order. PLACEHOLDER: harga (rupiah penuh) hanya contoh untuk
     * ringkasan indikatif di form pesan; ganti saat tarif resmi tersedia.
     * `qty` = batas jumlah bila add-on bisa dipesan lebih dari satu.
     */
    public const ADDONS = [
        'extra-day' => ['label' => 'Hari tambahan', 'desc' => '1 hari kerja tambahan, tim & peralatan sama.', 'unit' => '/ hari', 'price' => 2500000, 'qty' => 30],
        'extra-revision' => ['label' => 'Revisi tambahan', 'desc' => 'Di luar 2× revisi yang sudah termasuk paket.', 'unit' => '/ revisi', 'price' => 750000, 'qty' => 20],
        'extra-drone' => ['label' => 'Drone tambahan', 'desc' => '1 pilot dan 1 unit drone untuk area kedua.', 'unit' => '', 'price' => 3000000, 'qty' => null],
        'express' => ['label' => 'Express delivery', 'desc' => 'Hasil dikirim dalam 24 jam setelah edit selesai.', 'unit' => '', 'price' => 1500000, 'qty' => null],
        'watermark' => ['label' => 'Watermark custom', 'desc' => 'Watermark khusus (nama/logo klien) pada pratinjau.', 'unit' => '', 'price' => 750000, 'qty' => null],
        'timelapse' => ['label' => 'Montase time-lapse', 'desc' => 'Montase progres dari foto udara bulanan.', 'unit' => '', 'price' => 1000000, 'qty' => null],
    ];

    public function index(): View
    {
        $packages = Package::active()->get();

        return view('site.packages.index', [
            'standard' => $packages->whereNotNull('price')->values(),
            'custom' => $packages->whereNull('price')->values(),
            'addons' => self::ADDONS,
        ]);
    }
}
