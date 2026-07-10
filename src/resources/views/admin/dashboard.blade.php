@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="grid md:grid-cols-5 gap-4">
    @foreach ([
        ['Dosen',    $stats['dosen'],    'amber'],
        ['Members',  $stats['members'],  'blue'],
        ['Alumni',   $stats['alumni'],   'emerald'],
        ['Projects', $stats['projects'], 'indigo'],
        ['Gallery',  $stats['gallery'],  'rose'],
    ] as [$label, $val, $c])
        <div class="glass p-6 rounded-2xl border-t-4 border-{{ $c }}-500 hover:-translate-y-0.5 transition">
            <div class="text-sm text-slate-500">{{ $label }}</div>
            <div class="text-3xl font-bold text-slate-900">{{ $val }}</div>
        </div>
    @endforeach
</div>
@endsection
