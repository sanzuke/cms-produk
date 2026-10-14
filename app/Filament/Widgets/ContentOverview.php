<?php

namespace App\Filament\Widgets;

use App\Enums\Role;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Testimonial;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Ringkasan konten di dasbor untuk owner dan editor. */
class ContentOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 20;

    protected ?string $heading = 'Konten';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(Role::Owner, Role::Editor) ?? false;
    }

    protected function getStats(): array
    {
        $scheduled = Post::where('status', 'published')->where('publish_at', '>', now())->count();

        return [
            Stat::make('Artikel draft', Post::where('status', 'draft')->count())
                ->description($scheduled.' terjadwal terbit')
                ->color('warning'),
            Stat::make('Layanan terbit', Service::where('status', 'published')->count())
                ->description(Service::where('status', 'draft')->count().' draft'),
            Stat::make('Portofolio terbit', Project::where('status', 'published')->count())
                ->description(Project::where('status', 'draft')->count().' draft'),
            Stat::make('Testimoni belum berizin', Testimonial::where('allow_public', false)->count())
                ->description('Tidak tampil di situs')
                ->color('gray'),
        ];
    }
}
