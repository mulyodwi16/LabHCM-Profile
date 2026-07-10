<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;

class DirectoryController extends Controller
{
    private const ROLES = ['dosen', 'member', 'alumni'];

    public function index(Request $r)
    {
        $role = $r->string('role')->toString();
        $q = User::with('profile', 'roles');

        if (in_array($role, self::ROLES, true)) {
            $q->role($role);
        } else {
            $q->whereHas('roles', fn ($rq) => $rq->whereIn('name', self::ROLES));
        }

        if ($s = $r->string('q')->toString()) {
            $q->where('name', 'like', "%{$s}%");
        }
        if ($prodi = $r->string('prodi')->toString()) {
            $q->whereHas('profile', fn ($p) => $p->where('prodi', $prodi));
        }
        if ($ang = $r->integer('angkatan')) {
            $q->whereHas('profile', fn ($p) => $p->where('angkatan', $ang));
        }

        $sort = $r->input('sort', 'name');
        match ($sort) {
            'batch_desc' => $q->join('profiles', 'profiles.user_id', 'users.id')->orderByDesc('profiles.angkatan')->select('users.*'),
            'batch_asc'  => $q->join('profiles', 'profiles.user_id', 'users.id')->orderBy('profiles.angkatan')->select('users.*'),
            default      => $q->orderBy('name'),
        };

        $users = $q->paginate(24)->withQueryString();

        if ($r->boolean('partial')) {
            return view('public.partials.people-list', compact('users'));
        }

        return view('public.directory', [
            'users'     => $users,
            'role'      => $role,
            'prodis'    => Profile::whereNotNull('prodi')->distinct()->orderBy('prodi')->pluck('prodi'),
            'angkatans' => Profile::whereNotNull('angkatan')->distinct()->orderByDesc('angkatan')->pluck('angkatan'),
        ]);
    }
}
