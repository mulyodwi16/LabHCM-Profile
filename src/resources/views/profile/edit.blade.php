@extends('layouts.public')
@section('title', 'My Profile')

@section('content')
@php
    $isDosen = $user->hasRole('dosen');
    $fields = [
        ['name','Full Name','text', $user->name],
        ['email','Email','email', $user->email],
    ];
    $fields[] = $isDosen
        ? ['nip', 'NIP (Nomor Induk Pegawai)', 'text', $user->profile?->nip]
        : ['nrp', 'NRP / NIM', 'text', $user->profile?->nrp];
    $fields = array_merge($fields, [
        ['prodi', 'Study Program (Prodi)', 'text', $user->profile?->prodi],
        ['angkatan', $isDosen ? 'Tahun Bergabung' : 'Angkatan', 'number', $user->profile?->angkatan],
        ['phone', 'Phone', 'text', $user->profile?->phone],
        ['youtube_url', 'YouTube Demo URL', 'url', $user->profile?->youtube_url],
        ['github_url', 'GitHub URL', 'url', $user->profile?->github_url],
        ['portfolio_url', 'Portfolio / Drive URL', 'url', $user->profile?->portfolio_url],
    ]);
@endphp
<section class="max-w-4xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold text-slate-900 mb-6">My Profile</h1>

    @if(session('status'))
        <div class="mb-4 p-3 bg-green-100 border border-green-200 text-green-800 rounded">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded">
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="glass rounded-2xl p-6 space-y-4">
        @csrf @method('PATCH')

        <div class="flex items-center gap-4">
            <img src="{{ $user->profile?->photo_url }}" class="w-20 h-20 rounded-full object-cover border" alt="">
            <div>
                <input type="file" name="photo" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="text-sm block">
                <x-upload-hint type="image" />
                @error('photo')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        @foreach ($fields as [$name, $label, $type, $val])
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
                <input type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $val) }}"
                       class="mt-1 w-full rounded-lg glass-input">
                @error($name)<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        @endforeach

        <div>
            <label class="block text-sm font-medium text-slate-700">Bio</label>
            <textarea name="bio" rows="4" class="mt-1 w-full rounded-lg glass-input">{{ old('bio', $user->profile?->bio) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Skills (comma-separated)</label>
            <input name="skills" value="{{ old('skills', implode(', ', (array)($user->profile?->skills ?? []))) }}" class="mt-1 w-full rounded-lg glass-input">
        </div>

        <button class="px-5 py-2.5 rounded-lg bg-blue-700 text-white font-semibold hover:bg-blue-800">Save Changes</button>
    </form>

    <div class="glass rounded-2xl p-6 mt-6">
        <h2 class="text-lg font-semibold mb-3">Supporting Documents (PDF)</h2>
        <form method="POST" action="{{ route('profile.documents.store') }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3 mb-4">
            @csrf
            <div class="flex-1 min-w-40">
                <label class="text-sm text-slate-600">Label</label>
                <input name="label" required class="w-full rounded-lg border-slate-300">
            </div>
            <div>
                <label class="text-sm text-slate-600">File</label>
                <input type="file" name="file" accept=".pdf,application/pdf" required class="block w-full text-sm">
                <x-upload-hint type="pdf" />
                @error('file')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Upload</button>
        </form>

        <ul class="divide-y">
            @forelse ($user->profile?->documents ?? [] as $d)
                <li class="flex items-center justify-between py-2">
                    <a href="{{ \Storage::url($d->file_path) }}" target="_blank" class="text-blue-700 hover:underline">{{ $d->label }}</a>
                    <form method="POST" action="{{ route('profile.documents.destroy', $d) }}">@csrf @method('DELETE')
                        <button class="text-red-600 text-sm">Remove</button>
                    </form>
                </li>
            @empty
                <li class="text-slate-500 py-2">No documents uploaded.</li>
            @endforelse
        </ul>
    </div>
</section>
@endsection
