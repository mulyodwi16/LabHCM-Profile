<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin · @yield('title', 'HCM')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-shell text-slate-800">
<div class="flex min-h-screen">
    <aside class="w-64 hidden md:flex flex-col text-slate-200 relative">
        <div class="absolute inset-0 glass-dark"></div>
        <div class="relative flex-1 flex flex-col">
            <div class="p-4 border-b border-white/10 flex items-center gap-2">
                <img src="{{ asset('images/HCMWhite.svg') }}" alt="HCM" class="h-8">
                <span class="font-semibold">Admin</span>
            </div>
            <nav class="flex-1 p-3 space-y-1 text-sm">
                @foreach ([
                    ['admin.dashboard',   'Dashboard',  []],
                    ['admin.users.index', 'Dosen',      ['role' => 'dosen']],
                    ['admin.users.index', 'Members',    ['role' => 'member']],
                    ['admin.users.index', 'Alumni',     ['role' => 'alumni']],
                    ['admin.projects.index','Projects', []],
                    ['admin.gallery.index','Gallery',   []],
                ] as [$r, $label, $params])
                    <a href="{{ route($r, $params) }}"
                       class="block px-3 py-2 rounded-lg transition hover:bg-white/10 {{ request()->routeIs($r) && collect($params)->every(fn($v,$k) => request()->route($k) === $v) ? 'bg-blue-600/70 text-white shadow-md shadow-blue-950/40' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
            <div class="p-3 border-t border-white/10 text-xs">
                <div class="text-slate-400">{{ auth()->user()->email }}</div>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="mt-1 text-red-300 hover:text-red-200 transition">Logout</button>
                </form>
                <a href="{{ route('home') }}" class="block mt-1 text-slate-400 hover:text-white transition">&larr; Public site</a>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col">
        <header class="glass-nav px-6 py-4 flex items-center justify-between">
            <h1 class="font-semibold text-slate-900">@yield('title', 'Admin')</h1>
        </header>
        <main class="p-6">
            @if ($errors->any())
                <div class="mb-4 p-4 glass rounded-xl border border-red-200/70 text-red-800">
                    <p class="font-semibold mb-2">Periksa input berikut:</p>
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(session('status'))
                <div class="mb-4 p-3 glass rounded-xl border border-emerald-200/60 text-emerald-800">{{ session('status') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
