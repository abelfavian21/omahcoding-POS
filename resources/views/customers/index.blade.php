@extends('layouts.app')
@section('title', 'Pelanggan')
@section('content')
<div data-live-region="page">
@php
  $admin = auth()->user()->isAdmin();
  $tierPill = ['bronze' => 'pill-slate', 'silver' => 'pill-blue', 'gold' => 'pill-amber', 'vip' => 'pill-green'];
  $filters = [
    ['label' => 'Semua', 'q' => [], 'count' => $stats['total']],
    ['label' => 'VIP', 'q' => ['tier' => 'vip'], 'count' => $tierCounts['vip'] ?? 0],
    ['label' => 'Gold', 'q' => ['tier' => 'gold'], 'count' => $tierCounts['gold'] ?? 0],
    ['label' => 'Silver', 'q' => ['tier' => 'silver'], 'count' => $tierCounts['silver'] ?? 0],
    ['label' => 'Aktif minggu ini', 'q' => ['filter' => 'active'], 'count' => null],
    ['label' => 'Tidak aktif', 'q' => ['filter' => 'inactive'], 'count' => null],
  ];
  $lbl = 'text-xs font-bold text-slate-400 tracking-wide uppercase';
  $num = 'text-[28px] leading-[34px] font-extrabold tracking-[-0.03em]';
@endphp

<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
  <div>
    <div class="text-xs text-slate-400 font-semibold mb-1">Dashboard › <span class="text-slate-700">Pelanggan</span></div>
    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Manajemen Pelanggan</h1>
    <p class="text-sm font-medium text-slate-500 mt-1">Kelola member loyalty, riwayat kunjungan, dan profil tamu.</p>
  </div>
  <a href="{{ route('customers.create') }}" class="btn-primary self-start sm:self-auto"><span class="material-symbols-outlined">person_add</span> Tambah Pelanggan</a>
</div>

{{-- KPI --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-5 mb-5">
  <div class="card-dark p-4 sm:p-6">
    <div class="flex items-start justify-between"><span class="text-xs font-bold text-slate-400 tracking-wide uppercase">Total pelanggan</span>
      <span class="w-8 h-8 rounded-xl bg-lime-accent text-slate-950 grid place-items-center shrink-0"><span class="material-symbols-outlined fill-1" style="font-size:18px">group</span></span></div>
    <div class="{{ $num }} mt-2">{{ number_format($stats['total'], 0, ',', '.') }}</div>
    <div class="mt-3 inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-lime-accent/15 text-lime-accent font-bold text-xs">+{{ $stats['newWeek'] }} baru minggu ini</div>
  </div>
  <div class="card p-4 sm:p-6">
    <span class="{{ $lbl }}">Member Gold &amp; VIP</span>
    <div class="{{ $num }} mt-2">{{ $stats['vip'] }}</div>
    <div class="h-2 bg-slate-100 rounded-full mt-4 overflow-hidden"><div class="h-full bg-lime-accent rounded-full" style="width: {{ $stats['total'] ? round($stats['vip'] / $stats['total'] * 100) : 0 }}%"></div></div>
  </div>
  <div class="card p-4 sm:p-6">
    <span class="{{ $lbl }}">Rata-rata LTV</span>
    <div class="text-2xl sm:text-[28px] leading-[34px] font-extrabold tracking-[-0.03em] mt-2 break-all">@rupiah($stats['ltv'])</div>
    <div class="text-xs font-medium text-slate-400 mt-3">Belanja rata-rata per pelanggan</div>
  </div>
  <div class="card p-4 sm:p-6">
    <span class="{{ $lbl }}">Repeat customer</span>
    <div class="{{ $num }} mt-2">{{ $stats['repeat'] }}<span class="text-xl text-slate-400 font-semibold">%</span></div>
    <div class="text-xs font-medium text-slate-400 mt-3">Pelanggan dengan &gt; 1 kunjungan</div>
  </div>
</div>

{{-- Cari + filter --}}
<div class="card p-4 mb-5 space-y-3 lg:space-y-0 lg:flex lg:items-center lg:gap-4">
  <form method="GET" class="relative lg:w-80 shrink-0">
    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:19px">search</span>
    <input name="q" value="{{ request('q') }}" class="input input-pill" placeholder="Cari nama, telepon, email, kode…">
  </form>
  <div class="flex gap-2 overflow-x-auto no-scrollbar -mx-4 px-4 lg:mx-0 lg:px-0 lg:flex-wrap">
    @foreach ($filters as $f)
      @php $on = request()->only(['tier', 'filter']) == $f['q']; @endphp
      <a href="{{ route('customers.index', $f['q']) }}" class="chip {{ $on ? 'chip-on' : '' }}">{{ $f['label'] }}{{ $f['count'] !== null ? " ({$f['count']})" : '' }}</a>
    @endforeach
  </div>
</div>

<div class="grid xl:grid-cols-3 gap-5">
  <div class="xl:col-span-2 card overflow-hidden min-w-0">
    <div class="px-5 sm:px-6 pt-5 pb-3 flex items-center gap-2"><h2 class="font-extrabold text-base">Direktori Member</h2><span class="pill pill-slate">{{ $customers->count() }} dari {{ $customers->total() }}</span></div>

    {{-- tabel (tablet+) --}}
    <div class="hidden md:block overflow-x-auto px-2">
      <table class="w-full">
        <thead><tr class="border-b border-slate-100"><th class="th">Pelanggan</th><th class="th">Kunjungan</th><th class="th">Belanja</th><th class="th">Poin</th>@if ($admin)<th class="th"></th>@endif</tr></thead>
        <tbody class="divide-y divide-slate-50">
          @forelse ($customers as $c)
            <tr class="{{ $selected?->id === $c->id ? 'bg-lime-subtle dark:bg-lime-accent/5' : 'hover:bg-slate-50/70' }} transition-colors">
              <td class="td">
                <a href="{{ route('customers.index', array_merge(request()->query(), ['selected' => $c->id])) }}" class="flex items-center gap-3">
                  <span class="w-10 h-10 rounded-full bg-[#181829] text-lime-accent grid place-items-center text-sm font-bold shrink-0">{{ $c->initials }}</span>
                  <span><b>{{ $c->name }}</b> <span class="pill {{ $tierPill[$c->tier] }} !py-0 !text-[10px]">{{ $c->tier }}</span>
                    <span class="block text-xs text-slate-400">{{ $c->phone ?: $c->email }}</span></span>
                </a>
              </td>
              <td class="td"><div class="font-semibold">{{ $c->visits }} kunjungan</div><div class="text-xs text-slate-400">{{ $c->last_visit_at?->diffForHumans() ?? '—' }}</div></td>
              <td class="td"><div class="font-semibold">@rupiah($c->total_spent)</div><div class="text-xs text-slate-400">Rata-rata @rupiah($c->avg_spend)</div></td>
              <td class="td"><span class="pill pill-amber">{{ number_format($c->points) }} pts</span></td>
              @if ($admin)
                <td class="td text-right whitespace-nowrap">
                  <form method="POST" action="{{ route('customers.destroy', $c) }}" class="inline" onsubmit="return confirm('Hapus pelanggan {{ addslashes($c->name) }}?')">
                    @csrf @method('DELETE')<button class="btn-ghost !h-9 !px-3 text-red-600" aria-label="Hapus"><span class="material-symbols-outlined">delete</span></button>
                  </form>
                </td>
              @endif
            </tr>
          @empty
            <tr><td colspan="5" class="td text-center text-slate-500 py-10">Pelanggan tidak ditemukan.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- daftar kartu (mobile) --}}
    <ul class="md:hidden divide-y divide-slate-50 px-5">
      @forelse ($customers as $c)
        <li class="py-3.5 flex items-center gap-3 {{ $selected?->id === $c->id ? 'bg-lime-subtle dark:bg-lime-accent/5 -mx-5 px-5' : '' }}">
          <a href="{{ route('customers.index', array_merge(request()->query(), ['selected' => $c->id])) }}" class="flex items-center gap-3 min-w-0 flex-1">
            <span class="w-11 h-11 rounded-full bg-[#181829] text-lime-accent grid place-items-center text-sm font-bold shrink-0">{{ $c->initials }}</span>
            <span class="min-w-0 flex-1">
              <span class="flex items-center gap-1.5"><b class="truncate">{{ $c->name }}</b><span class="pill {{ $tierPill[$c->tier] }} !py-0 !text-[9px]">{{ $c->tier }}</span></span>
              <span class="block text-xs text-slate-400 truncate">{{ $c->phone ?: $c->email }}</span>
              <span class="block text-xs text-slate-500 mt-0.5">{{ $c->visits }}× · @rupiah($c->total_spent) · {{ number_format($c->points) }} pts</span>
            </span>
          </a>
          @if ($admin)
            <form method="POST" action="{{ route('customers.destroy', $c) }}" onsubmit="return confirm('Hapus pelanggan {{ addslashes($c->name) }}?')">
              @csrf @method('DELETE')<button class="btn-ghost !h-10 !w-10 !px-0 text-red-600" aria-label="Hapus"><span class="material-symbols-outlined">delete</span></button>
            </form>
          @endif
        </li>
      @empty
        <li class="py-10 text-center text-sm text-slate-500">Pelanggan tidak ditemukan.</li>
      @endforelse
    </ul>
    <div class="px-4 py-3 mt-1 border-t border-slate-100 overflow-x-auto">{{ $customers->links() }}</div>
  </div>

  {{-- Panel member terpilih: di mobile tampil paling atas agar langsung terlihat --}}
  <div class="space-y-5 min-w-0 {{ $selected ? 'order-first xl:order-none' : '' }}">
    @if ($selected)
      <div class="card overflow-hidden">
        <div class="h-20 bg-[#181829] px-5 pt-4 flex justify-between text-lime-accent text-xs font-bold">
          <span class="flex items-center gap-1"><span class="material-symbols-outlined fill-1" style="font-size:16px">star</span> MEMBER TERPILIH</span><span class="bg-white/10 rounded-full px-2.5 h-6 grid place-items-center text-white">{{ $selected->code }}</span>
        </div>
        <div class="px-5 pb-5 -mt-8">
          <div class="flex items-end justify-between">
            <div class="w-16 h-16 rounded-2xl bg-lime-accent text-slate-950 border-4 border-white grid place-items-center text-xl font-extrabold">{{ $selected->initials }}</div>
            <a href="{{ route('customers.edit', $selected) }}" class="btn-secondary !h-10 !px-4" aria-label="Edit"><span class="material-symbols-outlined">edit</span> Edit</a>
          </div>
          <div class="mt-3 text-xl font-extrabold flex flex-wrap items-center gap-2">{{ $selected->name }} <span class="pill {{ $tierPill[$selected->tier] }}">{{ $selected->tier }}</span></div>
          <div class="text-xs text-slate-400 font-medium">Member sejak {{ $selected->created_at->translatedFormat('M Y') }}</div>

          <div class="bg-slate-50 rounded-2xl p-3.5 mt-4 space-y-2 text-sm">
            <div class="flex gap-2.5 items-center min-w-0"><span class="material-symbols-outlined text-slate-400">mail</span><span class="truncate">{{ $selected->email ?: '—' }}</span></div>
            <div class="flex gap-2.5 items-center"><span class="material-symbols-outlined text-slate-400">call</span>{{ $selected->phone ?: '—' }}</div>
          </div>
          <div class="grid grid-cols-3 gap-2 mt-3 text-center">
            <div class="bg-slate-50 rounded-2xl p-2.5"><div class="text-[10px] font-bold text-slate-400">LIFETIME</div><div class="font-extrabold text-sm break-all">@rupiah($selected->total_spent)</div></div>
            <div class="bg-slate-50 rounded-2xl p-2.5"><div class="text-[10px] font-bold text-slate-400">KUNJUNGAN</div><div class="font-extrabold">{{ $selected->visits }}×</div></div>
            <div class="bg-lime-subtle dark:bg-lime-accent/10 rounded-2xl p-2.5"><div class="text-[10px] font-bold text-slate-500">POIN</div><div class="font-extrabold text-lime-900 dark:text-lime-accent">{{ number_format($selected->points) }}</div></div>
          </div>

          @if ($selected->notes || $selected->allergy)
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mt-5 mb-2">Catatan barista &amp; diet</div>
            <div class="bg-slate-50 rounded-2xl p-3.5 space-y-2 text-sm">
              @if ($selected->notes)<div class="flex gap-2"><span class="material-symbols-outlined text-amber-500">favorite</span>{{ $selected->notes }}</div>@endif
              @if ($selected->allergy)<div class="flex gap-2 text-red-600 font-semibold"><span class="material-symbols-outlined">warning</span>{{ $selected->allergy }}</div>@endif
            </div>
          @endif

          <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mt-5 mb-2">Riwayat kunjungan</div>
          <div class="space-y-2">
            @forelse ($recent as $o)
              <div class="bg-slate-50 rounded-2xl p-3.5 text-sm">
                <div class="flex justify-between gap-2"><span class="font-semibold">{{ $o->paid_at?->format('d M Y, H:i') }}</span><b>@rupiah($o->total)</b></div>
                <div class="text-xs text-slate-400 truncate">{{ $o->items->map(fn ($i) => $i->qty . '× ' . $i->product_name)->implode(', ') }}</div>
              </div>
            @empty
              <p class="text-sm text-slate-500">Belum ada transaksi.</p>
            @endforelse
          </div>
        </div>
      </div>
    @endif

    <div class="card p-4 sm:p-5 flex gap-3 items-start">
      <span class="w-10 h-10 rounded-xl bg-lime-subtle dark:bg-lime-accent/10 text-lime-900 dark:text-lime-accent grid place-items-center shrink-0"><span class="material-symbols-outlined">cake</span></span>
      <div><div class="font-bold">Ulang tahun minggu ini</div><div class="text-sm text-slate-500">{{ $birthdays }} pelanggan berulang tahun dalam 7 hari ke depan.</div></div>
    </div>
  </div>
</div>
</div>
@endsection
