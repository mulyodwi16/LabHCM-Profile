@extends('layouts.admin')
@section('title', ($project->exists ? 'Edit' : 'New') . ' Project')

@section('content')
<form method="POST" action="{{ $project->exists ? route('admin.projects.update', $project) : route('admin.projects.store') }}"
      enctype="multipart/form-data" class="glass p-6 rounded-2xl space-y-4 max-w-2xl">
    @csrf
    @if($project->exists) @method('PATCH') @endif

    <div>
        <label class="text-sm font-medium">Title</label>
        <input name="title" value="{{ old('title', $project->title) }}" required class="w-full rounded-lg glass-input">
        @error('title')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="text-sm font-medium">Description</label>
        <textarea name="description" rows="6" required class="w-full rounded-lg glass-input">{{ old('description', $project->description) }}</textarea>
        @error('description')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="text-sm font-medium">YouTube Demo URL</label>
        <input name="youtube_url" value="{{ old('youtube_url', $project->youtube_url) }}" class="w-full rounded-lg glass-input" placeholder="https://youtube.com/...">
        @error('youtube_url')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="text-sm font-medium">GitHub URL</label>
        <input name="github_url" value="{{ old('github_url', $project->github_url) }}" class="w-full rounded-lg glass-input" placeholder="https://github.com/...">
        @error('github_url')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="text-sm font-medium">Add Images <span class="text-red-600">*</span></label>
        <input type="file" name="images[]" multiple accept=".jpg,.jpeg,.png,image/jpeg,image/png" {{ $project->exists ? '' : 'required' }} class="block w-full text-sm">
        <x-upload-hint type="image" />
        <p class="text-xs text-slate-500 mt-1">Wajib minimal 1 gambar agar tampil di halaman utama.</p>
        @error('images')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        @error('images.*')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        @if($project->exists && $project->images->count())
            <div class="grid grid-cols-4 gap-2 mt-2">
                @foreach ($project->images as $img)
                    <img src="{{ $img->url }}" class="w-full h-24 object-cover rounded" alt="{{ $project->title }}"
                         onerror="this.onerror=null;this.src='{{ asset('images/HCMBlue.svg') }}';">
                @endforeach
            </div>
        @endif
    </div>
    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="published" value="1" @checked(old('published', $project->published ?? true))>
        <span class="text-sm">Published (visible on public site)</span>
    </label>

    <div>
        <button class="px-5 py-2.5 rounded-lg bg-blue-700 text-white font-semibold">Save</button>
        <a href="{{ route('admin.projects.index') }}" class="ml-2 text-slate-600">Cancel</a>
    </div>
</form>
@endsection
