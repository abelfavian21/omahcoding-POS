@extends('layouts.app')
@section('title', 'Varian & Resep (BOM) · ' . $product->name)
@section('content')
<div class="space-y-6 max-w-4xl">
  <div>
    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1 h-9 px-3 -ml-3 rounded-full text-sm font-semibold text-slate-500 hover:bg-white hover:text-slate-900 transition">
      <span class="material-symbols-outlined" style="font-size:18px">arrow_back</span> Kembali ke Daftar Produk
    </a>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-2">
      <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">{{ $product->name }}</h1>
        <p class="text-sm text-slate-500 mt-1">
          Harga Jual Dasar: <b class="text-slate-900">Rp {{ number_format($product->price, 0, ',', '.') }}</b> ·
          Estimasi HPP: <b class="text-emerald-700">Rp {{ number_format($product->cost_price, 0, ',', '.') }}</b>
        </p>
      </div>
      <div class="flex gap-2">
        <a href="{{ route('products.edit', $product) }}" class="btn-ghost !h-9 text-xs">Edit Produk</a>
      </div>
    </div>
  </div>

  {{-- ================= TABEL VARIAN / MODIFIER ================= --}}
  <div class="card p-5 sm:p-6 space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-extrabold text-slate-900">1. Varian & Modifiers</h2>
        <p class="text-xs text-slate-500">Pilihan varian seperti Ukuran (Large/Reguler), Topping, atau Tingkat Manis.</p>
      </div>
      <button onclick="document.getElementById('varModal').classList.remove('hidden')" class="btn-secondary !h-8 !px-3 text-xs">
        <span class="material-symbols-outlined !text-base">add</span> Tambah Varian
      </button>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 border-b border-slate-100 text-xs text-slate-500 font-bold uppercase">
          <tr>
            <th class="px-4 py-2.5">Grup</th>
            <th class="px-4 py-2.5">Nama Opsi</th>
            <th class="px-4 py-2.5">Tambahan Harga (+Rp)</th>
            <th class="px-4 py-2.5">Tambahan HPP (+Rp)</th>
            <th class="px-4 py-2.5 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse ($product->allVariants as $var)
            <tr>
              <td class="px-4 py-2.5"><span class="pill pill-slate !text-[11px]">{{ $var->group_name }}</span></td>
              <td class="px-4 py-2.5 font-bold text-slate-900">{{ $var->name }}</td>
              <td class="px-4 py-2.5 text-emerald-700 font-semibold">+Rp {{ number_format($var->price_extra, 0, ',', '.') }}</td>
              <td class="px-4 py-2.5 text-slate-600">+Rp {{ number_format($var->cost_extra, 0, ',', '.') }}</td>
              <td class="px-4 py-2.5 text-right">
                <form action="{{ route('products.variants.destroy', $var) }}" method="POST" onsubmit="return confirm('Hapus varian ini?')" class="inline">
                  @csrf @method('DELETE')
                  <button class="text-red-500 hover:text-red-700 p-1"><span class="material-symbols-outlined !text-lg">delete</span></button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-4 py-6 text-center text-slate-400 text-xs">Belum ada varian. Produk ini dijual dengan satu pilihan default.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ================= TABEL RESEP / BOM ================= --}}
  <div class="card p-5 sm:p-6 space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-extrabold text-slate-900">2. Resep Bahan Baku (Bill of Materials)</h2>
        <p class="text-xs text-slate-500">Takaran bahan baku yang otomatis terpotong saat menu ini dibayar di kasir.</p>
      </div>
      <button onclick="document.getElementById('recModal').classList.remove('hidden')" class="btn-primary !h-8 !px-3 text-xs">
        <span class="material-symbols-outlined !text-base">add</span> Tambah Bahan Resep
      </button>
    </div>

    @if ($ingredients->isEmpty())
      <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold">
        Anda belum memiliki data bahan baku. Tambahkan bahan baku terlebih dahulu di menu <a href="{{ route('ingredients.index') }}" class="underline font-bold">Bahan Baku (BOM)</a>.
      </div>
    @endif

    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 border-b border-slate-100 text-xs text-slate-500 font-bold uppercase">
          <tr>
            <th class="px-4 py-2.5">Bahan Mentah</th>
            <th class="px-4 py-2.5">Khusus Varian</th>
            <th class="px-4 py-2.5">Takaran / Porsi</th>
            <th class="px-4 py-2.5">Biaya Bahan</th>
            <th class="px-4 py-2.5 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse ($product->recipes as $r)
            <tr>
              <td class="px-4 py-2.5 font-bold text-slate-900">{{ $r->ingredient?->name }}</td>
              <td class="px-4 py-2.5">
                @if ($r->variant)
                  <span class="pill pill-brand !text-[11px]">{{ $r->variant->group_name }}: {{ $r->variant->name }}</span>
                @else
                  <span class="pill pill-slate !text-[11px]">Resep Dasar (Semua Varian)</span>
                @endif
              </td>
              <td class="px-4 py-2.5 font-extrabold text-slate-800">{{ number_format($r->amount, 2) }} {{ $r->ingredient?->unit }}</td>
              <td class="px-4 py-2.5 text-slate-600 text-xs">
                @if ($r->ingredient)
                  Rp {{ number_format($r->amount * $r->ingredient->cost_per_unit, 0, ',', '.') }}
                @endif
              </td>
              <td class="px-4 py-2.5 text-right">
                <form action="{{ route('products.recipes.destroy', $r) }}" method="POST" onsubmit="return confirm('Hapus bahan resep ini?')" class="inline">
                  @csrf @method('DELETE')
                  <button class="text-red-500 hover:text-red-700 p-1"><span class="material-symbols-outlined !text-lg">delete</span></button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-4 py-6 text-center text-slate-400 text-xs">
                Belum ada resep bahan baku. Pengurangan stok akan memotong stok fisik produk jadi langsung.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Modal Tambah Varian --}}
<div id="varModal" class="hidden fixed inset-0 z-50 bg-slate-950/50 backdrop-blur-sm grid items-center justify-center p-4">
  <div class="card w-full max-w-md p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-extrabold text-lg">Tambah Pilihan Varian</h3>
      <button onclick="document.getElementById('varModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined">close</span></button>
    </div>
    <form action="{{ route('products.variants.store', $product) }}" method="POST" class="space-y-4">
      @csrf
      <div>
        <label class="label">Grup Varian *</label>
        <input name="group_name" list="group_suggestions" class="input" placeholder="Pilih atau ketik baru (misal: Ukuran, Pilihan Rasa, Topping)" required>
        <datalist id="group_suggestions">
          <option value="Ukuran">
          <option value="Varian Rasa">
          <option value="Topping">
          <option value="Tingkat Manis">
          <option value="Suhu">
        </datalist>
        <p class="text-[11px] text-slate-500 mt-1">💡 <b>Tips:</b> Masukkan nama grup yang sama (misal <i>"Ukuran"</i>) agar kasir hanya bisa memilih <b>satu pilihan</b> (radio button).</p>
      </div>
      <div>
        <label class="label">Nama Pilihan *</label>
        <input name="name" class="input" placeholder="Contoh: Large, Extra Boba, Less Sweet" required>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="label">Tambahan Harga (Rp)</label>
          <input name="price_extra" type="number" step="500" min="0" value="0" class="input" required>
        </div>
        <div>
          <label class="label">Tambahan HPP (Rp)</label>
          <input name="cost_extra" type="number" step="500" min="0" value="0" class="input">
        </div>
      </div>
      <div class="pt-2 flex justify-end gap-2">
        <button type="button" onclick="document.getElementById('varModal').classList.add('hidden')" class="btn-ghost">Batal</button>
        <button type="submit" class="btn-primary">Simpan Varian</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Tambah Bahan Resep --}}
<div id="recModal" class="hidden fixed inset-0 z-50 bg-slate-950/50 backdrop-blur-sm grid items-center justify-center p-4">
  <div class="card w-full max-w-md p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-extrabold text-lg">Tambah Bahan ke Resep</h3>
      <button onclick="document.getElementById('recModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined">close</span></button>
    </div>
    <form action="{{ route('products.recipes.store', $product) }}" method="POST" class="space-y-4">
      @csrf
      <div>
        <label class="label">Pilih Bahan Baku *</label>
        <select name="ingredient_id" class="input" required>
          <option value="">-- Pilih Bahan --</option>
          @foreach ($ingredients as $ing)
            <option value="{{ $ing->id }}">{{ $ing->name }} (Satuan: {{ $ing->unit }})</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="label">Khusus Varian Tertentu (Opsional)</label>
        <select name="product_variant_id" class="input">
          <option value="">Resep Dasar (Berlaku untuk semua)</option>
          @foreach ($product->allVariants as $var)
            <option value="{{ $var->id }}">{{ $var->group_name }}: {{ $var->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="label">Takaran yang Dibutuhkan per Porsi *</label>
        <input name="amount" type="number" step="0.01" min="0.01" class="input" placeholder="Contoh: 15 (untuk 15 gram kopi)" required>
      </div>
      <div class="pt-2 flex justify-end gap-2">
        <button type="button" onclick="document.getElementById('recModal').classList.add('hidden')" class="btn-ghost">Batal</button>
        <button type="submit" class="btn-primary">Tambahkan ke Resep</button>
      </div>
    </form>
  </div>
</div>
@endsection
