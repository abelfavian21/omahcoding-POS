@extends('layouts.app')
@section('title', 'Stok Barang')
@section('content')
<div class="mb-6">
  <div class="text-xs text-slate-500 mb-1">Dashboard › <span class="text-brand-600 font-semibold">Stok Barang</span></div>
  <h1 class="text-3xl font-extrabold tracking-tight">Stok Barang</h1>
  <p class="text-sm text-slate-500">Catat barang masuk, keluar, dan stok opname. Setiap perubahan tersimpan di riwayat.</p>
</div>

<div class="grid lg:grid-cols-3 gap-5">
  <form method="POST" action="{{ route('stock.store') }}" class="card p-5 space-y-4 h-fit">
    @csrf
    <h2 class="font-bold text-lg">Ubah Stok</h2>
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
      <input name="qty" type="number" min="0" class="input" value="{{ old('qty') }}" required>
      @error('qty')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
    <div><label class="label">Catatan</label>
      <input name="note" class="input" value="{{ old('note') }}" placeholder="mis. Belanja supplier, barang pecah"></div>
    <button class="btn-primary w-full">Simpan Perubahan</button>
  </form>

  <div class="lg:col-span-2 card overflow-hidden">
    <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-slate-100">
      <select name="product" class="input !w-auto min-w-[180px]" onchange="this.form.submit()">
        <option value="">Semua produk</option>
        @foreach ($products as $p)<option value="{{ $p->id }}" @selected(request('product') == $p->id)>{{ $p->name }}</option>@endforeach
      </select>
      <select name="type" class="input !w-auto" onchange="this.form.submit()">
        <option value="">Semua jenis</option>
        @foreach (\App\Models\StockMovement::TYPES as $k => $v)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $v }}</option>@endforeach
      </select>
    </form>
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-brand-50/50"><tr><th class="th">Waktu</th><th class="th">Produk</th><th class="th">Jenis</th><th class="th text-right">Perubahan</th><th class="th text-right">Stok akhir</th><th class="th">Oleh</th></tr></thead>
        <tbody class="divide-y divide-slate-50">
          @forelse ($movements as $m)
            <tr>
              <td class="td text-slate-500 whitespace-nowrap">{{ $m->created_at->format('d M, H:i') }}</td>
              <td class="td"><div class="font-semibold">{{ $m->product->name }}</div>@if ($m->note)<div class="text-xs text-slate-500">{{ $m->note }}</div>@endif</td>
              <td class="td"><span class="pill pill-slate">{{ \App\Models\StockMovement::TYPES[$m->type] ?? $m->type }}</span></td>
              <td class="td text-right font-bold {{ $m->qty >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $m->qty > 0 ? '+' : '' }}{{ $m->qty }}</td>
              <td class="td text-right">{{ $m->stock_after }}</td>
              <td class="td text-slate-500">{{ $m->user->name ?? 'Sistem' }}</td>
            </tr>
          @empty
            <tr><td colspan="6" class="td text-center text-slate-500 py-10">Belum ada riwayat.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-100">{{ $movements->links() }}</div>
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
