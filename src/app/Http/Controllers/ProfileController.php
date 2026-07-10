<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit(Request $r)
    {
        $user = $r->user();
        $user->profile()->firstOrCreate([]);
        return view('profile.edit', ['user' => $user->fresh('profile.documents')]);
    }

    public function update(Request $r)
    {
        $user = $r->user();

        $data = $r->validate([
            'name'          => ['required', 'string', 'max:120'],
            'email'         => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'nrp'           => ['nullable', 'string', 'max:32'],
            'nip'           => ['nullable', 'string', 'max:32'],
            'prodi'         => ['nullable', 'string', 'max:120'],
            'angkatan'      => ['nullable', 'integer', 'between:2000,2100'],
            'phone'         => ['nullable', 'string', 'max:32'],
            'bio'           => ['nullable', 'string', 'max:2000'],
            'skills'        => ['nullable', 'string', 'max:500'],
            'youtube_url'   => ['nullable', 'url', 'max:255'],
            'github_url'    => ['nullable', 'url', 'max:255'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'photo'         => ['nullable', 'image', 'max:4096'],
        ]);

        $user->update(['name' => $data['name'], 'email' => $data['email']]);

        $profile = $user->profile()->firstOrCreate([]);
        $payload = collect($data)->except(['name', 'email', 'skills', 'photo'])->toArray();
        $payload['skills'] = $data['skills'] ? array_values(array_filter(array_map('trim', explode(',', $data['skills'])))) : null;

        if ($r->hasFile('photo')) {
            if ($profile->photo_path) Storage::disk('public')->delete($profile->photo_path);
            $payload['photo_path'] = $r->file('photo')->store('avatars', 'public');
        }

        $profile->update($payload);

        return back()->with('status', 'Profile updated.');
    }

    public function uploadDocument(Request $r)
    {
        $data = $r->validate([
            'label' => ['required', 'string', 'max:120'],
            'file'  => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $profile = $r->user()->profile()->firstOrCreate([]);
        Document::create([
            'profile_id' => $profile->id,
            'label'      => $data['label'],
            'file_path'  => $r->file('file')->store('documents', 'public'),
        ]);

        return back()->with('status', 'Document uploaded.');
    }

    public function destroyDocument(Request $r, Document $document)
    {
        abort_unless($document->profile->user_id === $r->user()->id, 403);
        Storage::disk('public')->delete($document->file_path);
        $document->delete();
        return back()->with('status', 'Document removed.');
    }
}
