<?php

namespace App\Http\Controllers;

use App\Models\Project;

class PublicProjectController extends Controller
{
    public function show(Project $project)
    {
        abort_unless($project->published, 404);
        return view('public.project_show', compact('project'));
    }
}
