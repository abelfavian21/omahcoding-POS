@extends('layouts.app')
@section('title', 'Produk')
@section('content')
@php
  $admin = auth()->user()->isAdmin();
  $stockPill = ['aman' => 'pill-green', 'menipis' => 'pill-amber', 'habis' => 'pill-red'];
@endphp
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
  <div>
    <div class="text-xs text-slate-400 font-semibold mb-1">Dashboard › <span class="text-slate-700">Produk</span></div>
    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Produk &amp; Menu</h1>
    <p class="text-sm font-medium text-slate-500 mt-1">Kelola menu, harga, dan pantau ketersediaan stok.</p>
  </div>
  @if ($admin)
    <div class="flex flex-wrap gap-2">
      <a href="{{ route('stock.index') }}" class="btn-secondary"><span class="material-symbols-outlined">warehouse</span> Riwayat Stok</a>
      <a href="{{ route('products.create') }}" class="btn-primary"><span class="material-symbols-outlined">add</span> Tambah Produk</a>
    </div>
  @endif
</div>

<form method="GET" class="card p-4 mb-5 grid sm:grid-cols-2 lg:flex lg:flex-wrap lg:items-center gap-3">
  <div class="relative sm:col-span-2 lg:flex-1 lg:min-w-[240px]">
    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:19px">search</span>
    <input name="q" value="{{ request('q') }}" class="input input-pill" placeholder="Cari nama / SKU…">
  </div>
  <select name="category" class="input lg:!w-auto" onchange="this.form.submit()">
    <option value="">Semua kategori</option>
    @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->emoji }} {{ $c->name }}</option>@endforeach
  </select>
  <label class="chip gap-2 cursor-pointer {{ request('stock') === 'low' ? '!bg-amber-50 !border-amber-200 !text-amber-700' : '' }}">
    <input type="checkbox" name="stock" value="low" class="rounded text-amber-500" onchange="this.form.submit()" @checked(request('stock') === 'low')> Stok menipis ({{ $lowCount }})
  </label>
  <button class="btn-dark text-lime-accent sm:col-span-2 lg:col-auto">Cari</button>
</form>

<div class="card overflow-hidden">
  {{-- tabel (tablet+) --}}
  <div class="hidden md:block overflow-x-auto p-2">
    <table class="w-full">
      <thead><tr class="border-b border-slate-100">
        <th class="th">Produk</th><th class="th">Kategori</th><th class="th text-right">Harga</th><th class="th text-center">Stok</th><th class="th">Status</th>@if ($admin)<th class="th text-right">Aksi</th>@endif
      </tr></thead>
      <tbody class="divide-y divide-slate-50">
        @forelse ($products as $p)
          <tr class="hover:bg-slate-50/70 transition-colors">
            <td class="td">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 grid place-items-center text-2xl overflow-hidden shrink-0">
                  @if ($p->image_url)<img src="{{ $p->image_url }}" class="w-full h-full object-cover" alt="">@else{{ $p->emoji ?? '🍽️' }}@endif
                </div>
                <div><div class="font-bold">{{ $p->name }}</div><div class="text-xs text-slate-400">{{ $p->sku }}</div></div>
              </div>
            </td>
            <td class="td"><span class="pill pill-slate">{{ $p->category->name ?? '—' }}</span></td>
            <td class="td text-right font-extrabold">@rupiah($p->price)</td>
            <td class="td text-center"><span class="pill {{ $stockPill[$p->stock_status] }}">{{ $p->stock }}</span></td>
            <td class="td">{!! $p->is_active ? '<span class="pill pill-blue">Aktif</span>' : '<span class="pill pill-slate">Nonaktif</span>' !!}</td>
            @if ($admin)
              <td class="td text-right whitespace-nowrap">
                <a href="{{ route('stock.index', ['product' => $p->id]) }}" class="btn-ghost !h-9 !px-3" title="Atur stok"><span class="material-symbols-outlined">inventory</span></a>
                <a href="{{ route('products.edit', $p) }}" class="btn-ghost !h-9 !px-3" title="Edit"><span class="material-symbols-outlined">edit</span></a>
                <form method="POST" action="{{ route('products.destroy', $p) }}" class="inline" onsubmit="return confirm('Hapus produk {{ addslashes($p->name) }}?')">
                  @csrf @method('DELETE')
                  <button class="btn-ghost !h-9 !px-3 text-red-600" title="Hapus"><span class="material-symbols-outlined">delete</span></button>
                </form>
              </td>
            @endif
          </tr>
        @empty
          <tr><td colspan="6" class="td text-center text-slate-500 py-10">Tidak ada produk.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- daftar kartu (mobile) --}}
  <ul class="md:hidden divide-y divide-slate-50 px-4">
    @forelse ($products as $p)
      <li class="py-3.5">
        <div class="flex items-center gap-3">
          <div class="w-14 h-14 rounded-2xl bg-slate-100 grid place-items-center text-3xl overflow-hidden shrink-0">
            @if ($p->image_url)<img src="{{ $p->image_url }}" class="w-full h-full object-cover" alt="">@else{{ $p->emoji ?? '🍽️' }}@endif
          </div>
          <div class="min-w-0 flex-1">
            <div class="font-bold truncate">{{ $p->name }}</div>
            <div class="text-xs text-slate-400 truncate">{{ $p->sku }} · {{ $p->category->name ?? '—' }}</div>
            <div class="font-extrabold text-sm mt-0.5">@rupiah($p->price)</div>
          </div>
          <div class="flex flex-col items-end gap-1.5 shrink-0">
            <span class="pill {{ $stockPill[$p->stock_status] }}">Stok {{ $p->stock }}</span>
            {!! $p->is_active ? '<span class="pill pill-blue">Aktif</span>' : '<span class="pill pill-slate">Nonaktif</span>' !!}
          </div>
        </div>
        @if ($admin)
          <div class="flex gap-2 mt-3">
            <a href="{{ route('stock.index', ['product' => $p->id]) }}" class="btn-secondary !h-10 flex-1 !px-3"><span class="material-symbols-outlined">inventory</span> Stok</a>
            <a href="{{ route('products.edit', $p) }}" class="btn-secondary !h-10 flex-1 !px-3"><span class="material-symbols-outlined">edit</span> Edit</a>
            <form method="POST" action="{{ route('products.destroy', $p) }}" onsubmit="return confirm('Hapus produk {{ addslashes($p->name) }}?')">
              @csrf @method('DELETE')
              <button class="btn-danger !h-10 !w-10 !px-0" aria-label="Hapus"><span class="material-symbols-outlined">delete</span></button>
            </form>
          </div>
        @endif
      </li>
    @empty
      <li class="py-10 text-center text-sm text-slate-500">Tidak ada produk.</li>
    @endforelse
  </ul>
  <div class="px-4 py-3 border-t border-slate-100 overflow-x-auto">{{ $products->links() }}</div>
</div>
@endsection
