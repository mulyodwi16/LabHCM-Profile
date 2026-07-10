@extends('layouts.admin')
@section('title', ['dosen' => 'Dosen', 'member' => 'Mahasiswa', 'alumni' => 'Alumni'][$role] . 's')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-lg font-semibold">{{ ['dosen' => 'Dosen', 'member' => 'Mahasiswa', 'alumni' => 'Alumni'][$role] }} List ({{ $users->total() }})</h2>
    <a href="{{ route('admin.users.create', $role) }}" class="px-4 py-2 bg-blue-700 text-white rounded-lg">+ New {{ ['dosen' => 'Dosen', 'member' => 'Mahasiswa', 'alumni' => 'Alumni'][$role] }}</a>
</div>

<div class="glass rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-white/40 backdrop-blur text-left">
            <tr>
                <th class="p-3">Name</th><th class="p-3">Email</th><th class="p-3">Prodi</th><th class="p-3">Angkatan</th><th class="p-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @foreach ($users as $u)
                <tr>
                    <td class="p-3 font-medium">{{ $u->name }}</td>
                    <td class="p-3 text-slate-600">{{ $u->email }}</td>
                    <td class="p-3">{{ $u->profile?->prodi ?? '-' }}</td>
                    <td class="p-3">{{ $u->profile?->angkatan ?? '-' }}</td>
                    <td class="p-3 text-right space-x-2">
                        <a href="{{ route('admin.users.edit', ['role' => $role, 'user' => $u]) }}" class="text-blue-700">Edit</a>
                        <form method="POST" action="{{ route('admin.users.destroy', ['role' => $role, 'user' => $u]) }}" class="inline"
                              onsubmit="return confirm('Delete this user?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
