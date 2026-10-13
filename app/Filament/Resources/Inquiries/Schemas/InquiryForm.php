<?php

namespace App\Filament\Resources\Inquiries\Schemas;

use App\Models\Inquiry;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Pipeline')
                    ->columns(2)
                    ->schema([
                        Select::make('status')->options(Inquiry::STATUSES)->default('new')->required()->native(false),
                        Select::make('assigned_to')->label('Ditangani oleh')->relationship('assignee', 'name')->native(false)->searchable()->preload(),
                        Textarea::make('internal_notes')->label('Catatan internal')->rows(3)->columnSpanFull(),
                    ]),

                Section::make('Kontak')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('Nama')->required()->maxLength(255),
                        TextInput::make('company')->label('Perusahaan')->maxLength(255),
                        TextInput::make('position')->label('Jabatan')->maxLength(255),
                        TextInput::make('email')->email()->required()->maxLength(255),
                        TextInput::make('phone')->label('WhatsApp / Telepon')->tel()->maxLength(30),
                    ]),

                Section::make('Kebutuhan')
                    ->columns(2)
                    ->schema([
                        Select::make('service_id')->label('Layanan')->relationship('service', 'title')->native(false)->searchable()->preload(),
                        TextInput::make('site')->label('Lokasi / site')->maxLength(255),
                        Select::make('commodity')->label('Komoditas')->native(false)->options([
                            'batubara' => 'Batubara', 'nikel' => 'Nikel', 'emas' => 'Emas', 'lainnya' => 'Lainnya',
                        ]),
                        DatePicker::make('needed_date')->label('Tanggal dibutuhkan'),
                        TextInput::make('duration')->label('Perkiraan durasi')->maxLength(60),
                        Textarea::make('brief')->label('Kebutuhan khusus')->rows(4)->columnSpanFull(),
                        FileUpload::make('attachments')
                            ->label('Lampiran brief')
                            ->multiple()
                            ->disk('local')
                            ->directory('inquiries')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(10240)
                            ->downloadable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
