@extends('layouts.public')
@section('title', 'People')

@section('content')
<section class="relative overflow-hidden text-white py-14">
    <div class="absolute inset-0 bg-gradient-to-br from-blue-900 via-blue-800 to-slate-900"></div>
    <div class="absolute inset-0 opacity-40" style="background:
        radial-gradient(500px 300px at 15% 30%, rgba(56,189,248,0.35), transparent 60%),
        radial-gradient(500px 300px at 85% 70%, rgba(139,92,246,0.30), transparent 60%);"></div>
    <div class="relative max-w-7xl mx-auto px-4">
        <h1 class="text-3xl md:text-4xl font-bold">People</h1>
        <p class="text-blue-200 mt-2">Dosen, mahasiswa aktif, dan alumni HCM Laboratory.</p>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-8">
    <form id="peopleFilters" method="GET" action="{{ route('people') }}" class="grid md:grid-cols-6 gap-3 mb-6 glass rounded-2xl p-4">
        <div class="md:col-span-2 relative">
            <input name="q" value="{{ request('q') }}" placeholder="Search name..." autocomplete="off"
                   class="glass-input rounded-lg w-full pr-10">
            <span id="peopleSpinner" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                <svg class="animate-spin h-4 w-4 text-blue-700" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
            </span>
        </div>
        <select name="role" class="glass-input rounded-lg">
            <option value="">All Roles</option>
            <option value="dosen"  @selected(request('role')==='dosen')>Dosen</option>
            <option value="member" @selected(request('role')==='member')>Mahasiswa</option>
            <option value="alumni" @selected(request('role')==='alumni')>Alumni</option>
        </select>
        <select name="prodi" class="glass-input rounded-lg">
            <option value="">All Study Programs</option>
            @foreach ($prodis as $p)
                <option value="{{ $p }}" @selected(request('prodi') === $p)>{{ $p }}</option>
            @endforeach
        </select>
        <select name="angkatan" class="glass-input rounded-lg">
            <option value="">All Batches</option>
            @foreach ($angkatans as $a)
                <option value="{{ $a }}" @selected((int)request('angkatan') === (int)$a)>{{ $a }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <select name="sort" class="glass-input rounded-lg flex-1">
                <option value="name"       @selected(request('sort')==='name')>Name A-Z</option>
                <option value="batch_desc" @selected(request('sort')==='batch_desc')>Batch (Newest)</option>
                <option value="batch_asc"  @selected(request('sort')==='batch_asc')>Batch (Oldest)</option>
            </select>
            <button class="px-4 py-2 rounded-lg bg-blue-700 text-white font-medium hover:bg-blue-800 md:hidden">Go</button>
        </div>
    </form>

    <div class="flex flex-wrap gap-2 mb-6 text-sm">
        @foreach (['' => 'All', 'dosen' => 'Dosen', 'member' => 'Mahasiswa', 'alumni' => 'Alumni'] as $k => $label)
            <button type="button" data-role-chip="{{ $k }}"
               class="px-3 py-1.5 rounded-full {{ (string) request('role') === (string) $k ? 'bg-blue-700 text-white' : 'bg-white/50 hover:bg-white/80 backdrop-blur' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div id="peopleResults">
        @include('public.partials.people-list', ['users' => $users])
    </div>
</section>

<script>
(() => {
    const form    = document.getElementById('peopleFilters');
    const results = document.getElementById('peopleResults');
    const spinner = document.getElementById('peopleSpinner');
    if (!form || !results) return;

    let controller;
    const debounce = (fn, ms) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };

    const fetchList = async () => {
        controller?.abort();
        controller = new AbortController();
        const params = new URLSearchParams(new FormData(form));
        params.set('partial', '1');
        spinner.classList.remove('hidden');
        try {
            const r = await fetch("{{ route('people') }}?" + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            });
            results.innerHTML = await r.text();
            const shown = new URLSearchParams(new FormData(form));
            shown.delete('partial');
            history.replaceState(null, '', "{{ route('people') }}" + (shown.toString() ? '?' + shown.toString() : ''));
        } catch (e) {
            if (e.name !== 'AbortError') console.error(e);
        } finally {
            spinner.classList.add('hidden');
        }
    };

    const kick = debounce(fetchList, 250);
    form.querySelectorAll('input, select').forEach(el => {
        el.addEventListener('input', kick);
        el.addEventListener('change', fetchList);
    });
    form.addEventListener('submit', e => { e.preventDefault(); fetchList(); });

    // role chip click: set select + fetch
    document.querySelectorAll('[data-role-chip]').forEach(chip => chip.addEventListener('click', () => {
        form.role.value = chip.dataset.roleChip;
        document.querySelectorAll('[data-role-chip]').forEach(c => {
            c.classList.toggle('bg-blue-700', c === chip);
            c.classList.toggle('text-white', c === chip);
            c.classList.toggle('bg-slate-200', c !== chip);
        });
        fetchList();
    }));

    // pagination inside partial: intercept and load via ajax
    results.addEventListener('click', e => {
        const a = e.target.closest('a[href]');
        if (!a) return;
        const url = new URL(a.href, location.origin);
        if (url.pathname !== "{{ route('people') }}") return;
        e.preventDefault();
        url.searchParams.set('partial', '1');
        spinner.classList.remove('hidden');
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                results.innerHTML = html;
                results.scrollIntoView({ behavior: 'smooth', block: 'start' });
            })
            .finally(() => spinner.classList.add('hidden'));
    });
})();
</script>
@endsection
