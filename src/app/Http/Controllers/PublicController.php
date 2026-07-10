<?php

namespace App\Http\Controllers;

use App\Models\GalleryItem;
use App\Models\Project;
use App\Models\User;

class PublicController extends Controller
{
    public function home()
    {
        $projects = Project::with('images')->where('published', true)->latest()->take(9)->get();
        $gallery  = GalleryItem::latest()->take(12)->get();

        $slides = $projects
            ->filter(fn ($p) => $p->images->isNotEmpty())
            ->map(fn ($p) => [
                'image' => $p->images->first()->url,
                'kind'  => 'Project',
                'title' => $p->title,
                'text'  => \Illuminate\Support\Str::limit($p->description, 140),
                'href'  => route('projects.show', $p),
            ])
            ->concat(
                $gallery->map(fn ($g) => [
                    'image' => $g->image_url,
                    'kind'  => 'Gallery',
                    'title' => $g->title,
                    'text'  => \Illuminate\Support\Str::limit($g->caption, 140),
                    'href'  => '#gallery',
                ])
            )
            ->take(5)
            ->values();

        return view('public.home', [
            'projects' => $projects,
            'gallery'  => $gallery,
            'slides'   => $slides,
            'stats'    => [
                'dosen'    => User::role('dosen')->count(),
                'members'  => User::role('member')->count(),
                'alumni'   => User::role('alumni')->count(),
                'projects' => Project::where('published', true)->count(),
            ],
        ]);
    }
}
