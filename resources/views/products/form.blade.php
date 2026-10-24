@extends('layouts.app')
@section('title', $product->exists ? 'Edit Produk' : 'Tambah Produk')
@section('content')
@php $editing = $product->exists; @endphp
<div class="max-w-3xl">
  <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1 h-9 px-3 -ml-3 rounded-full text-sm font-semibold text-slate-500 hover:bg-white hover:text-slate-900 transition"><span class="material-symbols-outlined" style="font-size:18px">arrow_back</span> Kembali ke produk</a>
  <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-2 mb-6">{{ $editing ? 'Edit Produk' : 'Tambah Produk' }}</h1>

  <form method="POST" enctype="multipart/form-data" class="card p-5 sm:p-6 space-y-5"
        action="{{ $editing ? route('products.update', $product) : route('products.store') }}">
    @csrf @if ($editing) @method('PUT') @endif

    @if ($editing)
      <div class="p-4 rounded-2xl bg-lime-50 border border-lime-200 flex items-center justify-between">
        <div>
          <div class="font-extrabold text-slate-900 text-sm">Varian & Resep Bahan Baku (BOM)</div>
          <div class="text-xs text-slate-600">Atur opsi ukuran/topping serta bahan baku yang terpotong otomatis.</div>
        </div>
        <a href="{{ route('products.recipe', $product) }}" class="btn-dark !h-9 !px-4 text-xs text-lime-accent">Kelola Resep & Varian &rarr;</a>
      </div>
    @endif

    <div class="grid sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2"><label class="label">Nama produk *</label>
        <input name="name" class="input" value="{{ old('name', $product->name) }}" required>
        @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
      <div><label class="label">SKU *</label>
        <input name="sku" class="input" value="{{ old('sku', $product->sku) }}" placeholder="MNU-001" required>
        @error('sku')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
      <div><label class="label">Kategori</label>
        <select name="category_id" class="input">
          <option value="">— Tanpa kategori —</option>
          @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>@endforeach
        </select></div>
      <div><label class="label">Harga jual (Rp) *</label>
        <input name="price" type="number" min="0" step="100" class="input" value="{{ old('price', $product->price !== null ? (int) $product->price : '') }}" required>
        @error('price')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
      <div><label class="label">Harga modal / HPP (Rp)</label>
        <input name="cost_price" type="number" min="0" step="100" class="input" value="{{ old('cost_price', (int) $product->cost_price) }}"></div>

      @unless ($editing)
        <div><label class="label">Stok awal</label>
          <input name="stock" type="number" min="0" class="input" value="{{ old('stock', 0) }}"></div>
      @else
        <div><label class="label">Stok saat ini</label>
          <div class="input flex items-center bg-slate-50 text-slate-500">{{ $product->stock }} <a href="{{ route('stock.index', ['product' => $product->id]) }}" class="ml-auto text-brand-600 font-semibold">Atur stok →</a></div></div>
      @endunless
      <div><label class="label">Batas stok menipis *</label>
        <input name="min_stock" type="number" min="0" class="input" value="{{ old('min_stock', $product->min_stock) }}" required></div>

      <div class="sm:col-span-2">
        <label class="label">Ikon (dipakai bila tanpa foto)</label>
        @php
          $cur = old('emoji', \App\Support\Icons::name($product->emoji));
          $opts = \App\Support\Icons::picker();
          if (! isset($opts[$cur])) { $opts[$cur] = $cur; }   // ikon lama di luar daftar tetap terpilih
        @endphp
        <div class="grid grid-cols-6 sm:grid-cols-8 gap-2">
          @foreach ($opts as $name => $label)
            <label class="cursor-pointer" title="{{ $label }}">
              <input type="radio" name="emoji" value="{{ $name }}" class="peer sr-only" @checked($cur === $name)>
              <span class="grid place-items-center h-11 rounded-xl border border-slate-200 bg-white text-slate-600 transition peer-checked:bg-lime-accent peer-checked:text-slate-950 peer-checked:border-lime-accent peer-focus-visible:ring-4 peer-focus-visible:ring-lime-accent/40">
                <span class="material-symbols-outlined" aria-hidden="true">{{ $name }}</span>
                <span class="sr-only">{{ $label }}</span>
              </span>
            </label>
          @endforeach
        </div>
        @error('emoji')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
      <div><label class="label">Foto produk (maks 2 MB)</label>
        <input name="image" type="file" accept="image/*" class="input !h-auto py-2 text-xs">
        @error('image')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>

      <div class="sm:col-span-2"><label class="label">Deskripsi</label>
        <textarea name="description" rows="3" class="input">{{ old('description', $product->description) }}</textarea></div>
    </div>

    <label class="flex items-center gap-2 text-sm font-semibold">
      <input type="hidden" name="is_active" value="0">
      <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-[#536500] focus:ring-[#D2F839]/50" @checked(old('is_active', $product->is_active))> Tampilkan di menu kasir
    </label>

    <div class="flex flex-col-reverse sm:flex-row gap-3 pt-2">
      <button class="btn-primary">Simpan</button>
      <a href="{{ route('products.index') }}" class="btn-ghost">Batal</a>
    </div>
  </form>
</div>
@endsection
