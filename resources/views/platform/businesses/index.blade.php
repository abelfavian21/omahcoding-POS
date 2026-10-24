@extends('layouts.platform')
@section('title', 'Toko')
@section('content')
@php $pill = ['trial' => 'pill-amber', 'active' => 'pill-green', 'suspended' => 'pill-red']; @endphp
<div class="mb-5">
  <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Toko</h1>
  <p class="text-sm font-medium text-slate-500 mt-1">{{ number_format($businesses->total(), 0, ',', '.') }} toko{{ $q !== '' || $status || $ending ? ' sesuai filter' : '' }}.</p>
</div>

<form method="GET" class="card p-4 mb-4 grid sm:grid-cols-[minmax(0,1fr)_200px_auto_auto] gap-3 items-end">
  <div><label for="q" class="label">Cari nama, kode, email, atau pemilik</label><input id="q" class="input" type="search" name="q" value="{{ $q }}" maxlength="80"></div>
  <div><label for="status" class="label">Status</label>
    <select id="status" name="status" class="input"><option value="">Semua</option>@foreach ($statuses as $k => $v)<option value="{{ $k }}" @selected($status === $k)>{{ $v }}</option>@endforeach</select></div>
  <label class="flex items-center gap-2 h-11 text-sm font-semibold"><input type="checkbox" name="ending" value="1" @checked($ending) class="rounded"> Trial ≤ 7 hari</label>
  <button class="btn-dark">Terapkan</button>
</form>

<div class="card overflow-hidden">
  <div class="hidden md:block overflow-x-auto p-2">
    <table class="w-full">
      <thead><tr class="border-b border-slate-100"><th class="th">Toko</th><th class="th">Pemilik</th><th class="th">Kategori</th><th class="th">Status</th><th class="th">Pengguna</th><th class="th">Daftar</th><th class="th"></th></tr></thead>
      <tbody class="divide-y divide-slate-50">
        @forelse ($businesses as $b)
          <tr class="hover:bg-slate-50/70">
            <td class="td"><a class="font-bold hover:underline" href="{{ route('platform.businesses.show', $b) }}">{{ $b->name }}</a><div class="text-xs text-slate-500">{{ $b->code }}</div></td>
            <td class="td">{{ $b->owner_name }}<div class="text-xs text-slate-500">{{ $b->email }}</div></td>
            <td class="td">{{ $b->category?->name }}</td>
            <td class="td"><span class="pill {{ $pill[$b->status] ?? 'pill-slate' }}">{{ $statuses[$b->status] ?? $b->status }}</span>@if ($b->status === 'trial' && $b->trial_ends_at)<div class="text-xs text-slate-500 mt-1">s/d {{ $b->trial_ends_at->translatedFormat('d M Y') }}</div>@endif</td>
            <td class="td">{{ $b->users_count }}</td>
            <td class="td whitespace-nowrap">{{ $b->created_at->translatedFormat('d M Y') }}</td>
            <td class="td text-right"><a class="btn-secondary !h-9" href="{{ route('platform.businesses.show', $b) }}">Buka</a></td>
          </tr>
        @empty
          <tr><td class="td text-center text-slate-500 py-10" colspan="7">Tidak ada toko yang cocok.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <ul class="md:hidden divide-y divide-slate-50 px-4">
    @forelse ($businesses as $b)
      <li class="py-3.5"><a href="{{ route('platform.businesses.show', $b) }}" class="block">
        <div class="flex items-center justify-between gap-2"><span class="font-bold truncate">{{ $b->name }}</span><span class="pill {{ $pill[$b->status] ?? 'pill-slate' }}">{{ $statuses[$b->status] ?? $b->status }}</span></div>
        <div class="text-xs text-slate-500 mt-1">{{ $b->code }} · {{ $b->owner_name }} · {{ $b->users_count }} pengguna</div>
        @if ($b->status === 'trial' && $b->trial_ends_at)<div class="text-xs text-slate-500">Trial s/d {{ $b->trial_ends_at->translatedFormat('d M Y') }}</div>@endif
      </a></li>
    @empty
      <li class="py-10 text-center text-sm text-slate-500">Tidak ada toko yang cocok.</li>
    @endforelse
  </ul>
</div>
<div class="mt-4 overflow-x-auto">{{ $businesses->links() }}</div>
@endsection
