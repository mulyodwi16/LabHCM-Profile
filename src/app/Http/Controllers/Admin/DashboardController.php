<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use App\Models\Project;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard', [
            'stats' => [
                'dosen'    => User::role('dosen')->count(),
                'members'  => User::role('member')->count(),
                'alumni'   => User::role('alumni')->count(),
                'projects' => Project::count(),
                'gallery'  => GalleryItem::count(),
            ],
        ]);
    }
}
