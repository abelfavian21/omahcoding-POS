@extends('layouts.app')
@section('title', 'Shift Kasir')
@section('content')
<div data-live-region="page">
@php $isAdmin = auth()->user()->isAdmin(); @endphp
<div class="max-w-5xl">
  <div class="text-xs text-slate-400 font-semibold mb-1">Dashboard › <span class="text-slate-700">Shift Kasir</span></div>
  <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Shift Kasir</h1>
  <p class="text-sm font-medium text-slate-500 mt-1 mb-6">Catat uang di laci saat buka dan tutup shift. Selisih kas tercatat otomatis.</p>

  @if ($errors->any())
    <div class="mb-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm font-semibold">{{ $errors->first() }}</div>
  @endif

  @if ($current)
    <div class="card-dark p-5 sm:p-6 mb-6">
      <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-lime-accent/15 text-lime-accent text-[10px] font-bold uppercase tracking-wide"><span class="w-1.5 h-1.5 rounded-full bg-lime-accent animate-pulse motion-reduce:animate-none"></span> Shift berjalan</span>
          <div class="text-sm text-slate-300 mt-2.5">Dibuka {{ $current->opened_at->translatedFormat('d M Y H:i') }} · modal awal <b class="text-white">@rupiah($current->opening_cash)</b></div>
        </div>
        <a href="{{ route('pos') }}" class="btn bg-lime-accent text-slate-950 hover:bg-[#DCFA5C]">Ke Kasir <span class="material-symbols-outlined">arrow_forward</span></a>
      </div>
      <form method="POST" action="{{ route('shifts.close', $current) }}" class="grid sm:grid-cols-3 gap-4 items-end">
        @csrf
        <div>
          <label class="label !text-slate-300">Uang di laci (hitung fisik) *</label>
          <input name="closing_cash" type="number" min="0" step="1" class="input !bg-white/5 !border-white/15 !text-white" required inputmode="numeric">
          <p class="text-[11px] text-slate-400 mt-1">Hitung dulu uangnya; sistem membandingkan setelah Anda menutup shift.</p>
        </div>
        <div><label class="label !text-slate-300">Catatan</label><input name="note" maxlength="255" class="input !bg-white/5 !border-white/15 !text-white"></div>
        <button class="btn bg-red-500/15 text-red-300 border border-red-400/30 hover:bg-red-500/25" onclick="return confirm('Tutup shift sekarang? Tindakan ini tidak bisa dibatalkan.')">Tutup Shift</button>
      </form>
    </div>
  @else
    <div class="card p-5 sm:p-6 mb-6">
      <div class="flex items-center gap-3 mb-4">
        <span class="w-10 h-10 rounded-xl bg-[#181829] text-lime-accent grid place-items-center"><span class="material-symbols-outlined">lock_open</span></span>
        <h2 class="font-extrabold text-base">Buka shift baru</h2>
      </div>
      <form method="POST" action="{{ route('shifts.store') }}" class="grid sm:grid-cols-3 gap-4 items-end">
        @csrf
        <div><label class="label">Modal awal di laci (Rp) *</label><input name="opening_cash" type="number" min="0" step="1" class="input" required inputmode="numeric"></div>
        <div><label class="label">Catatan</label><input name="note" maxlength="255" class="input"></div>
        <button class="btn-primary">Buka Shift</button>
      </form>
    </div>
  @endif

  <div class="card overflow-hidden">
    <div class="px-5 sm:px-6 pt-5 pb-3"><h2 class="font-extrabold text-base">Riwayat shift</h2></div>

    {{-- tabel (tablet+) --}}
    <div class="hidden md:block overflow-x-auto px-2 pb-2">
      <table class="w-full">
        <thead><tr class="border-b border-slate-100">
          <th class="th">Kasir</th><th class="th">Buka</th><th class="th">Tutup</th>
          <th class="th text-right">Modal</th><th class="th text-right">Dihitung</th>
          @if ($isAdmin)<th class="th text-right">Seharusnya</th>@endif
          <th class="th text-right">Selisih</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-50">
          @forelse ($shifts as $s)
            <tr class="hover:bg-slate-50/70 transition-colors">
              <td class="td font-semibold">{{ $s->cashier?->name ?? '-' }}</td>
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
            <tr><td class="td text-center text-slate-500 py-10" colspan="7">Belum ada shift.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- daftar kartu (mobile) --}}
    <ul class="md:hidden divide-y divide-slate-50 px-4">
      @forelse ($shifts as $s)
        <li class="py-3.5">
          <div class="flex items-center justify-between gap-2">
            <b class="truncate">{{ $s->cashier?->name ?? '-' }}</b>
            @if ($s->difference === null) <span class="pill pill-slate">Berjalan</span>
            @elseif ((float) $s->difference === 0.0) <span class="pill pill-green">Sesuai</span>
            @else <span class="pill {{ $s->difference > 0 ? 'pill-amber' : 'pill-red' }}">{{ $s->difference > 0 ? '+' : '-' }}Rp {{ number_format(abs($s->difference), 0, ',', '.') }}</span>
            @endif
          </div>
          <div class="text-xs text-slate-400 mt-0.5">{{ $s->opened_at->translatedFormat('d M H:i') }} → {{ $s->closed_at?->translatedFormat('d M H:i') ?? '—' }}</div>
          <div class="grid grid-cols-{{ $isAdmin ? 3 : 2 }} gap-2 mt-2.5 text-center">
            <div class="bg-slate-50 rounded-xl p-2"><div class="text-[10px] font-bold text-slate-400 uppercase">Modal</div><div class="text-xs font-extrabold">@rupiah($s->opening_cash)</div></div>
            <div class="bg-slate-50 rounded-xl p-2"><div class="text-[10px] font-bold text-slate-400 uppercase">Dihitung</div><div class="text-xs font-extrabold">{{ $s->closing_cash !== null ? 'Rp ' . number_format($s->closing_cash, 0, ',', '.') : '—' }}</div></div>
            @if ($isAdmin)<div class="bg-slate-50 rounded-xl p-2"><div class="text-[10px] font-bold text-slate-400 uppercase">Seharusnya</div><div class="text-xs font-extrabold">{{ $s->expected_cash !== null ? 'Rp ' . number_format($s->expected_cash, 0, ',', '.') : '—' }}</div></div>@endif
          </div>
        </li>
      @empty
        <li class="py-10 text-center text-sm text-slate-500">Belum ada shift.</li>
      @endforelse
    </ul>
  </div>
  <div class="mt-4 overflow-x-auto">{{ $shifts->links() }}</div>
</div>
</div>
@endsection
