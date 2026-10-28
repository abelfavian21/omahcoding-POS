@extends('layouts.app')
@section('title', 'Riwayat Stok ' . $ingredient->name)
@section('content')
<div data-live-region="page">
<div class="space-y-6 max-w-4xl">
  <div>
    <a href="{{ route('ingredients.index') }}" class="inline-flex items-center gap-1 h-9 px-3 -ml-3 rounded-full text-sm font-semibold text-slate-500 hover:bg-white hover:text-slate-900 transition">
      <span class="material-symbols-outlined" style="font-size:18px">arrow_back</span> Kembali ke Bahan Baku
    </a>
    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-2">Riwayat: {{ $ingredient->name }}</h1>
    <p class="text-sm text-slate-500 mt-1">Stok saat ini: <b class="text-slate-900">{{ number_format($ingredient->stock, 2) }} {{ $ingredient->unit }}</b></p>
  </div>

  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 border-b border-slate-100 text-xs text-slate-500 font-bold uppercase tracking-wider">
          <tr>
            <th class="px-5 py-3">Waktu</th>
            <th class="px-5 py-3">Tipe</th>
            <th class="px-5 py-3">Perubahan</th>
            <th class="px-5 py-3">Sisa Stok</th>
            <th class="px-5 py-3">Keterangan</th>
            <th class="px-5 py-3">Petugas</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse ($movements as $m)
            <tr>
              <td class="px-5 py-3 text-slate-500 whitespace-nowrap text-xs">{{ $m->created_at->format('d/m/Y H:i') }}</td>
              <td class="px-5 py-3">
                @php
                  $badge = match($m->type) {
                    'in' => 'bg-emerald-100 text-emerald-800',
                    'out' => 'bg-red-100 text-red-800',
                    'sale' => 'bg-blue-100 text-blue-800',
                    'void' => 'bg-amber-100 text-amber-800',
                    default => 'bg-slate-100 text-slate-800',
                  };
                  $label = match($m->type) {
                    'in' => 'Masuk',
                    'out' => 'Keluar',
                    'sale' => 'Terpakai (POS)',
                    'void' => 'Batal (Void)',
                    'adjustment' => 'Opname',
                    default => $m->type,
                  };
                @endphp
                <span class="pill {{ $badge }} !text-[11px]">{{ $label }}</span>
              </td>
              <td class="px-5 py-3 font-bold {{ $m->qty > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                {{ $m->qty > 0 ? '+' : '' }}{{ number_format($m->qty, 2) }} {{ $ingredient->unit }}
              </td>
              <td class="px-5 py-3 text-slate-700 font-medium">{{ number_format($m->stock_after, 2) }} {{ $ingredient->unit }}</td>
              <td class="px-5 py-3 text-slate-600 text-xs">{{ $m->note ?: '-' }}</td>
              <td class="px-5 py-3 text-slate-500 text-xs">{{ $m->user?->name ?: 'Sistem' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-5 py-8 text-center text-slate-400">Belum ada riwayat mutasi untuk bahan ini.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if ($movements->hasPages())
      <div class="p-4 border-t border-slate-100">
        {{ $movements->links() }}
      </div>
    @endif
  </div>
</div>
</div>
@endsection
