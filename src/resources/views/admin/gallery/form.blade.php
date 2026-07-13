@extends('layouts.admin')
@section('title', ($item->exists ? 'Edit' : 'New') . ' Gallery Item')

@section('content')
<form method="POST" action="{{ $item->exists ? route('admin.gallery.update', $item) : route('admin.gallery.store') }}"
      enctype="multipart/form-data" class="glass p-6 rounded-2xl max-w-lg space-y-4">
    @csrf
    @if($item->exists) @method('PATCH') @endif

    <div>
        <label class="text-sm font-medium">Title</label>
        <input name="title" value="{{ old('title', $item->title) }}" required class="w-full rounded-lg glass-input">
    </div>
    <div>
        <label class="text-sm font-medium">Caption</label>
        <textarea name="caption" rows="3" class="w-full rounded-lg glass-input">{{ old('caption', $item->caption) }}</textarea>
    </div>
    <div>
        <label class="text-sm font-medium">Date Taken</label>
        <input type="date" name="taken_at" value="{{ old('taken_at', optional($item->taken_at)->format('Y-m-d')) }}" class="rounded-lg border-slate-300">
    </div>
    <div>
        <label class="text-sm font-medium">Image {{ $item->exists ? '(leave blank to keep)' : '' }}</label>
        <input type="file" name="image" accept=".jpg,.jpeg,.png,image/jpeg,image/png" {{ $item->exists ? '' : 'required' }} class="block w-full text-sm">
        <x-upload-hint type="image" />
        @error('image')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        @if($item->exists)<img src="{{ $item->image_url }}" class="mt-2 w-32 h-32 object-cover rounded" alt="">@endif
    </div>

    <button class="px-5 py-2.5 rounded-lg bg-blue-700 text-white font-semibold">Save</button>
    <a href="{{ route('admin.gallery.index') }}" class="ml-2 text-slate-600">Cancel</a>
</form>
@endsection
