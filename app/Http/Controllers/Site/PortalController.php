<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Mail\ClientActivityMail;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderComment;
use App\Support\InquiryNotifier;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use ZipArchive;

/**
 * Portal klien tanpa akun: akses lewat link berisi token acak (Delivery::access_token).
 * Token hanya dicocokkan lewat query DB; berkas hanya dilayani dari path yang tersimpan di `files`.
 */
class PortalController extends Controller implements HasMiddleware
{
    /** Ekstensi gambar yang boleh ditampilkan inline sebagai pratinjau (SVG sengaja tidak termasuk). */
    private const PREVIEW_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    private const PREVIEW_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public static function middleware(): array
    {
        return [
            new Middleware(function (Request $request, Closure $next): Response {
                $response = $next($request);

                // Halaman berisi data klien: jangan di-cache, jangan diindeks, jangan bocorkan token lewat Referer.
                $response->headers->set('Cache-Control', 'no-store, private');
                $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
                $response->headers->set('Referrer-Policy', 'no-referrer');

                return $response;
            }),
        ];
    }

    public function show(string $token): View
    {
        $delivery = $this->resolve($token);
        $order = $delivery->order;

        $files = $this->files($delivery);
        $favorites = (array) $delivery->favorites;

        $statuses = collect(Order::STATUSES)->except('cancelled');
        $currentIndex = $statuses->keys()->search($order->status);

        return view('site.portal.show', [
            'delivery' => $delivery,
            'order' => $order,
            'token' => $token,
            'statuses' => $statuses,
            'currentIndex' => $currentIndex === false ? -1 : $currentIndex,
            'invoices' => $order->invoices()->where('status', '!=', 'cancelled')->orderBy('id')->get(),
            'files' => $files->map(fn (array $f) => $f + [
                'favorite' => in_array($f['path'], $favorites, true),
                'is_image' => $this->isPreviewableName($f['name']),
            ])->all(),
            'comments' => $order->comments()->reorder('id')->get(),
            'locked' => $delivery->downloadsLocked(),
            'favoriteCount' => $files->filter(fn (array $f) => in_array($f['path'], $favorites, true))->count(),
            'bank' => [
                'name' => config('site.bank_name'),
                'account' => config('site.bank_account'),
                'holder' => config('site.bank_holder'),
            ],
        ]);
    }

    public function proof(Request $request, string $token, int $invoice): RedirectResponse
    {
        $delivery = $this->resolve($token);

        // Invoice harus milik order ini; invoice order lain diperlakukan seolah tidak ada.
        $record = $delivery->order->invoices()->whereKey($invoice)->firstOrFail();
        $bag = "proof-{$record->id}";
        $back = route('portal.show', $token).'#invoice-'.$record->id;

        if (! in_array($record->status, ['unpaid', 'proof_submitted'], true)) {
            return redirect($back)->withErrors(['proof' => 'Invoice ini tidak lagi menerima bukti pembayaran.'], $bag);
        }

        $validator = Validator::make($request->all(), [
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [
            'proof.required' => 'Pilih berkas bukti pembayaran.',
            'proof.file' => 'Berkas tidak valid.',
            'proof.mimes' => 'Bukti pembayaran harus berupa JPG, PNG, atau PDF.',
            'proof.max' => 'Ukuran bukti pembayaran maksimal 5 MB.',
            'proof.uploaded' => 'Berkas gagal diunggah; ukurannya mungkin melebihi batas server.',
        ]);

        if ($validator->fails()) {
            return redirect($back)->withErrors($validator, $bag);
        }

        $old = $record->proof_path;
        $record->update([
            'proof_path' => $request->file('proof')->store('payment-proofs', 'local'),
            'status' => 'proof_submitted',
        ]);

        if ($old) {
            Storage::disk('local')->delete($old);
        }

        $this->notifyStaff(new ClientActivityMail($delivery->order, ClientActivityMail::PROOF, null, $record));

        return redirect($back)->with('portal_status', 'Bukti pembayaran terkirim. Kami akan memeriksa dan memperbarui status tagihan.');
    }

    public function favorite(Request $request, string $token): RedirectResponse|JsonResponse
    {
        $delivery = $this->resolve($token);

        $index = $request->input('index');
        $files = $delivery->files ?? [];

        if (! is_numeric($index) || ! isset($files[(int) $index])) {
            return $this->respond($request, $token, 'berkas', ['ok' => false], 422);
        }

        $path = $files[(int) $index]['path'];
        $favorites = array_values((array) $delivery->favorites);

        $isFavorite = ! in_array($path, $favorites, true);
        $favorites = $isFavorite
            ? [...$favorites, $path]
            : array_values(array_diff($favorites, [$path]));

        $delivery->update(['favorites' => $favorites]);

        return $this->respond($request, $token, 'berkas-'.(int) $index, [
            'ok' => true,
            'favorite' => $isFavorite,
            'count' => count($this->favoritePaths($delivery->fresh())),
        ]);
    }

    public function comment(Request $request, string $token): RedirectResponse
    {
        $delivery = $this->resolve($token);
        $back = route('portal.show', $token).'#komentar';

        $validator = Validator::make($request->all(), [
            'body' => ['required', 'string', 'min:3', 'max:2000'],
        ], [
            'body.required' => 'Tulis komentar revisi Anda.',
            'body.min' => 'Komentar minimal 3 karakter.',
            'body.max' => 'Komentar maksimal 2000 karakter.',
        ]);

        if ($validator->fails()) {
            return redirect($back)->withErrors($validator, 'comment')->withInput();
        }

        $order = $delivery->order;
        $comment = OrderComment::create([
            'order_id' => $order->id,
            'author' => 'client',
            'author_name' => $order->client_name,
            'body' => $validator->validated()['body'],
        ]);

        $this->notifyStaff(new ClientActivityMail($order, ClientActivityMail::COMMENT, $comment->body));

        return redirect($back)->with('portal_status', 'Komentar terkirim. Tim kami akan menanggapinya.');
    }

    public function file(Request $request, string $token, int $index): Response
    {
        $delivery = $this->resolve($token);
        $file = ($delivery->files ?? [])[$index] ?? null;
        $disk = Storage::disk('local');

        if ($file === null || ! $disk->exists($file['path'])) {
            abort(404);
        }

        // Pratinjau gambar: boleh walau unduhan terkunci, tidak dihitung sebagai unduhan.
        if ($request->boolean('preview')) {
            $mime = $disk->mimeType($file['path']);

            if (! in_array($mime, self::PREVIEW_MIMES, true)) {
                abort(404);
            }

            // Selama unduhan terkunci (belum lunas) klien hanya boleh melihat versi kecil;
            // pratinjau resolusi asli akan sama saja dengan membagikan hasil sebelum dibayar.
            if ($delivery->downloadsLocked()) {
                return $this->lowResPreview($disk, $file['path']);
            }

            return $disk->response($file['path'], $file['name'], [
                'Content-Type' => $mime,
                'X-Content-Type-Options' => 'nosniff',
            ], 'inline');
        }

        $this->guardDownload($delivery);
        $delivery->increment('download_count');

        return $disk->download($file['path'], $file['name'], ['X-Content-Type-Options' => 'nosniff']);
    }

    /** Versi JPEG kecil (maks 640 px, kualitas 60), di-cache di disk privat. Gagal aman: tidak pernah jatuh ke file asli. */
    private function lowResPreview(\Illuminate\Contracts\Filesystem\Filesystem $disk, string $path): Response
    {
        $cache = 'previews/'.sha1($path).'.jpg';

        if (! $disk->exists($cache)) {
            $bytes = $disk->get($path);
            $info = @getimagesizefromstring($bytes);
            $limit = $this->memoryLimitBytes();

            // Estimasi memori GD: ~5 byte per piksel. Terlalu besar untuk server ini → tidak ada pratinjau.
            if (! function_exists('imagecreatefromstring') || $info === false || ($info[0] * $info[1] * 5) > $limit * 0.6) {
                abort(404);
            }

            $src = @imagecreatefromstring($bytes);
            if ($src === false) {
                abort(404);
            }

            $scale = min(1, 640 / max($info[0], $info[1]));
            $dst = imagescale($src, max(1, (int) round($info[0] * $scale)), max(1, (int) round($info[1] * $scale)));
            imagedestroy($src);

            ob_start();
            imagejpeg($dst, null, 60);
            $disk->put($cache, ob_get_clean());
            imagedestroy($dst);
        }

        return $disk->response($cache, 'preview.jpg', [
            'Content-Type' => 'image/jpeg',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }

    private function memoryLimitBytes(): int
    {
        $raw = trim((string) ini_get('memory_limit'));
        if ($raw === '' || $raw === '-1') {
            return PHP_INT_MAX;
        }

        return (int) $raw * match (strtolower(substr($raw, -1))) {
            'g' => 1024 ** 3,
            'm' => 1024 ** 2,
            'k' => 1024,
            default => 1,
        };
    }

    public function zip(Request $request, string $token): Response|RedirectResponse
    {
        $delivery = $this->resolve($token);
        $this->guardDownload($delivery);

        $onlyFavorites = $request->boolean('favorit');
        $files = $this->files($delivery);

        if ($onlyFavorites) {
            $favorites = (array) $delivery->favorites;
            $files = $files->filter(fn (array $f) => in_array($f['path'], $favorites, true));
        }

        $disk = Storage::disk('local');
        $files = $files->filter(fn (array $f) => $disk->exists($f['path']))->values();

        if ($files->isEmpty()) {
            return redirect(route('portal.show', $token).'#berkas')
                ->with('portal_error', $onlyFavorites ? 'Belum ada berkas favorit untuk diunduh.' : 'Belum ada berkas untuk diunduh.');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'portal-zip-');
        $zip = new ZipArchive;

        if ($tmp === false || $zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'ZIP tidak dapat dibuat.');
        }

        $used = [];
        foreach ($files as $f) {
            $name = $this->uniqueZipName($f['name'], $used);
            $zip->addFile($disk->path($f['path']), $name);
            // Foto/video sudah terkompresi: simpan apa adanya agar cepat dan hemat CPU.
            $zip->setCompressionName($name, ZipArchive::CM_STORE);
        }

        $zip->close();

        $delivery->increment('download_count');

        $downloadName = Str::slug($delivery->order->number.'-'.($onlyFavorites ? 'favorit' : 'hasil')).'.zip';

        return response()->download($tmp, $downloadName, ['Content-Type' => 'application/zip'])->deleteFileAfterSend(true);
    }

    /** Token → Delivery lewat DB. Tidak ada / belum terbit → 404; kedaluwarsa → halaman 410. */
    private function resolve(string $token): Delivery
    {
        $delivery = Delivery::with('order')->where('access_token', $token)->first();

        if ($delivery === null || $delivery->published_at === null || $delivery->published_at->isFuture()) {
            abort(404);
        }

        if ($delivery->expires_at !== null && $delivery->expires_at->isPast()) {
            throw new HttpResponseException(response()->view('site.portal.expired', [
                'order' => $delivery->order,
            ], 410));
        }

        return $delivery;
    }

    private function guardDownload(Delivery $delivery): void
    {
        if ($delivery->downloadsLocked()) {
            abort(403, 'Unduhan terkunci sampai tagihan pelunasan lunas. Pratinjau foto tetap dapat dilihat.');
        }
    }

    /** @return \Illuminate\Support\Collection<int, array{index:int,path:string,name:string,size:int}> */
    private function files(Delivery $delivery)
    {
        return collect($delivery->files ?? [])->map(fn (array $f, int $i) => [
            'index' => $i,
            'path' => $f['path'],
            'name' => $f['name'],
            'size' => (int) ($f['size'] ?? 0),
        ])->values();
    }

    /** @return list<string> Favorit yang berkasnya masih ada di daftar. */
    private function favoritePaths(Delivery $delivery): array
    {
        return array_values(array_intersect((array) $delivery->favorites, array_column($delivery->files ?? [], 'path')));
    }

    private function isPreviewableName(string $name): bool
    {
        return in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), self::PREVIEW_EXTENSIONS, true);
    }

    /** @param array<string, true> $used */
    private function uniqueZipName(string $name, array &$used): string
    {
        $name = trim(str_replace(['/', '\\', "\0"], '-', $name), ". \t") ?: 'berkas';
        $base = pathinfo($name, PATHINFO_FILENAME);
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $candidate = $name;

        for ($n = 2; isset($used[strtolower($candidate)]); $n++) {
            $candidate = $base.' ('.$n.')'.($ext !== '' ? ".$ext" : '');
        }

        $used[strtolower($candidate)] = true;

        return $candidate;
    }

    private function respond(Request $request, string $token, string $fragment, array $json, int $status = 200): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json($json, $status);
        }

        return redirect(route('portal.show', $token).'#'.$fragment);
    }

    /** Notifikasi ke staf setelah respons terkirim; kegagalan SMTP tidak menggagalkan aksi klien. */
    private function notifyStaff(ClientActivityMail $mail): void
    {
        $recipients = InquiryNotifier::recipients();

        if ($recipients === []) {
            return;
        }

        dispatch(function () use ($mail, $recipients): void {
            try {
                Mail::to($recipients)->send($mail);
            } catch (Throwable $e) {
                report($e);
            }
        })->afterResponse();
    }
}
