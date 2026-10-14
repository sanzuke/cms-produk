<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInquiryRequest;
use App\Models\Inquiry;
use App\Models\Package;
use App\Models\Service;
use App\Support\InquiryNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function create(Request $request): View
    {
        $services = Service::published()->get(['id', 'slug', 'title']);
        $packages = Package::active()->get();
        $standard = $packages->whereNotNull('price')->values();

        // Preselect dari query string: ?layanan=<slug>, ?paket=<slug>, ?jalur=custom.
        $paketSlug = (string) $request->query('paket', '');
        $chosenPackage = $packages->firstWhere('slug', $paketSlug);

        $defaultJalur = ($request->query('jalur') === 'custom' || ($chosenPackage && $chosenPackage->price === null))
            ? 'custom'
            : 'paket';

        return view('site.order.create', [
            'services' => $services,
            'standard' => $standard,
            'addons' => PackageController::ADDONS,
            'commodities' => StoreInquiryRequest::COMMODITIES,
            'durations' => StoreInquiryRequest::DURATIONS,
            'defaultService' => $services->firstWhere('slug', (string) $request->query('layanan', ''))?->id,
            'defaultPackage' => $chosenPackage && $chosenPackage->price !== null ? $chosenPackage->slug : null,
            'defaultJalur' => $defaultJalur,
        ]);
    }

    public function store(StoreInquiryRequest $request): RedirectResponse
    {
        // Honeypot terisi: pura-pura sukses, jangan simpan apa pun.
        if ($request->isBot()) {
            return redirect()->route('order.thanks')->with('inquiry', ['ref' => null]);
        }

        $data = $request->validated();

        $paths = [];
        foreach ($request->file('attachments', []) as $file) {
            $paths[] = $file->store('inquiries', 'local');
        }

        $inquiry = Inquiry::create([
            'name' => $data['name'],
            'company' => $data['company'],
            'position' => $data['position'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'],
            'service_id' => $data['service_id'] ?? null,
            'site' => $data['site'],
            'commodity' => $data['commodity'] ?? null,
            'needed_date' => $data['needed_date'],
            'duration' => $data['duration'] ?? null,
            'brief' => $this->composeBrief($data),
            'attachments' => $paths ?: null,
            'status' => 'new',
        ]);

        InquiryNotifier::notifyStaff($inquiry);
        InquiryNotifier::confirmToRequester($inquiry);

        return redirect()->route('order.thanks')->with('inquiry', [
            'ref' => $inquiry->reference(),
        ]);
    }

    public function thanks(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('inquiry')) {
            return redirect()->route('order.create');
        }

        return view('site.order.thanks', ['ref' => $request->session()->get('inquiry')['ref'] ?? null]);
    }

    /** Gabungkan pilihan wizard yang tidak punya kolom sendiri ke teks brief. */
    private function composeBrief(array $data): ?string
    {
        $lines = [];

        if (($data['jalur'] ?? 'paket') === 'custom') {
            $lines[] = 'Jalur: Penawaran custom';
        } else {
            if (! empty($data['paket'])) {
                $lines[] = 'Paket diminati: '.Package::where('slug', $data['paket'])->value('name');
            }

            $addons = [];
            foreach ($data['addon'] ?? [] as $key) {
                $addon = PackageController::ADDONS[$key];
                $qty = $addon['qty'] ? max(1, (int) ($data['addon_qty'][$key] ?? 1)) : 1;
                $addons[] = $addon['label'].($addon['qty'] ? ' ×'.$qty : '');
            }
            if ($addons) {
                $lines[] = 'Add-on: '.implode(', ', $addons);
            }
        }

        $text = trim((string) ($data['brief'] ?? ''));
        if ($text !== '') {
            $lines[] = '';
            $lines[] = $text;
        }

        return $lines ? implode("\n", $lines) : null;
    }
}
