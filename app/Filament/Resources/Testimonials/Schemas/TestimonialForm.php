<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('author')->label('Nama')->required()->maxLength(255),
                        TextInput::make('role')->label('Jabatan')->maxLength(255),
                        TextInput::make('company')->label('Perusahaan')->maxLength(255),
                        TextInput::make('sort_order')->label('Urutan')->numeric()->default(0)->required(),
                        Textarea::make('quote')->label('Kutipan')->required()->rows(4)->columnSpanFull(),
                        Toggle::make('allow_public')
                            ->label('Sudah ada izin tampil di situs publik')
                            ->helperText('Nyalakan hanya setelah ada izin tertulis dari yang bersangkutan.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
