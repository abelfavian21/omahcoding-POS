@extends('layouts.app')
@section('title', 'Shift Kasir')
@section('content')
@php $isAdmin = auth()->user()->isAdmin(); @endphp
<div class="max-w-5xl">
  <h1 class="text-3xl font-extrabold tracking-tight mb-1">Shift Kasir</h1>
  <p class="text-sm text-slate-500 mb-6">Catat uang di laci saat buka dan tutup shift. Selisih kas tercatat otomatis.</p>

  @if ($errors->any())
    <div class="mb-4 rounded-xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm font-semibold">{{ $errors->first() }}</div>
  @endif

  @if ($current)
    <div class="card p-6 mb-6">
      <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
          <span class="pill pill-green">Shift berjalan</span>
          <div class="text-sm text-slate-500 mt-2">Dibuka {{ $current->opened_at->translatedFormat('d M Y H:i') }} · modal awal @rupiah($current->opening_cash)</div>
        </div>
        <a href="{{ route('pos') }}" class="btn-secondary">Ke Kasir</a>
      </div>
      <form method="POST" action="{{ route('shifts.close', $current) }}" class="grid sm:grid-cols-3 gap-4 items-end">
        @csrf
        <div>
          <label class="label">Uang di laci (hitung fisik) *</label>
          <input name="closing_cash" type="number" min="0" step="1" class="input" required inputmode="numeric">
          <p class="text-[11px] text-slate-400 mt-1">Hitung dulu uangnya; sistem membandingkan setelah Anda menutup shift.</p>
        </div>
        <div><label class="label">Catatan</label><input name="note" maxlength="255" class="input"></div>
        <button class="btn-danger" onclick="return confirm('Tutup shift sekarang? Tindakan ini tidak bisa dibatalkan.')">Tutup Shift</button>
      </form>
    </div>
  @else
    <div class="card p-6 mb-6">
      <h2 class="font-bold mb-3">Buka shift baru</h2>
      <form method="POST" action="{{ route('shifts.store') }}" class="grid sm:grid-cols-3 gap-4 items-end">
        @csrf
        <div><label class="label">Modal awal di laci (Rp) *</label><input name="opening_cash" type="number" min="0" step="1" class="input" required inputmode="numeric"></div>
        <div><label class="label">Catatan</label><input name="note" maxlength="255" class="input"></div>
        <button class="btn-primary">Buka Shift</button>
      </form>
    </div>
  @endif

  <div class="card overflow-x-auto">
    <table class="w-full">
      <thead><tr class="border-b border-slate-100">
        <th class="th">Kasir</th><th class="th">Buka</th><th class="th">Tutup</th>
        <th class="th text-right">Modal</th><th class="th text-right">Dihitung</th>
        @if ($isAdmin)<th class="th text-right">Seharusnya</th>@endif
        <th class="th text-right">Selisih</th>
      </tr></thead>
      <tbody class="divide-y divide-slate-50">
        @forelse ($shifts as $s)
          <tr>
            <td class="td">{{ $s->cashier?->name ?? '-' }}</td>
            <td class="td">{{ $s->opened_at->translatedFormat('d M H:i') }}</td>
            <td class="td">{{ $s->closed_at?->translatedFormat('d M H:i') ?? '—' }}</td>
            <td class="td text-right">@rupiah($s->opening_cash)</td>
            <td class="td text-right">{{ $s->closing_cash !== null ? 'Rp ' . number_format($s->closing_cash, 0, ',', '.') : '—' }}</td>
            @if ($isAdmin)<td class="td text-right">{{ $s->expected_cash !== null ? 'Rp ' . number_format($s->expected_cash, 0, ',', '.') : '—' }}</td>@endif
            <td class="td text-right">
              @if ($s->difference === null) —
              @elseif ((float) $s->difference === 0.0) <span class="pill pill-green">Sesuai</span>
              @else <span class="pill {{ $s->difference > 0 ? 'pill-amber' : 'pill-red' }}">{{ $s->difference > 0 ? '+' : '-' }}Rp {{ number_format(abs($s->difference), 0, ',', '.') }}</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td class="td text-center text-slate-500" colspan="7">Belum ada shift.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $shifts->links() }}</div>
</div>
@endsection
