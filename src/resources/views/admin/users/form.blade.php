@extends('layouts.admin')
@php
    $labels = ['dosen' => 'Dosen', 'member' => 'Mahasiswa', 'alumni' => 'Alumni'];
    $isDosen = $role === 'dosen';
@endphp
@section('title', ($user->exists ? 'Edit ' : 'Create ') . $labels[$role])

@section('content')
<form method="POST" action="{{ $user->exists ? route('admin.users.update', ['role' => $role, 'user' => $user]) : route('admin.users.store', $role) }}"
      enctype="multipart/form-data" class="grid md:grid-cols-2 gap-6 max-w-5xl">
    @csrf
    @if($user->exists) @method('PATCH') @endif

    {{-- Account --}}
    <div class="glass p-6 rounded-2xl space-y-4">
        <h2 class="font-semibold text-slate-900">Account</h2>

        <div>
            <label class="text-sm font-medium">Name</label>
            <input name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-lg glass-input">
            @error('name')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="text-sm font-medium">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-lg glass-input">
            @error('email')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="text-sm font-medium">Password {{ $user->exists ? '(leave blank to keep)' : '' }}</label>
            <input type="password" name="password" class="w-full rounded-lg glass-input" autocomplete="new-password">
            @error('password')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>

        @if($user->exists)
            <div>
                <label class="text-sm font-medium">Switch role</label>
                <select name="switch_role" class="w-full rounded-lg glass-input">
                    <option value="">(keep {{ $labels[$role] }})</option>
                    @foreach ($labels as $key => $label)
                        @if($key !== $role)
                            <option value="{{ $key }}">Move to {{ $label }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    {{-- Profile --}}
    <div class="glass p-6 rounded-2xl space-y-4">
        <h2 class="font-semibold text-slate-900">Profile</h2>

        <div class="flex items-center gap-4">
            <img src="{{ $user->profile?->photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name ?: '?') . '&background=1e3a8a&color=fff' }}"
                 class="w-20 h-20 rounded-full object-cover border" alt="">
            <input type="file" name="photo" accept="image/*" class="text-sm">
        </div>

        @if($isDosen)
            <div>
                <label class="text-sm font-medium">NIP (Nomor Induk Pegawai)</label>
                <input name="nip" value="{{ old('nip', $user->profile?->nip) }}" class="w-full rounded-lg glass-input">
            </div>
        @else
            <div>
                <label class="text-sm font-medium">NRP / NIM</label>
                <input name="nrp" value="{{ old('nrp', $user->profile?->nrp) }}" class="w-full rounded-lg glass-input">
            </div>
        @endif

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-sm font-medium">Study Program (Prodi)</label>
                <input name="prodi" value="{{ old('prodi', $user->profile?->prodi) }}" class="w-full rounded-lg glass-input">
            </div>
            <div>
                <label class="text-sm font-medium">{{ $isDosen ? 'Tahun Bergabung' : 'Angkatan' }}</label>
                <input type="number" name="angkatan" value="{{ old('angkatan', $user->profile?->angkatan) }}" class="w-full rounded-lg glass-input">
            </div>
        </div>

        <div>
            <label class="text-sm font-medium">Phone</label>
            <input name="phone" value="{{ old('phone', $user->profile?->phone) }}" class="w-full rounded-lg glass-input">
        </div>
        <div>
            <label class="text-sm font-medium">Bio</label>
            <textarea name="bio" rows="3" class="w-full rounded-lg glass-input">{{ old('bio', $user->profile?->bio) }}</textarea>
        </div>
        <div>
            <label class="text-sm font-medium">Skills (comma-separated)</label>
            <input name="skills" value="{{ old('skills', implode(', ', (array)($user->profile?->skills ?? []))) }}" class="w-full rounded-lg glass-input">
        </div>

        @foreach ([['youtube_url', 'YouTube URL'], ['github_url', 'GitHub URL'], ['portfolio_url', 'Portfolio / Drive URL']] as [$f, $lbl])
            <div>
                <label class="text-sm font-medium">{{ $lbl }}</label>
                <input type="url" name="{{ $f }}" value="{{ old($f, $user->profile?->{$f}) }}" class="w-full rounded-lg glass-input">
            </div>
        @endforeach
    </div>

    <div class="md:col-span-2 flex gap-2">
        <button class="px-5 py-2.5 rounded-lg bg-blue-700 text-white font-semibold">Save</button>
        <a href="{{ route('admin.users.index', $role) }}" class="px-5 py-2.5 rounded-lg bg-slate-200 text-slate-800">Cancel</a>
    </div>
</form>
@endsection
