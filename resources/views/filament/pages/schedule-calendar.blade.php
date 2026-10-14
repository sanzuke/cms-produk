<x-filament-panels::page>
    <style>
        .sc-grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .sc-grid th { padding: .5rem; font-size: .75rem; font-weight: 600; text-align: left; color: var(--gray-500); }
        .sc-grid td { height: 6.5rem; padding: .25rem; vertical-align: top; border: 1px solid color-mix(in srgb, var(--gray-400) 35%, transparent); }
        .sc-out { background: color-mix(in srgb, var(--gray-400) 10%, transparent); }
        .sc-day { font-size: .75rem; font-weight: 600; margin-bottom: .125rem; }
        .sc-today .sc-day { display: inline-block; padding: 0 .375rem; border-radius: 9999px; background: var(--primary-600); color: #fff; }
        .sc-chip { display: block; margin-bottom: .125rem; padding: .125rem .375rem; border-radius: .375rem; font-size: .7rem; line-height: 1.2; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; text-decoration: none; color: #fff; }
        .sc-warning { background: var(--warning-600); }
        .sc-info { background: var(--info-600, var(--primary-500)); }
        .sc-primary { background: var(--primary-600); }
        .sc-gray { background: var(--gray-600); }
        .sc-success { background: var(--success-600); }
        .sc-danger { background: var(--danger-600); }
        .sc-legend { display: flex; flex-wrap: wrap; gap: .75rem; font-size: .75rem; }
        .sc-legend span::before { content: ''; display: inline-block; width: .6rem; height: .6rem; margin-right: .25rem; border-radius: 9999px; background: var(--dot); }
    </style>

    <x-filament::section>
        <div class="flex flex-wrap items-center justify-between gap-3" style="margin-bottom: 1rem;">
            <div class="flex items-center gap-2">
                <x-filament::button color="gray" size="sm" wire:click="previousMonth" icon="heroicon-m-chevron-left">Sebelumnya</x-filament::button>
                <x-filament::button color="gray" size="sm" wire:click="today">Hari ini</x-filament::button>
                <x-filament::button color="gray" size="sm" wire:click="nextMonth" icon="heroicon-m-chevron-right" icon-position="after">Berikutnya</x-filament::button>
            </div>
            <h2 class="text-lg font-semibold" data-testid="calendar-month">{{ $monthLabel }}</h2>
        </div>

        <table class="sc-grid">
            <thead>
                <tr>@foreach ($weekdays as $d)<th>{{ $d }}</th>@endforeach</tr>
            </thead>
            <tbody>
                @foreach ($weeks as $week)
                    <tr>
                        @foreach ($week as $cell)
                            <td class="{{ $cell['inMonth'] ? '' : 'sc-out' }} {{ $cell['isToday'] ? 'sc-today' : '' }}">
                                <div class="sc-day">{{ $cell['date']->day }}</div>
                                @foreach ($cell['chips'] as $chip)
                                    <a href="{{ $chip['url'] }}" class="sc-chip sc-{{ $chip['color'] }}" title="{{ $chip['number'] }} · {{ $chip['title'] }} ({{ $chip['statusLabel'] }})">{{ $chip['title'] }}</a>
                                @endforeach
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="sc-legend" style="margin-top: 1rem;">
            @foreach ($legend as $item)
                <span style="--dot: var(--{{ $item['color'] }}-600);">{{ $item['label'] }}</span>
            @endforeach
        </div>
    </x-filament::section>

    <x-filament::section heading="Minggu ini">
        @forelse ($thisWeek as $item)
            <div class="flex items-center justify-between gap-3 py-1">
                <a href="{{ $item['url'] }}" class="text-sm font-medium hover:underline">{{ $item['title'] }}
                    <span class="text-xs text-gray-500">· {{ $item['client'] }}</span></a>
                <span class="text-xs text-gray-500">{{ $item['range'] }} · {{ $item['statusLabel'] }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-500">Tidak ada jadwal minggu ini.</p>
        @endforelse
    </x-filament::section>
</x-filament-panels::page>
