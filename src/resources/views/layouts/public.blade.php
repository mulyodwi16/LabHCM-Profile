<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HCM Laboratory')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="text-slate-800 font-sans antialiased">

@php
    $home = route('home');
    $navItems = [
        ['href' => $home . '#home',     'label' => 'Home',     'section' => 'home'],
        ['href' => $home . '#about',    'label' => 'About',    'section' => 'about'],
        ['href' => $home . '#projects', 'label' => 'Projects', 'section' => 'projects'],
        ['href' => $home . '#gallery',  'label' => 'Gallery',  'section' => 'gallery'],
        ['href' => $home . '#contact',  'label' => 'Contact',  'section' => 'contact'],
        ['href' => route('people'),     'label' => 'People',   'section' => null, 'route' => 'people'],
    ];
@endphp

<header class="sticky top-0 z-40 glass-nav">
    <nav class="max-w-7xl mx-auto flex items-center justify-between px-4 py-3">
        <a href="{{ $home }}#home" class="flex items-center gap-2">
            <img src="{{ asset('images/HCMBlue.svg') }}" alt="HCM" class="h-9 w-auto">
            <span class="font-semibold text-slate-900 hidden sm:inline">HCM Laboratory</span>
        </a>
        <button id="navToggle" class="md:hidden p-2 rounded hover:bg-white/40" aria-label="Menu">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <ul id="navMenu" class="hidden md:flex items-center gap-6 text-sm font-medium">
            @foreach ($navItems as $it)
                <li><a href="{{ $it['href'] }}"
                       class="hover:text-blue-700 transition {{ !empty($it['route']) && request()->routeIs($it['route']) ? 'text-blue-700 font-semibold' : '' }}">
                    {{ $it['label'] }}
                </a></li>
            @endforeach
            @auth
                <li><a href="{{ route('profile.edit') }}" class="text-slate-600 hover:text-blue-700 transition">Profile</a></li>
                @if(auth()->user()->isAdmin())
                    <li><a href="{{ route('admin.dashboard') }}" class="text-blue-700 font-semibold">Admin</a></li>
                @endif
                <li>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button class="text-sm text-slate-600 hover:text-red-600 transition">Logout</button>
                    </form>
                </li>
            @else
                <li><a href="{{ route('login') }}" class="px-4 py-2 rounded-lg bg-blue-700/90 hover:bg-blue-700 text-white backdrop-blur transition shadow-md shadow-blue-900/20">Login</a></li>
            @endauth
        </ul>
    </nav>
    <ul id="navMenuMobile" class="md:hidden hidden border-t border-white/40 px-4 py-3 space-y-2 bg-white/60 backdrop-blur-xl">
        @foreach ($navItems as $it)
            <li><a href="{{ $it['href'] }}" class="block py-1">{{ $it['label'] }}</a></li>
        @endforeach
        @auth
            <li><a href="{{ route('profile.edit') }}" class="block py-1">Profile</a></li>
            @if(auth()->user()->isAdmin())<li><a href="{{ route('admin.dashboard') }}" class="block py-1 text-blue-700 font-semibold">Admin</a></li>@endif
            <li><form method="POST" action="{{ route('logout') }}">@csrf<button class="py-1">Logout</button></form></li>
        @else
            <li><a href="{{ route('login') }}" class="block py-1 text-blue-700">Login</a></li>
        @endauth
    </ul>
</header>

<main>@yield('content')</main>

<footer id="contact" class="scroll-mt-20 mt-16 relative overflow-hidden">
    <div class="absolute inset-0 bg-slate-900/95 backdrop-blur-xl"></div>
    <div class="relative text-slate-300">
        <div class="max-w-7xl mx-auto px-4 py-14 grid md:grid-cols-3 gap-8">
            <div>
                <img src="{{ asset('images/HCMWhite.svg') }}" alt="HCM" class="h-10 mb-3">
                <p class="text-sm text-slate-400">Human Centric Multimedia Laboratory. Advancing research at the intersection of people and technology.</p>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3">Explore</h4>
                <ul class="space-y-1 text-sm">
                    <li><a href="{{ $home }}#about" class="hover:text-white transition">About</a></li>
                    <li><a href="{{ $home }}#projects" class="hover:text-white transition">Projects</a></li>
                    <li><a href="{{ $home }}#gallery" class="hover:text-white transition">Gallery</a></li>
                    <li><a href="{{ route('people') }}" class="hover:text-white transition">People</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3">Contact</h4>
                <p class="text-sm text-slate-400 leading-relaxed">
                    HCM Lab, Gedung Riset<br>
                    Universitas<br>
                    <a href="mailto:hcm@lab.test" class="text-blue-300 hover:text-white transition">hcm@lab.test</a>
                </p>
            </div>
        </div>
        <div class="border-t border-white/10 py-4 text-center text-xs text-slate-500">&copy; {{ date('Y') }} HCM Laboratory.</div>
    </div>
</footer>

<script>
    document.getElementById('navToggle')?.addEventListener('click', () => {
        document.getElementById('navMenuMobile').classList.toggle('hidden');
    });
</script>
</body>
</html>
