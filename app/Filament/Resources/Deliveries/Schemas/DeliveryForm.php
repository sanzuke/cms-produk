<?php

namespace App\Filament\Resources\Deliveries\Schemas;

use App\Models\Delivery;
use App\Models\Order;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class DeliveryForm
{
    public const ACCEPTED_TYPES = [
        'image/jpeg', 'image/png', 'image/webp', 'image/gif',
        'video/mp4', 'video/quicktime', 'video/webm',
        'application/zip', 'application/x-zip-compressed',
        'application/pdf',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Order')
                    ->schema([
                        Select::make('order_id')
                            ->label('Order')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->disabled(fn (string $operation) => $operation === 'edit')
                            ->dehydrated()
                            ->unique(ignoreRecord: true)
                            ->helperText('Satu order hanya punya satu paket hasil. Hanya order yang belum punya hasil yang muncul.')
                            ->options(fn (?Delivery $record) => Order::query()
                                ->when(
                                    $record,
                                    fn ($q) => $q->whereKey($record->order_id),
                                    fn ($q) => $q->whereDoesntHave('delivery'),
                                )
                                ->latest()
                                ->limit(200)
                                ->get()
                                ->mapWithKeys(fn (Order $o) => [$o->id => trim("{$o->number} — {$o->client_name}".($o->client_company ? " ({$o->client_company})" : ''))])
                                ->all()),
                        TextEntry::make('portal_link')
                            ->label('Link portal klien')
                            ->state(fn (?Delivery $record) => $record?->portalUrl())
                            ->copyable()
                            ->copyMessage('Link disalin')
                            ->visible(fn (string $operation) => $operation === 'edit'),
                    ]),

                Section::make('Berkas hasil')
                    ->schema([
                        FileUpload::make('uploads')
                            ->label('Foto, video, dan dokumen')
                            ->multiple()
                            ->reorderable()
                            ->disk('local')
                            ->directory(fn ($get) => 'deliveries/'.(int) $get('order_id'))
                            ->visibility('private')
                            ->acceptedFileTypes(self::ACCEPTED_TYPES)
                            ->maxSize(51200)
                            ->storeFileNamesIn('upload_names')
                            ->downloadable()
                            ->preventFilePathTampering(true, fn (string $file, ?Delivery $record) => collect($record?->files ?? [])->contains('path', $file))
                            ->helperText('Foto, video (MP4/MOV/WebM), ZIP, atau PDF; maksimal 50 MB per berkas. Batas sebenarnya mengikuti upload_max_filesize / post_max_size PHP di hosting. Video besar sebaiknya dikirim sebagai ZIP atau diunggah per berkas.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Penerbitan')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Terbitkan ke klien')
                            ->helperText('Mati = link portal belum bisa dibuka klien (404).')
                            ->live()
                            ->columnSpanFull(),
                        DateTimePicker::make('published_at')
                            ->label('Terbit sejak')
                            ->seconds(false)
                            ->default(now())
                            ->visible(fn ($get) => (bool) $get('is_published')),
                        DateTimePicker::make('expires_at')
                            ->label('Kedaluwarsa')
                            ->seconds(false)
                            ->default(now()->addDays(30))
                            ->helperText('Setelah lewat, portal menampilkan halaman kedaluwarsa. Kosongkan bila tidak ingin dibatasi.'),
                        Toggle::make('require_full_payment')
                            ->label('Tahan unduhan sampai lunas')
                            ->helperText('Klien tetap bisa melihat pratinjau foto; unduhan terbuka setelah invoice pelunasan lunas.')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),

                Section::make('Aktivitas klien')
                    ->visible(fn (string $operation) => $operation === 'edit')
                    ->schema([
                        TextEntry::make('favorites_list')
                            ->label('Favorit klien')
                            ->state(fn (?Delivery $record) => self::favoriteNames($record))
                            ->placeholder('Belum ada favorit.')
                            ->listWithLineBreaks()
                            ->bulleted(),
                        TextEntry::make('comments_list')
                            ->label('Komentar revisi (hanya baca)')
                            ->state(fn (?Delivery $record) => self::commentLines($record))
                            ->placeholder('Belum ada komentar.')
                            ->listWithLineBreaks(),
                    ]),
            ]);
    }

    /** @return list<string> */
    private static function favoriteNames(?Delivery $record): array
    {
        if (! $record) {
            return [];
        }

        $favorites = (array) $record->favorites;

        return collect($record->files ?? [])
            ->filter(fn (array $f) => in_array($f['path'], $favorites, true))
            ->pluck('name')
            ->values()
            ->all();
    }

    /** @return list<string> */
    private static function commentLines(?Delivery $record): array
    {
        if (! $record) {
            return [];
        }

        return $record->order->comments()->reorder('id')->get()
            ->map(fn ($c) => sprintf(
                '[%s] %s, %s: %s',
                $c->author === 'client' ? 'Klien' : 'Staf',
                $c->author_name,
                $c->created_at->translatedFormat('d M Y H:i'),
                $c->body,
            ))
            ->all();
    }

    /** Atribut Delivery → state form (daftar berkas jadi path + peta nama). */
    public static function toFormData(array $data): array
    {
        $files = collect($data['files'] ?? []);

        $data['uploads'] = $files->pluck('path')->values()->all();
        $data['upload_names'] = $files->pluck('name', 'path')->all();
        $data['is_published'] = filled($data['published_at'] ?? null);
        unset($data['files']);

        return $data;
    }

    /**
     * State form → atribut Delivery: susun `files` {path,name,size}, hapus berkas yang dilepas
     * dari disk, bersihkan favorit yang berkasnya sudah tidak ada, dan tentukan published_at.
     */
    public static function toModelData(array $data, ?Delivery $record): array
    {
        $disk = Storage::disk('local');
        $existing = collect($record?->files ?? [])->keyBy('path');
        $prefix = 'deliveries/'.(int) ($record?->order_id ?? $data['order_id'] ?? 0).'/';
        $names = (array) ($data['upload_names'] ?? []);

        $files = [];
        foreach (array_unique(array_filter((array) ($data['uploads'] ?? []), 'is_string')) as $path) {
            $known = $existing->get($path);

            // Hanya berkas lama atau berkas yang baru diunggah ke folder order ini.
            if (! $known && (! str_starts_with($path, $prefix) || str_contains($path, '..'))) {
                continue;
            }

            $files[] = [
                'path' => $path,
                'name' => $known['name'] ?? ($names[$path] ?? basename($path)),
                'size' => $known['size'] ?? ($disk->exists($path) ? $disk->size($path) : 0),
            ];
        }

        $keep = array_column($files, 'path');
        foreach ($existing->keys()->diff($keep) as $orphan) {
            $disk->delete($orphan);
        }

        $data['files'] = $files;
        $data['favorites'] = array_values(array_intersect((array) ($record?->favorites ?? []), $keep));
        $data['published_at'] = ($data['is_published'] ?? false) ? ($data['published_at'] ?? now()) : null;

        unset($data['uploads'], $data['upload_names'], $data['is_published']);

        return $data;
    }
}
