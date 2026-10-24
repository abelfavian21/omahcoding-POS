@extends('layouts.app')
@section('title', 'Bahan Baku & Resep (BOM)')
@section('content')
<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Manajemen Bahan Baku</h1>
      <p class="text-sm text-slate-500 mt-1">Kelola stok bahan mentah (kopi, susu, sirup, cup) untuk sistem resep (Bill of Materials).</p>
    </div>
    <div class="flex gap-2">
      <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="btn-primary !h-10 text-xs">
        <span class="material-symbols-outlined">add</span> Tambah Bahan
      </button>
    </div>
  </div>

  @if (session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between">
      <span>{{ session('success') }}</span>
      <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
    </div>
  @endif
  @if ($errors->any())
    <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold">
      {{ $errors->first() }}
    </div>
  @endif

  <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
    <div class="flex gap-2 items-center">
      <a href="{{ route('ingredients.index') }}" class="pill {{ !$filter ? 'pill-brand' : 'pill-slate' }} cursor-pointer">Semua ({{ \App\Models\Ingredient::count() }})</a>
      <a href="{{ route('ingredients.index', ['filter' => 'low']) }}" class="pill {{ $filter === 'low' ? 'pill-brand' : 'pill-slate' }} cursor-pointer">Menipis / Habis ({{ $lowCount }})</a>
    </div>
    <form method="GET" class="relative sm:w-72">
      <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:18px">search</span>
      <input name="q" value="{{ $q }}" class="input input-pill !pl-9" placeholder="Cari nama bahan...">
    </form>
  </div>

  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 border-b border-slate-100 text-xs text-slate-500 font-bold uppercase tracking-wider">
          <tr>
            <th class="px-5 py-3">Nama Bahan</th>
            <th class="px-5 py-3">Satuan</th>
            <th class="px-5 py-3">Stok Saat Ini</th>
            <th class="px-5 py-3">Batas Minimum</th>
            <th class="px-5 py-3">Biaya / Satuan</th>
            <th class="px-5 py-3">Status</th>
            <th class="px-5 py-3 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse ($ingredients as $ing)
            <tr class="hover:bg-slate-50/70 transition">
              <td class="px-5 py-3.5 font-bold text-slate-900">{{ $ing->name }}</td>
              <td class="px-5 py-3.5 text-slate-600"><span class="pill pill-slate !py-0.5">{{ $ing->unit }}</span></td>
              <td class="px-5 py-3.5 font-extrabold text-base {{ $ing->stock <= $ing->min_stock ? 'text-amber-600' : 'text-slate-900' }}">
                {{ number_format($ing->stock, 2) }} <span class="text-xs font-normal text-slate-500">{{ $ing->unit }}</span>
              </td>
              <td class="px-5 py-3.5 text-slate-500">{{ number_format($ing->min_stock, 2) }} {{ $ing->unit }}</td>
              <td class="px-5 py-3.5 text-slate-700 font-medium">Rp {{ number_format($ing->cost_per_unit, 0, ',', '.') }} / {{ $ing->unit }}</td>
              <td class="px-5 py-3.5">
                @if ($ing->stock <= 0)
                  <span class="pill bg-red-100 text-red-700">Habis</span>
                @elseif ($ing->stock <= $ing->min_stock)
                  <span class="pill bg-amber-100 text-amber-700">Menipis</span>
                @else
                  <span class="pill bg-emerald-100 text-emerald-700">Aman</span>
                @endif
              </td>
              <td class="px-5 py-3.5 text-right space-x-1 whitespace-nowrap">
                <button onclick="openAdjust({{ json_encode($ing) }})" class="btn-secondary !h-8 !px-3 text-xs" title="Sesuaikan Stok">
                  <span class="material-symbols-outlined !text-base">tune</span> Sesuaikan
                </button>
                <a href="{{ route('ingredients.history', $ing) }}" class="btn-ghost !h-8 !px-3 text-xs text-slate-600 hover:text-slate-900" title="Riwayat Mutasi">
                  <span class="material-symbols-outlined !text-base">history</span>
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                Belum ada data bahan baku. Klik <b>Tambah Bahan</b> untuk memulai.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if ($ingredients->hasPages())
      <div class="p-4 border-t border-slate-100">
        {{ $ingredients->links() }}
      </div>
    @endif
  </div>
</div>

{{-- Modal Tambah Bahan --}}
<div id="addModal" class="hidden fixed inset-0 z-50 bg-slate-950/50 backdrop-blur-sm grid items-center justify-center p-4">
  <div class="card w-full max-w-md p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-extrabold text-lg">Tambah Bahan Baku Baru</h3>
      <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined">close</span></button>
    </div>
    <form action="{{ route('ingredients.store') }}" method="POST" class="space-y-4">
      @csrf
      <div>
        <label class="label">Nama Bahan *</label>
        <input name="name" class="input" placeholder="Contoh: Biji Kopi Espresso, Susu UHT" required>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="label">Satuan *</label>
          <input name="unit" class="input" placeholder="gram, ml, pcs" required>
        </div>
        <div>
          <label class="label">Harga Pokok per Satuan (Rp) *</label>
          <input name="cost_per_unit" type="number" step="0.01" min="0" class="input" placeholder="misal: 150" required>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="label">Stok Awal</label>
          <input name="stock" type="number" step="0.01" min="0" class="input" value="0" required>
        </div>
        <div>
          <label class="label">Peringatan Minimum</label>
          <input name="min_stock" type="number" step="0.01" min="0" class="input" value="10" required>
        </div>
      </div>
      <div class="pt-2 flex justify-end gap-2">
        <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="btn-ghost">Batal</button>
        <button type="submit" class="btn-primary">Simpan Bahan</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Penyesuaian Stok --}}
<div id="adjustModal" class="hidden fixed inset-0 z-50 bg-slate-950/50 backdrop-blur-sm grid items-center justify-center p-4">
  <div class="card w-full max-w-md p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-extrabold text-lg" id="adjTitle">Sesuaikan Stok</h3>
      <button onclick="document.getElementById('adjustModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined">close</span></button>
    </div>
    <form id="adjForm" method="POST" class="space-y-4">
      @csrf
      <div>
        <label class="label">Jenis Perubahan</label>
        <select name="type" class="input" required>
          <option value="in">Stok Masuk (Restock / Pembelian)</option>
          <option value="out">Stok Keluar (Rusak / Waste / Tumpah)</option>
          <option value="adjustment">Stok Opname (Atur ulang ke jumlah aktual)</option>
        </select>
      </div>
      <div>
        <label class="label">Jumlah (<span id="adjUnit"></span>)</label>
        <input name="amount" type="number" step="0.01" min="0.01" class="input" placeholder="Masukkan angka" required>
      </div>
      <div>
        <label class="label">Catatan</label>
        <input name="note" class="input" placeholder="Contoh: Beli di supermarket, kardus bocor">
      </div>
      <div class="pt-2 flex justify-end gap-2">
        <button type="button" onclick="document.getElementById('adjustModal').classList.add('hidden')" class="btn-ghost">Batal</button>
        <button type="submit" class="btn-primary">Terapkan</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openAdjust(ing) {
    document.getElementById('adjTitle').textContent = 'Sesuaikan: ' + ing.name;
    document.getElementById('adjUnit').textContent = ing.unit;
    document.getElementById('adjForm').action = '/ingredients/' + ing.id + '/adjust';
    document.getElementById('adjustModal').classList.remove('hidden');
  }
</script>
@endsection
