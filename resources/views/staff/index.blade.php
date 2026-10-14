@extends('layouts.app')
@section('title', 'Staff')
@section('content')
<div class="flex flex-wrap items-end justify-between gap-4 mb-6">
  <div>
    <div class="text-xs text-slate-500 mb-1">Dashboard › <span class="text-brand-600 font-semibold">Staff</span></div>
    <h1 class="text-3xl font-extrabold tracking-tight">Manajemen Staff</h1>
    <p class="text-sm text-slate-500">Kelola akun admin dan kasir beserta PIN login cepat.</p>
  </div>
  <a href="{{ route('staff.create') }}" class="btn-primary"><span class="material-symbols-outlined">person_add</span> Tambah Staff</a>
</div>
@if ($errors->any())<div class="mb-4 rounded-xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ $errors->first() }}</div>@endif
<div class="card overflow-hidden"><div class="overflow-x-auto">
  <table class="w-full">
    <thead class="bg-brand-50/50"><tr><th class="th">Nama</th><th class="th">Peran</th><th class="th">Shift</th><th class="th">PIN</th><th class="th text-center">Transaksi</th><th class="th">Status</th><th class="th text-right">Aksi</th></tr></thead>
    <tbody class="divide-y divide-slate-50">
      @foreach ($staff as $s)
        <tr>
          <td class="td"><div class="flex items-center gap-3"><span class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 grid place-items-center text-sm font-bold">{{ $s->initials }}</span>
            <div><b>{{ $s->name }}</b><div class="text-xs text-slate-500">{{ $s->email }}</div></div></div></td>
          <td class="td"><span class="pill {{ $s->role === 'admin' ? 'pill-blue' : 'pill-slate' }}">{{ $s->role }}</span></td>
          <td class="td text-slate-600">{{ $s->shift ?: '—' }}</td>
          <td class="td">{{ $s->pin ? '••••' : '—' }}</td>
          <td class="td text-center">{{ $s->orders_count }}</td>
          <td class="td">{!! $s->is_active ? '<span class="pill pill-green">Aktif</span>' : '<span class="pill pill-red">Nonaktif</span>' !!}</td>
          <td class="td text-right whitespace-nowrap">
            <a href="{{ route('staff.edit', $s) }}" class="btn-ghost !h-9 !px-3"><span class="material-symbols-outlined">edit</span></a>
            @if ($s->id !== auth()->id())
              <form method="POST" action="{{ route('staff.destroy', $s) }}" class="inline" onsubmit="return confirm('Hapus / nonaktifkan {{ addslashes($s->name) }}?')">
                @csrf @method('DELETE')<button class="btn-ghost !h-9 !px-3 text-red-600"><span class="material-symbols-outlined">delete</span></button></form>
            @endif
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div></div>
@endsection
