<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    private const ROLES = ['dosen', 'member', 'alumni'];

    public function index(Request $r, string $role)
    {
        abort_unless(in_array($role, self::ROLES, true), 404);
        $users = User::role($role)->with('profile')->orderBy('name')->paginate(20);
        return view('admin.users.index', compact('users', 'role'));
    }

    public function create(string $role)
    {
        abort_unless(in_array($role, self::ROLES, true), 404);
        return view('admin.users.form', ['user' => new User(), 'role' => $role]);
    }

    public function store(Request $r, string $role)
    {
        abort_unless(in_array($role, self::ROLES, true), 404);

        $account = $r->validate([
            'name'     => ['required', 'string', 'max:120'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);
        $account['password'] = Hash::make($account['password']);

        $user = User::create($account);
        $user->assignRole($role);
        $profile = $user->profile()->create([]);

        $this->saveProfile($r, $profile);

        return redirect()->route('admin.users.edit', ['role' => $role, 'user' => $user])
            ->with('status', 'User created.');
    }

    public function edit(string $role, User $user)
    {
        abort_unless(in_array($role, self::ROLES, true), 404);
        $user->profile()->firstOrCreate([]);
        return view('admin.users.form', ['user' => $user->fresh('profile'), 'role' => $role]);
    }

    public function update(Request $r, string $role, User $user)
    {
        abort_unless(in_array($role, self::ROLES, true), 404);

        $account = $r->validate([
            'name'     => ['required', 'string', 'max:120'],
            'email'    => ['required', 'email', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
        ]);
        if (!empty($account['password'])) $account['password'] = Hash::make($account['password']);
        else unset($account['password']);
        $user->update($account);

        if ($r->filled('switch_role') && in_array($r->input('switch_role'), self::ROLES, true)) {
            $user->syncRoles([$r->input('switch_role')]);
            $role = $r->input('switch_role');
        }

        $profile = $user->profile()->firstOrCreate([]);
        $this->saveProfile($r, $profile);

        return redirect()->route('admin.users.edit', ['role' => $role, 'user' => $user])
            ->with('status', 'Profile saved.');
    }

    public function destroy(string $role, User $user)
    {
        abort_unless(in_array($role, self::ROLES, true), 404);
        if ($user->profile?->photo_path) Storage::disk('public')->delete($user->profile->photo_path);
        $user->delete();
        return redirect()->route('admin.users.index', $role)->with('status', 'User deleted.');
    }

    private function saveProfile(Request $r, \App\Models\Profile $profile): void
    {
        $data = $r->validate([
            'nrp'           => ['nullable', 'string', 'max:32'],
            'nip'           => ['nullable', 'string', 'max:32'],
            'prodi'         => ['nullable', 'string', 'max:120'],
            'angkatan'      => ['nullable', 'integer', 'between:1970,2100'],
            'phone'         => ['nullable', 'string', 'max:32'],
            'bio'           => ['nullable', 'string', 'max:2000'],
            'skills'        => ['nullable', 'string', 'max:500'],
            'youtube_url'   => ['nullable', 'url', 'max:255'],
            'github_url'    => ['nullable', 'url', 'max:255'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'photo'         => ['nullable', 'image', 'max:4096'],
        ]);

        $payload = collect($data)->except(['skills', 'photo'])->toArray();
        $payload['skills'] = !empty($data['skills'])
            ? array_values(array_filter(array_map('trim', explode(',', $data['skills']))))
            : null;

        if ($r->hasFile('photo')) {
            if ($profile->photo_path) Storage::disk('public')->delete($profile->photo_path);
            $payload['photo_path'] = $r->file('photo')->store('avatars', 'public');
        }

        $profile->update($payload);
    }
}
