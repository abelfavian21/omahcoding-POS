@extends('layouts.app')
@section('title', 'Staff')
@section('content')
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
  <div>
    <div class="text-xs text-slate-400 font-semibold mb-1">Dashboard › <span class="text-slate-700">Staff</span></div>
    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Manajemen Staff</h1>
    <p class="text-sm font-medium text-slate-500 mt-1">Kelola akun admin dan kasir beserta PIN login cepat.</p>
  </div>
  <a href="{{ route('staff.create') }}" class="btn-primary self-start sm:self-auto"><span class="material-symbols-outlined">person_add</span> Tambah Staff</a>
</div>
@if ($errors->any())<div class="mb-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm font-semibold">{{ $errors->first() }}</div>@endif

<div class="card overflow-hidden">
  {{-- tabel (tablet+) --}}
  <div class="hidden md:block overflow-x-auto p-2">
    <table class="w-full">
      <thead><tr class="border-b border-slate-100"><th class="th">Nama</th><th class="th">Peran</th><th class="th">Shift</th><th class="th">PIN</th><th class="th text-center">Transaksi</th><th class="th">Status</th><th class="th text-right">Aksi</th></tr></thead>
      <tbody class="divide-y divide-slate-50">
        @foreach ($staff as $s)
          <tr class="hover:bg-slate-50/70 transition-colors">
            <td class="td"><div class="flex items-center gap-3"><span class="w-10 h-10 rounded-full bg-[#181829] text-lime-accent grid place-items-center text-sm font-bold shrink-0">{{ $s->initials }}</span>
              <div><b>{{ $s->name }}</b><div class="text-xs text-slate-400">{{ $s->email }}</div></div></div></td>
            <td class="td"><span class="pill {{ $s->role === 'admin' ? 'pill-blue' : 'pill-slate' }}">{{ $s->role }}</span></td>
            <td class="td text-slate-600">{{ $s->shift ?: '—' }}</td>
            <td class="td">{{ $s->pin ? '••••' : '—' }}</td>
            <td class="td text-center">{{ $s->orders_count }}</td>
            <td class="td">{!! $s->is_active ? '<span class="pill pill-green">Aktif</span>' : '<span class="pill pill-red">Nonaktif</span>' !!}</td>
            <td class="td text-right whitespace-nowrap">
              <a href="{{ route('staff.edit', $s) }}" class="btn-ghost !h-9 !px-3" aria-label="Edit"><span class="material-symbols-outlined">edit</span></a>
              @if ($s->id !== auth()->id())
                <form method="POST" action="{{ route('staff.destroy', $s) }}" class="inline" onsubmit="return confirm('Hapus / nonaktifkan {{ addslashes($s->name) }}?')">
                  @csrf @method('DELETE')<button class="btn-ghost !h-9 !px-3 text-red-600" aria-label="Hapus"><span class="material-symbols-outlined">delete</span></button></form>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- daftar kartu (mobile) --}}
  <ul class="md:hidden divide-y divide-slate-50 px-4">
    @foreach ($staff as $s)
      <li class="py-3.5">
        <div class="flex items-center gap-3">
          <span class="w-12 h-12 rounded-full bg-[#181829] text-lime-accent grid place-items-center text-sm font-bold shrink-0">{{ $s->initials }}</span>
          <div class="min-w-0 flex-1">
            <div class="font-bold truncate">{{ $s->name }}</div>
            <div class="text-xs text-slate-400 truncate">{{ $s->email }}</div>
            <div class="text-xs text-slate-500 mt-0.5">Shift {{ $s->shift ?: '—' }} · {{ $s->orders_count }} transaksi · PIN {{ $s->pin ? '••••' : '—' }}</div>
          </div>
          <div class="flex flex-col items-end gap-1.5 shrink-0">
            <span class="pill {{ $s->role === 'admin' ? 'pill-blue' : 'pill-slate' }}">{{ $s->role }}</span>
            {!! $s->is_active ? '<span class="pill pill-green">Aktif</span>' : '<span class="pill pill-red">Nonaktif</span>' !!}
          </div>
        </div>
        <div class="flex gap-2 mt-3">
          <a href="{{ route('staff.edit', $s) }}" class="btn-secondary !h-10 flex-1 !px-3"><span class="material-symbols-outlined">edit</span> Edit</a>
          @if ($s->id !== auth()->id())
            <form method="POST" action="{{ route('staff.destroy', $s) }}" onsubmit="return confirm('Hapus / nonaktifkan {{ addslashes($s->name) }}?')">
              @csrf @method('DELETE')<button class="btn-danger !h-10 !w-10 !px-0" aria-label="Hapus"><span class="material-symbols-outlined">delete</span></button></form>
          @endif
        </div>
      </li>
    @endforeach
  </ul>
</div>
@endsection
