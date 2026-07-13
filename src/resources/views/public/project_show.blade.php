@extends('layouts.public')
@section('title', $project->title)

@section('content')
<article class="max-w-4xl mx-auto px-4 py-12">
    <h1 class="text-3xl md:text-4xl font-bold text-slate-900">{{ $project->title }}</h1>
    <p class="text-sm text-slate-500 mt-1">By {{ $project->user->name }} · {{ $project->created_at->format('M Y') }}</p>

    @if($project->images->count())
        <div class="grid md:grid-cols-2 gap-3 mt-6">
                @foreach ($project->images as $img)
                    <img src="{{ $img->url }}" alt="{{ $project->title }}" class="rounded-xl w-full"
                         onerror="this.onerror=null;this.src='{{ asset('images/HCMBlue.svg') }}';">
            @endforeach
        </div>
    @endif

    <div class="prose mt-8 max-w-none text-slate-700 whitespace-pre-line">{{ $project->description }}</div>

    <div class="mt-8 flex gap-3 flex-wrap">
        @if($project->youtube_url)
            <a href="{{ $project->youtube_url }}" target="_blank" rel="noopener" class="px-4 py-2 bg-red-600 text-white rounded-lg">YouTube Demo</a>
        @endif
        @if($project->github_url)
            <a href="{{ $project->github_url }}" target="_blank" rel="noopener" class="px-4 py-2 bg-slate-900 text-white rounded-lg">GitHub</a>
        @endif
    </div>
</article>
@endsection
