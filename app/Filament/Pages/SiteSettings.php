<?php

namespace App\Filament\Pages;

use App\Enums\Role;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Identitas, kontak, dan stat bar situs publik (PLAN §6.15).
 * Disimpan di tabel settings dan menimpa default config/site.php.
 *
 * @property-read Schema $form
 */
class SiteSettings extends Page
{
    /** Kunci yang boleh disimpan; urutan = urutan di form. */
    public const KEYS = [
        'name', 'tagline', 'description',
        'email', 'notification_emails', 'whatsapp', 'phone_display', 'hours', 'service_area',
        'stats',
    ];

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 80;

    protected static ?string $navigationLabel = 'Pengaturan Situs';

    protected static ?string $title = 'Pengaturan Situs';

    protected static ?string $slug = 'pengaturan';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(Role::Owner) ?? false;
    }

    public function mount(): void
    {
        $this->form->fill(collect(self::KEYS)->mapWithKeys(fn (string $key) => [$key => config("site.$key")])->all());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Identitas')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('Nama brand')->required()->maxLength(100),
                        TextInput::make('tagline')->label('Tagline')->required()->maxLength(150),
                        Textarea::make('description')
                            ->label('Deskripsi singkat (beranda & meta SEO)')
                            ->required()
                            ->rows(3)
                            ->maxLength(300)
                            ->columnSpanFull(),
                    ]),

                Section::make('Kontak')
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')->label('Email')->email()->required()->maxLength(190),
                        TagsInput::make('notification_emails')
                            ->label('Penerima notifikasi inquiry baru')
                            ->placeholder('Ketik email lalu Enter')
                            ->nestedRecursiveRules(['email'])
                            ->helperText('Kosongkan untuk memakai email kontak di atas.')
                            ->columnSpanFull(),
                        TextInput::make('whatsapp')
                            ->label('Nomor WhatsApp')
                            ->placeholder('0812xxxxxxx')
                            ->helperText('Tombol WhatsApp hanya tampil bila diisi. Awalan 0 otomatis menjadi 62.')
                            ->rules(['nullable', 'regex:/^[0-9+\s\-()]{8,20}$/'])
                            ->validationMessages(['regex' => 'Nomor hanya boleh berisi angka, spasi, +, - dan tanda kurung.'])
                            ->dehydrateStateUsing(function (?string $state): string {
                                $digits = preg_replace('/\D+/', '', (string) $state);

                                return str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits;
                            }),
                        TextInput::make('phone_display')->label('Nomor tampil di situs')->placeholder('+62 812-3456-7890')->maxLength(40),
                        TextInput::make('hours')->label('Jam kerja')->maxLength(100),
                        TextInput::make('service_area')->label('Area layanan')->maxLength(200)->columnSpanFull(),
                    ]),

                Section::make('Stat bar beranda')
                    ->description('Isi dengan angka nyata. Jangan dipublikasikan sebelum data klien terverifikasi.')
                    ->schema([
                        Repeater::make('stats')
                            ->hiddenLabel()
                            ->schema([
                                TextInput::make('value')->label('Angka')->required()->maxLength(20),
                                TextInput::make('label')->label('Keterangan')->required()->maxLength(40),
                            ])
                            ->columns(2)
                            ->maxItems(6)
                            ->reorderable()
                            ->addActionLabel('Tambah stat'),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Simpan')->submit('save')->keyBindings(['mod+s']),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach (self::KEYS as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => $state[$key] ?? null]);
        }

        Notification::make()->title('Pengaturan disimpan')->success()->send();
    }
}
