@extends('layouts.app')
@section('title', 'Stok Barang')
@section('content')
<div class="mb-6">
  <div class="text-xs text-slate-400 font-semibold mb-1">Dashboard › <span class="text-slate-700">Stok Barang</span></div>
  <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Stok Barang</h1>
  <p class="text-sm font-medium text-slate-500 mt-1">Catat barang masuk, keluar, dan stok opname. Setiap perubahan tersimpan di riwayat.</p>
</div>

<div class="grid lg:grid-cols-3 gap-5">
  <form method="POST" action="{{ route('stock.store') }}" class="card p-5 sm:p-6 space-y-4 h-fit">
    @csrf
    <div class="flex items-center gap-3">
      <span class="w-10 h-10 rounded-xl bg-[#181829] text-lime-accent grid place-items-center"><span class="material-symbols-outlined">inventory</span></span>
      <h2 class="font-extrabold text-base">Ubah Stok</h2>
    </div>
    <div><label class="label">Produk</label>
      <select name="product_id" class="input" required>
        @foreach ($products as $p)<option value="{{ $p->id }}" @selected(old('product_id', $selected) == $p->id)>{{ $p->name }} (stok {{ $p->stock }})</option>@endforeach
      </select></div>
    <div><label class="label">Jenis</label>
      <select name="type" id="type" class="input">
        <option value="in" @selected(old('type') === 'in')>Barang masuk (+)</option>
        <option value="out" @selected(old('type') === 'out')>Barang keluar / rusak (−)</option>
        <option value="adjustment" @selected(old('type') === 'adjustment')>Stok opname (set jumlah sebenarnya)</option>
      </select></div>
    <div><label class="label" id="qtyLabel">Jumlah</label>
      <input name="qty" type="number" min="0" inputmode="numeric" class="input" value="{{ old('qty') }}" required>
      @error('qty')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
    <div><label class="label">Catatan</label>
      <input name="note" class="input" value="{{ old('note') }}" placeholder="mis. Belanja supplier, barang pecah"></div>
    <button class="btn-primary w-full">Simpan Perubahan</button>
  </form>

  <div class="lg:col-span-2 card overflow-hidden min-w-0">
    <form method="GET" class="grid sm:grid-cols-2 gap-3 p-4 border-b border-slate-100">
      <select name="product" class="input" onchange="this.form.submit()">
        <option value="">Semua produk</option>
        @foreach ($products as $p)<option value="{{ $p->id }}" @selected(request('product') == $p->id)>{{ $p->name }}</option>@endforeach
      </select>
      <select name="type" class="input" onchange="this.form.submit()">
        <option value="">Semua jenis</option>
        @foreach (\App\Models\StockMovement::TYPES as $k => $v)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $v }}</option>@endforeach
      </select>
    </form>

    {{-- tabel (tablet+) --}}
    <div class="hidden md:block overflow-x-auto p-2">
      <table class="w-full">
        <thead><tr class="border-b border-slate-100"><th class="th">Waktu</th><th class="th">Produk</th><th class="th">Jenis</th><th class="th text-right">Perubahan</th><th class="th text-right">Stok akhir</th><th class="th">Oleh</th></tr></thead>
        <tbody class="divide-y divide-slate-50">
          @forelse ($movements as $m)
            <tr class="hover:bg-slate-50/70 transition-colors">
              <td class="td text-slate-400 whitespace-nowrap">{{ $m->created_at->format('d M, H:i') }}</td>
              <td class="td"><div class="font-semibold">{{ $m->product->name }}</div>@if ($m->note)<div class="text-xs text-slate-400">{{ $m->note }}</div>@endif</td>
              <td class="td"><span class="pill pill-slate">{{ \App\Models\StockMovement::TYPES[$m->type] ?? $m->type }}</span></td>
              <td class="td text-right font-extrabold {{ $m->qty >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $m->qty > 0 ? '+' : '' }}{{ $m->qty }}</td>
              <td class="td text-right">{{ $m->stock_after }}</td>
              <td class="td text-slate-500">{{ $m->user->name ?? 'Sistem' }}</td>
            </tr>
          @empty
            <tr><td colspan="6" class="td text-center text-slate-500 py-10">Belum ada riwayat.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- daftar kartu (mobile) --}}
    <ul class="md:hidden divide-y divide-slate-50 px-4">
      @forelse ($movements as $m)
        <li class="py-3.5 flex items-center gap-3">
          <span class="w-11 h-11 rounded-2xl grid place-items-center shrink-0 font-extrabold text-sm {{ $m->qty >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">{{ $m->qty > 0 ? '+' : '' }}{{ $m->qty }}</span>
          <div class="min-w-0 flex-1">
            <div class="font-bold text-sm truncate">{{ $m->product->name }}</div>
            <div class="text-xs text-slate-400">{{ \App\Models\StockMovement::TYPES[$m->type] ?? $m->type }} · {{ $m->created_at->format('d M, H:i') }} · {{ $m->user->name ?? 'Sistem' }}</div>
            @if ($m->note)<div class="text-xs text-slate-500 truncate">{{ $m->note }}</div>@endif
          </div>
          <div class="text-right shrink-0"><div class="text-[10px] font-bold text-slate-400 uppercase">Stok</div><div class="font-extrabold">{{ $m->stock_after }}</div></div>
        </li>
      @empty
        <li class="py-10 text-center text-sm text-slate-500">Belum ada riwayat.</li>
      @endforelse
    </ul>
    <div class="px-4 py-3 border-t border-slate-100 overflow-x-auto">{{ $movements->links() }}</div>
  </div>
</div>

@push('scripts')
<script>
  const t = document.getElementById('type'), l = document.getElementById('qtyLabel');
  const upd = () => l.textContent = t.value === 'adjustment' ? 'Jumlah stok sebenarnya' : 'Jumlah';
  t.addEventListener('change', upd); upd();
</script>
@endpush
@endsection
