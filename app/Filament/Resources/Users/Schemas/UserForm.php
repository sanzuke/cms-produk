<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('Nama')->required()->maxLength(255),
                        TextInput::make('email')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
                        Select::make('role')
                            ->label('Role')
                            ->options(Role::options())
                            ->required()
                            ->native(false)
                            ->disabled(fn (?\App\Models\User $record) => $record && ($record->is(auth()->user()) || $record->isLastOwner()))
                            ->dehydrated(fn (?\App\Models\User $record) => ! ($record && ($record->is(auth()->user()) || $record->isLastOwner())))
                            ->helperText('Owner: semua akses. Editor: konten situs. Sales: inquiry & penjualan.'),
                        TextInput::make('password')
                            ->label('Kata sandi')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->helperText(fn (string $operation) => $operation === 'edit' ? 'Kosongkan bila tidak diganti.' : null),
                    ]),
            ]);
    }
}
