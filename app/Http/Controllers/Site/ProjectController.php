<?php

namespace App\Http\Controllers\Site;

use App\Filament\Resources\Projects\Schemas\ProjectForm;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $whitelists = [
            'jenis' => ['category', array_keys(ProjectForm::CATEGORIES)],
            'komoditas' => ['commodity', array_keys(ProjectForm::COMMODITIES)],
            'area' => ['work_area', array_keys(ProjectForm::WORK_AREAS)],
        ];

        $years = Project::published()->reorder()->whereNotNull('year')
            ->distinct()->orderByDesc('year')->pluck('year')->map(fn ($y) => (string) $y)->all();
        $whitelists['tahun'] = ['year', $years];

        $query = Project::published();
        $active = [];
        foreach ($whitelists as $param => [$column, $allowed]) {
            $value = $request->query($param);
            if (is_string($value) && in_array($value, $allowed, true)) {
                $active[$param] = $value;
                $query->where($column, $value);
            }
        }

        return view('site.projects.index', [
            'projects' => $query->paginate(12)->withQueryString(),
            'active' => $active,
            'years' => $years,
        ]);
    }

    public function show(string $slug)
    {
        $project = Project::published()->where('slug', $slug)->firstOrFail();

        $related = Project::published()
            ->where('category', $project->category)
            ->whereKeyNot($project->getKey())
            ->limit(3)->get();

        return view('site.projects.show', compact('project', 'related'));
    }
}
