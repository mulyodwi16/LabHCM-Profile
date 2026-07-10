<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
    @forelse ($users as $u)
        <x-person-card :user="$u" />
    @empty
        <p class="text-slate-500 col-span-full">No people match your filters.</p>
    @endforelse
</div>

<div class="mt-8">{{ $users->links() }}</div>
