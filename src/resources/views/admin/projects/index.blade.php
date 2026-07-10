@extends('layouts.admin')
@section('title', 'Projects')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-lg font-semibold">Projects ({{ $projects->total() }})</h2>
    <a href="{{ route('admin.projects.create') }}" class="px-4 py-2 bg-blue-700 text-white rounded-lg">+ New Project</a>
</div>

<div class="glass rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-white/40 backdrop-blur text-left">
            <tr><th class="p-3">Title</th><th class="p-3">Author</th><th class="p-3">Published</th><th class="p-3"></th></tr>
        </thead>
        <tbody class="divide-y">
            @foreach ($projects as $p)
                <tr>
                    <td class="p-3 font-medium">{{ $p->title }}</td>
                    <td class="p-3 text-slate-600">{{ $p->user->name }}</td>
                    <td class="p-3">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $p->published ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $p->published ? 'Live' : 'Draft' }}
                        </span>
                    </td>
                    <td class="p-3 text-right space-x-2">
                        <a href="{{ route('admin.projects.edit', $p) }}" class="text-blue-700">Edit</a>
                        <form method="POST" action="{{ route('admin.projects.destroy', $p) }}" class="inline"
                              onsubmit="return confirm('Delete this project?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $projects->links() }}</div>
@endsection
