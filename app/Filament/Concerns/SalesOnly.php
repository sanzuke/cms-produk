<?php

namespace App\Filament\Concerns;

use App\Enums\Role;

/** Resource/halaman modul penjualan: hanya owner dan sales. */
trait SalesOnly
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(Role::Owner, Role::Sales) ?? false;
    }
}
