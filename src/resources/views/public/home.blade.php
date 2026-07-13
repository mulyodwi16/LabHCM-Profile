@extends('layouts.public')
@section('title', 'Home')

@section('content')
{{-- HERO --}}
<section id="home" class="scroll-mt-20 relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-blue-900 via-blue-800 to-slate-900"></div>
    <div class="absolute inset-0 opacity-40" style="background:
        radial-gradient(600px 400px at 20% 30%, rgba(56,189,248,0.35), transparent 60%),
        radial-gradient(500px 400px at 80% 70%, rgba(139,92,246,0.30), transparent 60%);"></div>

    <div class="relative max-w-7xl mx-auto px-4 py-16 md:py-24 grid md:grid-cols-2 gap-10 items-center text-white">
        <div>
            <p class="text-blue-300 font-semibold tracking-wider text-sm uppercase mb-3">Human Centric Multimedia</p>
            <h1 class="text-4xl md:text-5xl font-bold leading-tight mb-5">Advancing knowledge through human-centric research.</h1>
            <p class="text-blue-100 text-lg mb-8 max-w-lg">A hub for discovery and exploration at the intersection of people, media, and intelligent systems.</p>
            <div class="flex gap-3">
                <a href="#projects" class="px-5 py-3 rounded-xl bg-white/90 backdrop-blur text-blue-800 font-semibold hover:bg-white shadow-lg shadow-blue-900/30 transition">Explore Projects</a>
                <a href="#about" class="px-5 py-3 rounded-xl bg-white/10 backdrop-blur border border-white/30 hover:bg-white/20 transition">About Us</a>
            </div>
        </div>

        <div id="heroCarousel" class="relative">
            @if($slides->isEmpty())
                <div class="flex justify-center"><img src="{{ asset('images/HCMWhite.svg') }}" alt="" class="w-72 opacity-90"></div>
            @else
                <div class="hc-track rounded-2xl overflow-hidden shadow-2xl shadow-blue-950/50 bg-slate-800/40 border border-white/10 aspect-video">
                    @foreach($slides as $i => $s)
                        <a href="{{ $s['href'] }}"
                           class="hc-slide relative block {{ $i === 0 ? 'is-active' : '' }}"
                           data-index="{{ $i }}">
                            <img src="{{ $s['image'] }}" alt="{{ $s['title'] }}"
                                 class="absolute inset-0 w-full h-full object-cover"
                                 loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                                 onerror="this.onerror=null;this.src='{{ asset('images/HCMBlue.svg') }}';">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/25 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-5 text-white">
                                <span class="text-xs uppercase tracking-wider text-blue-300 font-semibold">{{ $s['kind'] }}</span>
                                <h3 class="text-xl font-bold mt-1 line-clamp-1">{{ $s['title'] }}</h3>
                                @if($s['text'])<p class="text-sm text-slate-200 line-clamp-2 mt-1">{{ $s['text'] }}</p>@endif
                            </div>
                        </a>
                    @endforeach
                </div>

                @if($slides->count() > 1)
                    <div class="absolute bottom-3 left-0 right-0 z-10 flex justify-center gap-2">
                        @foreach($slides as $i => $s)
                            <button type="button" data-dot="{{ $i }}" class="w-2 h-2 rounded-full bg-white/50 hover:bg-white transition" aria-label="Slide {{ $i + 1 }}"></button>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </div>
</section>

{{-- STATS --}}
<section class="max-w-7xl mx-auto px-4 -mt-12 relative z-10">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 glass rounded-2xl p-6">
        @foreach ([
            ['Dosen', $stats['dosen']],
            ['Active Members', $stats['members']],
            ['Alumni', $stats['alumni']],
            ['Published Projects', $stats['projects']],
        ] as [$label, $val])
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-800">{{ $val }}</div>
                <div class="text-sm text-slate-600">{{ $label }}</div>
            </div>
        @endforeach
    </div>
</section>

{{-- ABOUT --}}
<section id="about" class="scroll-mt-20 max-w-4xl mx-auto px-4 py-20">
    <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-6">About HCM Laboratory</h2>
    <p class="text-lg text-slate-700 mb-4">Human Centric Multimedia (HCM) Laboratory conducts research at the intersection of human interaction, multimedia systems, and intelligent computing.</p>
    <p class="text-slate-700 mb-4">Our members explore computer vision, HCI, audio processing, generative media, and applied AI, with a strong emphasis on human factors.</p>
    <div class="grid md:grid-cols-2 gap-6 mt-8">
        <div class="glass p-6 rounded-2xl">
            <h3 class="font-semibold text-slate-900 mb-2">Mission</h3>
            <p class="text-slate-700">To advance multimedia research grounded in real human needs, and to train the next generation of interdisciplinary researchers.</p>
        </div>
        <div class="glass p-6 rounded-2xl">
            <h3 class="font-semibold text-slate-900 mb-2">Focus Areas</h3>
            <ul class="text-slate-700 list-disc list-inside space-y-1">
                <li>Computer Vision &amp; HCI</li>
                <li>Multimedia signal processing</li>
                <li>Human-centered AI systems</li>
            </ul>
        </div>
    </div>
</section>

{{-- PROJECTS --}}
<section id="projects" class="scroll-mt-20 py-20">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-6">Projects</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @forelse ($projects as $p)
                <a href="{{ route('projects.show', $p) }}" class="glass rounded-2xl overflow-hidden hover:shadow-2xl hover:-translate-y-0.5 transition group flex flex-col h-full">
                    <div class="relative w-full overflow-hidden bg-slate-200/60 aspect-[4/3] sm:aspect-video">
                        @if($p->images->first())
                            <img src="{{ $p->images->first()->url }}" alt="{{ $p->title }}"
                                 class="absolute inset-0 h-full w-full object-cover object-center transition-transform duration-300 group-hover:scale-105"
                                 loading="lazy" decoding="async"
                                 onerror="this.onerror=null;this.src='{{ asset('images/HCMBlue.svg') }}';">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center text-slate-400 text-sm px-4 text-center">Belum ada gambar</div>
                        @endif
                    </div>
                    <div class="p-4 flex-1 flex flex-col">
                        <h3 class="font-semibold text-slate-900 line-clamp-2">{{ $p->title }}</h3>
                        <p class="text-sm text-slate-600 line-clamp-2 mt-1 flex-1">{{ $p->description }}</p>
                    </div>
                </a>
            @empty
                <p class="text-slate-500">No projects yet.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- GALLERY --}}
<section id="gallery" class="scroll-mt-20 max-w-7xl mx-auto px-4 py-20">
    <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-6">Gallery</h2>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @forelse ($gallery as $g)
            <figure class="glass rounded-xl overflow-hidden hover:shadow-xl transition group">
                <img src="{{ $g->image_url }}" alt="{{ $g->title }}" class="w-full aspect-square object-cover group-hover:scale-105 transition" loading="lazy">
                <figcaption class="p-3">
                    <div class="font-medium text-sm truncate text-slate-800">{{ $g->title }}</div>
                    @if($g->taken_at)<div class="text-xs text-slate-500">{{ $g->taken_at->format('d M Y') }}</div>@endif
                </figcaption>
            </figure>
        @empty
            <p class="text-slate-500 col-span-full">No gallery items yet.</p>
        @endforelse
    </div>
</section>

@if($slides->count() > 1)
<script>
(() => {
    const root  = document.getElementById('heroCarousel');
    if (!root) return;
    const slides = root.querySelectorAll('.hc-slide');
    const dots   = root.querySelectorAll('[data-dot]');
    let idx = 0, timer;
    const paint = () => {
        slides.forEach((s, i) => s.classList.toggle('is-active', i === idx));
        dots.forEach((d, i)   => d.classList.toggle('bg-white', i === idx));
    };
    const next  = () => { idx = (idx + 1) % slides.length; paint(); };
    const start = () => timer = setInterval(next, 5000);
    const stop  = () => clearInterval(timer);
    dots.forEach(d => d.addEventListener('click', () => { idx = +d.dataset.dot; paint(); stop(); start(); }));
    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', start);
    document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());
    paint(); start();
})();
</script>
@endif
@endsection
