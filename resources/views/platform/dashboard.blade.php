@extends('layouts.platform')
@section('title', 'Ringkasan')
@section('content')
@php
  $pill = ['trial' => 'pill-amber', 'active' => 'pill-green', 'suspended' => 'pill-red'];
  $label = \App\Http\Controllers\Platform\BusinessController::STATUSES;
@endphp
<div class="mb-6">
  <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Ringkasan platform</h1>
  <p class="text-sm font-medium text-slate-500 mt-1">Semua toko di CashierStore, dari sisi pemilik platform.</p>
</div>

@unless ($enforceTrial)
  <div class="mb-5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-sm font-semibold">Penegakan trial sedang nonaktif (<code>POS_ENFORCE_TRIAL=false</code>): toko yang masa trial-nya habis tetap bisa dipakai sampai Anda menangguhkannya di sini.</div>
@endunless

<div class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4 mb-5">
  <div class="card p-5"><div class="label">Toko terdaftar</div><div class="text-3xl font-extrabold">{{ number_format($total, 0, ',', '.') }}</div>
    <div class="text-xs text-slate-500 mt-1">{{ $byStatus['active'] ?? 0 }} aktif · {{ $byStatus['trial'] ?? 0 }} trial · {{ $byStatus['suspended'] ?? 0 }} ditangguhkan</div></div>
  <div class="card p-5"><div class="label">Pendaftar baru</div><div class="text-3xl font-extrabold">{{ $signups7 }}</div><div class="text-xs text-slate-500 mt-1">7 hari terakhir · {{ $signups30 }} dalam 30 hari</div></div>
  <div class="card p-5"><div class="label">Trial perlu ditindak</div><div class="text-3xl font-extrabold">{{ $trialEndingSoon + $trialExpired }}</div>
    <div class="text-xs text-slate-500 mt-1">{{ $trialEndingSoon }} berakhir ≤ 7 hari · {{ $trialExpired }} sudah lewat</div></div>
  <div class="card p-5"><div class="label">Transaksi bulan ini</div><div class="text-3xl font-extrabold">{{ number_format($monthOrders, 0, ',', '.') }}</div>
    <div class="text-xs text-slate-500 mt-1">@rupiah($monthRevenue) · {{ number_format($activeUsers, 0, ',', '.') }} pengguna aktif</div></div>
</div>

<div class="grid lg:grid-cols-3 gap-4 items-start">
  <section class="card p-5" aria-labelledby="h-top">
    <h2 id="h-top" class="font-extrabold mb-3">Toko terbesar bulan ini</h2>
    @forelse ($top as $t)
      <a href="{{ route('platform.businesses.show', $t->id) }}" class="flex items-center justify-between gap-3 py-2.5 border-b border-slate-100 last:border-0 hover:opacity-80">
        <span class="min-w-0"><span class="block text-sm font-bold truncate">{{ $t->name }}</span><span class="text-xs text-slate-500">{{ number_format($t->orders, 0, ',', '.') }} transaksi</span></span>
        <span class="text-sm font-bold whitespace-nowrap">@rupiah($t->revenue)</span>
      </a>
    @empty
      <p class="text-sm text-slate-500 py-6 text-center">Belum ada transaksi bulan ini.</p>
    @endforelse
  </section>

  <section class="card p-5" aria-labelledby="h-trial">
    <h2 id="h-trial" class="font-extrabold mb-3">Trial berakhir berikutnya</h2>
    @forelse ($ending as $b)
      <a href="{{ route('platform.businesses.show', $b) }}" class="flex items-center justify-between gap-3 py-2.5 border-b border-slate-100 last:border-0 hover:opacity-80">
        <span class="min-w-0"><span class="block text-sm font-bold truncate">{{ $b->name }}</span><span class="text-xs text-slate-500">{{ $b->owner_name }}</span></span>
        <span class="pill {{ $b->trial_ends_at->isPast() ? 'pill-red' : 'pill-amber' }}">{{ $b->trial_ends_at->isPast() ? 'Lewat ' . $b->trial_ends_at->diffForHumans(null, true) : $b->trial_ends_at->diffForHumans(null, true) . ' lagi' }}</span>
      </a>
    @empty
      <p class="text-sm text-slate-500 py-6 text-center">Tidak ada toko dalam masa trial.</p>
    @endforelse
  </section>

  <section class="card p-5" aria-labelledby="h-new">
    <h2 id="h-new" class="font-extrabold mb-3">Pendaftar terbaru</h2>
    @forelse ($recent as $b)
      <a href="{{ route('platform.businesses.show', $b) }}" class="flex items-center justify-between gap-3 py-2.5 border-b border-slate-100 last:border-0 hover:opacity-80">
        <span class="min-w-0"><span class="block text-sm font-bold truncate">{{ $b->name }}</span><span class="text-xs text-slate-500">{{ $b->category?->name }} · {{ $b->created_at->translatedFormat('d M Y') }}</span></span>
        <span class="pill {{ $pill[$b->status] ?? 'pill-slate' }}">{{ $label[$b->status] ?? $b->status }}</span>
      </a>
    @empty
      <p class="text-sm text-slate-500 py-6 text-center">Belum ada toko.</p>
    @endforelse
  </section>
</div>
@endsection
