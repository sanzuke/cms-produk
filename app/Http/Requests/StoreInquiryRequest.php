<?php

namespace App\Http\Requests;

use App\Http\Controllers\Site\PackageController;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInquiryRequest extends FormRequest
{
    public const COMMODITIES = ['batubara', 'nikel', 'emas', 'lainnya'];

    public const DURATIONS = ['1 hari kerja', '2–3 hari kerja', '1 minggu', 'Bulanan (kontrak 3+ bulan)', 'Belum ditentukan'];

    public function authorize(): bool
    {
        return true;
    }

    /** Honeypot: kolom tersembunyi yang tidak pernah diisi manusia. */
    public function isBot(): bool
    {
        return filled($this->input('website'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Bot dilewatkan tanpa validasi; controller langsung mengalihkan tanpa menyimpan.
        if ($this->isBot()) {
            return [];
        }

        return [
            'name' => ['required', 'string', 'max:120'],
            'company' => ['required', 'string', 'max:160'],
            'position' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{8,30}$/'],
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'id')->where('status', 'published')],
            'site' => ['required', 'string', 'max:255'],
            'commodity' => ['nullable', Rule::in(self::COMMODITIES)],
            'needed_date' => ['required', 'date', 'after_or_equal:today'],
            'duration' => ['nullable', Rule::in(self::DURATIONS)],
            'brief' => ['nullable', 'string', 'max:5000'],
            'jalur' => ['nullable', Rule::in(['paket', 'custom'])],
            'paket' => ['nullable', 'string', Rule::exists('packages', 'slug')->where('is_active', true)->whereNotNull('price')],
            'addon' => ['nullable', 'array'],
            'addon.*' => ['string', Rule::in(array_keys(PackageController::ADDONS))],
            'addon_qty' => ['nullable', 'array'],
            'addon_qty.*' => ['nullable', 'integer', 'min:1', 'max:30'],
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'consent' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'Kolom :attribute wajib diisi.',
            'string' => 'Kolom :attribute harus berupa teks.',
            'max.string' => 'Kolom :attribute maksimal :max karakter.',
            'email' => 'Format email tidak valid.',
            'phone.regex' => 'Nomor telepon/WhatsApp tidak valid. Gunakan angka, spasi, tanda + atau -.',
            'service_id.exists' => 'Layanan yang dipilih tidak tersedia.',
            'service_id.integer' => 'Layanan yang dipilih tidak valid.',
            'commodity.in' => 'Komoditas yang dipilih tidak valid.',
            'duration.in' => 'Perkiraan durasi yang dipilih tidak valid.',
            'needed_date.date' => 'Tanggal dibutuhkan tidak valid.',
            'needed_date.after_or_equal' => 'Tanggal dibutuhkan tidak boleh sebelum hari ini.',
            'jalur.in' => 'Jalur pemesanan tidak valid.',
            'paket.exists' => 'Paket yang dipilih tidak tersedia.',
            'addon.array' => 'Pilihan add-on tidak valid.',
            'addon.*.in' => 'Add-on yang dipilih tidak valid.',
            'addon_qty.*.integer' => 'Jumlah add-on harus berupa angka.',
            'addon_qty.*.min' => 'Jumlah add-on minimal :min.',
            'addon_qty.*.max' => 'Jumlah add-on maksimal :max.',
            'attachments.array' => 'Lampiran tidak valid.',
            'attachments.max' => 'Lampiran maksimal :max berkas.',
            'attachments.*.file' => 'Lampiran harus berupa berkas.',
            'attachments.*.uploaded' => 'Lampiran gagal diunggah. Pastikan ukurannya tidak lebih dari 10 MB.',
            'attachments.*.mimes' => 'Lampiran harus berformat PDF, JPG, atau PNG.',
            'attachments.*.max' => 'Setiap lampiran maksimal 10 MB.',
            'consent.accepted' => 'Anda perlu menyetujui ketentuan privasi untuk melanjutkan.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'company' => 'perusahaan',
            'position' => 'jabatan',
            'email' => 'email',
            'phone' => 'nomor WhatsApp',
            'service_id' => 'layanan',
            'site' => 'lokasi / nama site',
            'commodity' => 'komoditas',
            'needed_date' => 'tanggal dibutuhkan',
            'duration' => 'perkiraan durasi',
            'brief' => 'kebutuhan khusus',
            'consent' => 'persetujuan',
        ];
    }
}
