<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Konten placeholder hanya untuk lokal/staging; production diisi lewat admin.
        // Akun admin: php artisan make:filament-user
        if (! app()->isProduction()) {
            $this->call(ContentSeeder::class);
        }
    }
}
