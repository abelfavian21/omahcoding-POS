@extends('layouts.app')
@section('title', 'Pelanggan')
@section('content')
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
@endphp

<div class="flex flex-wrap items-end justify-between gap-4 mb-6">
  <div>
    <div class="text-xs text-slate-500 mb-1">Dashboard › <span class="text-brand-600 font-semibold">Pelanggan</span></div>
    <h1 class="text-3xl font-extrabold tracking-tight">Manajemen Pelanggan</h1>
    <p class="text-sm text-slate-500">Kelola member loyalty, riwayat kunjungan, dan profil tamu.</p>
  </div>
  <a href="{{ route('customers.create') }}" class="btn-primary"><span class="material-symbols-outlined">person_add</span> Tambah Pelanggan</a>
</div>

<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-5">
  <div class="card p-5"><div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Pelanggan</div>
    <div class="text-3xl font-extrabold mt-2">{{ number_format($stats['total'], 0, ',', '.') }}</div><div class="text-xs text-slate-500 mt-2">● {{ $stats['newWeek'] }} baru minggu ini</div></div>
  <div class="card p-5"><div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Member Gold & VIP</div>
    <div class="text-3xl font-extrabold mt-2">{{ $stats['vip'] }}</div>
    <div class="h-1.5 bg-slate-100 rounded-full mt-3"><div class="h-full bg-amber-400 rounded-full" style="width: {{ $stats['total'] ? round($stats['vip'] / $stats['total'] * 100) : 0 }}%"></div></div></div>
  <div class="card p-5"><div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Rata-rata LTV</div>
    <div class="text-2xl font-extrabold mt-2">@rupiah($stats['ltv'])</div><div class="text-xs text-slate-500 mt-2">Belanja rata-rata per pelanggan</div></div>
  <div class="card p-5"><div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Repeat Customer</div>
    <div class="text-3xl font-extrabold mt-2">{{ $stats['repeat'] }}%</div><div class="text-xs text-slate-500 mt-2">Pelanggan dengan &gt; 1 kunjungan</div></div>
</div>

<div class="card p-4 mb-5 flex flex-wrap items-center gap-3">
  <form method="GET" class="flex-1 min-w-[220px]"><input name="q" value="{{ request('q') }}" class="input" placeholder="Cari nama, telepon, email, kode…"></form>
  <div class="flex flex-wrap gap-2">
    @foreach ($filters as $f)
      @php $on = request()->only(['tier', 'filter']) == $f['q']; @endphp
      <a href="{{ route('customers.index', $f['q']) }}" class="px-4 h-10 inline-flex items-center rounded-full text-sm font-semibold {{ $on ? 'bg-brand-600 text-white' : 'bg-brand-50 text-brand-700 hover:bg-brand-100' }}">{{ $f['label'] }}{{ $f['count'] !== null ? " ({$f['count']})" : '' }}</a>
    @endforeach
  </div>
</div>

<div class="grid xl:grid-cols-3 gap-5">
  <div class="xl:col-span-2 card overflow-hidden">
    <div class="px-5 py-4 flex items-center gap-2"><h2 class="font-bold text-lg">Direktori Member</h2><span class="pill pill-slate">{{ $customers->count() }} dari {{ $customers->total() }}</span></div>
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-brand-50/50"><tr><th class="th">Pelanggan</th><th class="th">Kunjungan</th><th class="th">Belanja</th><th class="th">Poin</th>@if ($admin)<th class="th"></th>@endif</tr></thead>
        <tbody class="divide-y divide-slate-50">
          @forelse ($customers as $c)
            <tr class="{{ $selected?->id === $c->id ? 'bg-brand-50/60' : 'hover:bg-slate-50/60' }}">
              <td class="td">
                <a href="{{ route('customers.index', array_merge(request()->query(), ['selected' => $c->id])) }}" class="flex items-center gap-3">
                  <span class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 grid place-items-center text-sm font-bold shrink-0">{{ $c->initials }}</span>
                  <span><b>{{ $c->name }}</b> <span class="pill {{ $tierPill[$c->tier] }} !py-0 !text-[10px]">{{ $c->tier }}</span>
                    <span class="block text-xs text-slate-500">{{ $c->phone ?: $c->email }}</span></span>
                </a>
              </td>
              <td class="td"><div class="font-semibold">{{ $c->visits }} kunjungan</div><div class="text-xs text-slate-500">{{ $c->last_visit_at?->diffForHumans() ?? '—' }}</div></td>
              <td class="td"><div class="font-semibold">@rupiah($c->total_spent)</div><div class="text-xs text-slate-500">Rata-rata @rupiah($c->avg_spend)</div></td>
              <td class="td"><span class="pill pill-amber">{{ number_format($c->points) }} pts</span></td>
              @if ($admin)
                <td class="td text-right whitespace-nowrap">
                  <form method="POST" action="{{ route('customers.destroy', $c) }}" class="inline" onsubmit="return confirm('Hapus pelanggan {{ addslashes($c->name) }}?')">
                    @csrf @method('DELETE')<button class="btn-ghost !h-9 !px-3 text-red-600"><span class="material-symbols-outlined">delete</span></button>
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
    <div class="px-4 py-3 border-t border-slate-100">{{ $customers->links() }}</div>
  </div>

  {{-- Panel member terpilih --}}
  <div class="space-y-5">
    @if ($selected)
      <div class="card overflow-hidden">
        <div class="h-16 bg-gradient-to-r from-brand-600 to-brand-800 px-5 pt-3 flex justify-between text-white text-xs font-bold">
          <span>★ MEMBER TERPILIH</span><span class="bg-white/20 rounded-full px-2 h-5 grid place-items-center">{{ $selected->code }}</span>
        </div>
        <div class="px-5 pb-5 -mt-8">
          <div class="flex items-end justify-between">
            <div class="w-16 h-16 rounded-2xl bg-brand-100 text-brand-700 border-4 border-white grid place-items-center text-xl font-extrabold">{{ $selected->initials }}</div>
            <a href="{{ route('customers.edit', $selected) }}" class="btn-ghost !h-9 !px-3"><span class="material-symbols-outlined">edit</span></a>
          </div>
          <div class="mt-2 text-xl font-extrabold">{{ $selected->name }} <span class="pill {{ $tierPill[$selected->tier] }}">{{ $selected->tier }}</span></div>
          <div class="text-xs text-slate-500">Member sejak {{ $selected->created_at->translatedFormat('M Y') }}</div>

          <div class="bg-slate-50 rounded-xl p-3 mt-4 space-y-1.5 text-sm">
            <div class="flex gap-2"><span class="material-symbols-outlined text-brand-600">mail</span>{{ $selected->email ?: '—' }}</div>
            <div class="flex gap-2"><span class="material-symbols-outlined text-brand-600">call</span>{{ $selected->phone ?: '—' }}</div>
          </div>
          <div class="grid grid-cols-3 gap-2 mt-3 text-center">
            <div class="bg-brand-50 rounded-xl p-2"><div class="text-[10px] font-bold text-slate-500">LIFETIME</div><div class="font-extrabold text-sm">@rupiah($selected->total_spent)</div></div>
            <div class="bg-brand-50 rounded-xl p-2"><div class="text-[10px] font-bold text-slate-500">KUNJUNGAN</div><div class="font-extrabold">{{ $selected->visits }}×</div></div>
            <div class="bg-amber-50 rounded-xl p-2"><div class="text-[10px] font-bold text-slate-500">POIN</div><div class="font-extrabold text-amber-700">{{ number_format($selected->points) }}</div></div>
          </div>

          @if ($selected->notes || $selected->allergy)
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mt-4 mb-2">Catatan barista & diet</div>
            <div class="bg-brand-50/60 rounded-xl p-3 space-y-2 text-sm">
              @if ($selected->notes)<div class="flex gap-2"><span class="material-symbols-outlined text-amber-500">favorite</span>{{ $selected->notes }}</div>@endif
              @if ($selected->allergy)<div class="flex gap-2 text-red-600 font-semibold"><span class="material-symbols-outlined">warning</span>{{ $selected->allergy }}</div>@endif
            </div>
          @endif

          <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mt-4 mb-2">Riwayat kunjungan</div>
          <div class="space-y-2">
            @forelse ($recent as $o)
              <div class="bg-slate-50 rounded-xl p-3 text-sm">
                <div class="flex justify-between"><span class="font-semibold">{{ $o->paid_at?->format('d M Y, H:i') }}</span><b class="text-brand-600">@rupiah($o->total)</b></div>
                <div class="text-xs text-slate-500 truncate">{{ $o->items->map(fn ($i) => $i->qty . '× ' . $i->product_name)->implode(', ') }}</div>
              </div>
            @empty
              <p class="text-sm text-slate-500">Belum ada transaksi.</p>
            @endforelse
          </div>
        </div>
      </div>
    @endif

    <div class="card p-4 flex gap-3 items-start bg-amber-50/50">
      <span class="material-symbols-outlined text-amber-600">cake</span>
      <div><div class="font-bold">Ulang tahun minggu ini</div><div class="text-sm text-slate-600">{{ $birthdays }} pelanggan berulang tahun dalam 7 hari ke depan.</div></div>
    </div>
  </div>
</div>
@endsection
