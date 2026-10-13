@php
    $label = \App\Filament\Resources\Projects\Schemas\ProjectForm::CATEGORIES[$project->category] ?? ucfirst($project->category);
    $short = explode(' / ', $label)[0];
    $meta = array_filter([
        \App\Filament\Resources\Projects\Schemas\ProjectForm::COMMODITIES[$project->commodity] ?? null,
        $showArea ?? false ? (\App\Filament\Resources\Projects\Schemas\ProjectForm::WORK_AREAS[$project->work_area] ?? null) : null,
        $project->year,
        $showClient ?? false ? $project->publicClientName() : null,
    ]);
@endphp
<a class="pf-card" href="{{ route('projects.show', $project->slug) }}">
  <div class="pf-card__media">
    <span class="badge badge--accent pf-card__tag">{{ $short }}</span>
    @if ($project->cover)
      <img src="{{ Storage::disk('public')->url($project->cover) }}" alt="{{ $project->title }}" loading="lazy">
    @else
      <div class="ph ph--4x5" data-label="{{ $short }} 4x5 — {{ $project->title }}"></div>
    @endif
  </div>
  <div class="pf-card__body">
    <h3>{{ $project->title }}</h3>
    <p class="pf-card__meta">{{ implode(' · ', $meta) }}</p>
  </div>
</a>
