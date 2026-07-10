<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function index()
    {
        return view('admin.projects.index', [
            'projects' => Project::with('user', 'images')->latest()->paginate(20),
        ]);
    }

    public function create() { return view('admin.projects.form', ['project' => new Project()]); }

    public function store(Request $r)
    {
        $data = $this->validated($r);
        $project = $r->user()->projects()->create($data);
        $this->syncImages($r, $project);
        return redirect()->route('admin.projects.index')->with('status', 'Project created.');
    }

    public function edit(Project $project) { return view('admin.projects.form', compact('project')); }

    public function update(Request $r, Project $project)
    {
        $project->update($this->validated($r));
        $this->syncImages($r, $project);
        return redirect()->route('admin.projects.index')->with('status', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        foreach ($project->images as $img) Storage::disk('public')->delete($img->path);
        $project->delete();
        return back()->with('status', 'Project deleted.');
    }

    private function validated(Request $r): array
    {
        return $r->validate([
            'title'       => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'github_url'  => ['nullable', 'url', 'max:255'],
            'published'   => ['nullable', 'boolean'],
        ]);
    }

    private function syncImages(Request $r, Project $project): void
    {
        if (!$r->hasFile('images')) return;
        foreach ($r->file('images') as $i => $file) {
            $project->images()->create([
                'path'       => $file->store('projects', 'public'),
                'sort_order' => $i,
            ]);
        }
    }
}
