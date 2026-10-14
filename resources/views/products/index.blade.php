@extends('layouts.app')
@section('title', 'Produk')
@section('content')
@php $admin = auth()->user()->isAdmin(); @endphp
<div class="flex flex-wrap items-end justify-between gap-4 mb-6">
  <div>
    <div class="text-xs text-slate-500 mb-1">Dashboard › <span class="text-brand-600 font-semibold">Produk</span></div>
    <h1 class="text-3xl font-extrabold tracking-tight">Produk & Menu</h1>
    <p class="text-sm text-slate-500">Kelola menu, harga, dan pantau ketersediaan stok.</p>
  </div>
  @if ($admin)
    <div class="flex gap-2">
      <a href="{{ route('stock.index') }}" class="btn bg-white border border-slate-200 text-slate-700 hover:bg-slate-50"><span class="material-symbols-outlined">warehouse</span> Riwayat Stok</a>
      <a href="{{ route('products.create') }}" class="btn-primary"><span class="material-symbols-outlined">add</span> Tambah Produk</a>
    </div>
  @endif
</div>

<form method="GET" class="card p-4 mb-5 flex flex-wrap gap-3">
  <div class="flex-1 min-w-[200px]"><input name="q" value="{{ request('q') }}" class="input" placeholder="Cari nama / SKU…"></div>
  <select name="category" class="input !w-auto" onchange="this.form.submit()">
    <option value="">Semua kategori</option>
    @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->emoji }} {{ $c->name }}</option>@endforeach
  </select>
  <label class="flex items-center gap-2 text-sm font-semibold px-3 rounded-xl border border-slate-200 h-11 {{ request('stock') === 'low' ? 'bg-amber-50 border-amber-200 text-amber-700' : '' }}">
    <input type="checkbox" name="stock" value="low" class="rounded text-amber-500" onchange="this.form.submit()" @checked(request('stock') === 'low')> Stok menipis ({{ $lowCount }})
  </label>
  <button class="btn-secondary">Cari</button>
</form>

<div class="card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full">
      <thead class="bg-brand-50/50"><tr>
        <th class="th">Produk</th><th class="th">Kategori</th><th class="th text-right">Harga</th><th class="th text-center">Stok</th><th class="th">Status</th>@if ($admin)<th class="th text-right">Aksi</th>@endif
      </tr></thead>
      <tbody class="divide-y divide-slate-50">
        @forelse ($products as $p)
          <tr class="hover:bg-slate-50/60">
            <td class="td">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-amber-50 grid place-items-center text-2xl overflow-hidden shrink-0">
                  @if ($p->image_url)<img src="{{ $p->image_url }}" class="w-full h-full object-cover" alt="">@else{{ $p->emoji ?? '🍽️' }}@endif
                </div>
                <div><div class="font-bold">{{ $p->name }}</div><div class="text-xs text-slate-500">{{ $p->sku }}</div></div>
              </div>
            </td>
            <td class="td"><span class="pill pill-slate">{{ $p->category->name ?? '—' }}</span></td>
            <td class="td text-right font-bold">@rupiah($p->price)</td>
            <td class="td text-center">
              <span class="pill {{ ['aman' => 'pill-green', 'menipis' => 'pill-amber', 'habis' => 'pill-red'][$p->stock_status] }}">{{ $p->stock }}</span>
            </td>
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
  <div class="px-4 py-3 border-t border-slate-100">{{ $products->links() }}</div>
</div>
@endsection
