@props(['user'])
@php
    $role = $user->roles->pluck('name')->first();
    $labels = ['dosen' => 'Dosen', 'member' => 'Mahasiswa', 'alumni' => 'Alumni'];
    $chips  = [
        'dosen'  => 'bg-amber-100/80 text-amber-800',
        'member' => 'bg-blue-100/80 text-blue-800',
        'alumni' => 'bg-emerald-100/80 text-emerald-800',
    ];
@endphp
<article class="glass rounded-2xl overflow-hidden hover:shadow-2xl hover:-translate-y-0.5 transition group">
    <div class="aspect-square bg-slate-200/50 overflow-hidden">
        <img src="{{ $user->profile?->photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=1e3a8a&color=fff' }}"
             alt="{{ $user->name }}" class="w-full h-full object-cover group-hover:scale-105 transition" loading="lazy">
    </div>
    <div class="p-4">
        <div class="flex items-start justify-between gap-2 mb-1">
            <h3 class="font-semibold text-slate-900 truncate">{{ $user->name }}</h3>
            @if($role)
                <span class="shrink-0 text-[10px] font-semibold uppercase px-2 py-0.5 rounded-full backdrop-blur {{ $chips[$role] ?? 'bg-slate-100/80 text-slate-700' }}">
                    {{ $labels[$role] ?? $role }}
                </span>
            @endif
        </div>
        <p class="text-sm text-slate-600 truncate">{{ $user->profile?->prodi ?? '-' }}</p>
        @if($user->profile?->angkatan)
            <p class="text-xs text-blue-700 font-medium mt-1">
                {{ $role === 'dosen' ? 'Bergabung' : 'Angkatan' }} {{ $user->profile->angkatan }}
            </p>
        @endif
    </div>
</article>
