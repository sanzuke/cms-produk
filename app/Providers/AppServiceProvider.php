<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->applySiteSettings();
    }

    /**
     * Nilai dari tabel settings menimpa default config/site.php.
     * Tabel belum ada (mis. sebelum migrate pertama) → pakai default.
     */
    private function applySiteSettings(): void
    {
        try {
            $overrides = Setting::query()->pluck('value', 'key')->all();
        } catch (QueryException) {
            return;
        }

        config(['site' => array_replace(config('site'), $overrides)]);
    }
}
