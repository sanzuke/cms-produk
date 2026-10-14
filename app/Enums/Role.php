<?php

namespace App\Enums;

enum Role: string
{
    case Owner = 'owner';
    case Editor = 'editor';
    case Sales = 'sales';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Editor => 'Editor (konten)',
            self::Sales => 'Sales / Admin order',
        };
    }

    /** @return array<string, string> value => label, untuk Select Filament. */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $r) => [$r->value => $r->label()])->all();
    }
}
