@extends('layouts.admin')
@section('title', 'Gallery')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-lg font-semibold">Gallery ({{ $items->total() }})</h2>
    <a href="{{ route('admin.gallery.create') }}" class="px-4 py-2 bg-blue-700 text-white rounded-lg">+ New Item</a>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    @foreach ($items as $g)
        <div class="glass rounded-xl overflow-hidden">
            <img src="{{ $g->image_url }}" class="aspect-square w-full object-cover" alt="">
            <div class="p-3">
                <div class="font-medium text-sm truncate">{{ $g->title }}</div>
                <div class="flex justify-between mt-2 text-xs">
                    <a href="{{ route('admin.gallery.edit', $g) }}" class="text-blue-700">Edit</a>
                    <form method="POST" action="{{ route('admin.gallery.destroy', $g) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
