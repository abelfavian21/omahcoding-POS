@extends('layouts.platform')
@section('title', $business->name)
@section('content')
@php $pill = ['active' => 'pill-green', 'suspended' => 'pill-red']; @endphp
<div class="mb-5">
  <a href="{{ route('platform.businesses.index') }}" class="text-sm font-semibold text-slate-600 hover:underline">‹ Semua toko</a>
  <div class="flex flex-wrap items-center gap-3 mt-1">
    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">{{ $business->name }}</h1>
    <span class="pill {{ $pill[$business->status] ?? 'pill-slate' }}">{{ $statuses[$business->status] ?? $business->status }}</span>
  </div>
  <p class="text-sm font-medium text-slate-500 mt-1">{{ $business->code }} · {{ $business->category?->name }} · terdaftar {{ $business->created_at->translatedFormat('d M Y') }}</p>
</div>

<div class="grid xl:grid-cols-[minmax(0,1fr)_380px] gap-4 items-start">
  <div class="space-y-4 min-w-0">
    <section class="card p-5" aria-labelledby="h-info">
      <h2 id="h-info" class="font-extrabold mb-3">Profil</h2>
      <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
        <div><dt class="label !mb-0">Pemilik</dt><dd class="font-semibold">{{ $business->owner_name }}</dd></div>
        <div><dt class="label !mb-0">Email</dt><dd class="font-semibold break-all">{{ $business->email }}</dd></div>
        <div><dt class="label !mb-0">WhatsApp</dt><dd class="font-semibold">{{ $business->phone ?: '—' }}</dd></div>
        <div><dt class="label !mb-0">Lokasi · skala</dt><dd class="font-semibold">{{ $business->location ?: '—' }} · {{ $business->outlet_count }}</dd></div>
        <div><dt class="label !mb-0">Pajak</dt><dd class="font-semibold">{{ $business->tax_label ?: 'Pajak' }} {{ rtrim(rtrim(number_format($business->taxRate() * 100, 2, ',', ''), '0'), ',') }}%</dd></div>
      </dl>
    </section>

    <section class="grid grid-cols-2 sm:grid-cols-4 gap-3" aria-label="Aktivitas">
      <div class="card p-4"><div class="label">Transaksi (lunas)</div><div class="text-xl sm:text-2xl font-extrabold">{{ number_format($orderCount, 0, ',', '.') }}</div></div>
      <div class="card p-4"><div class="label">Omzet 30 hari</div><div class="text-xl sm:text-2xl font-extrabold">@rupiah($revenue30)</div></div>
      <div class="card p-4"><div class="label">Omzet total</div><div class="text-xl sm:text-2xl font-extrabold">@rupiah($revenueAll)</div></div>
      <div class="card p-4"><div class="label">Transaksi terakhir</div><div class="text-base font-extrabold">{{ $lastOrderAt ? \Illuminate\Support\Carbon::parse($lastOrderAt)->translatedFormat('d M Y H:i') : 'Belum ada' }}</div></div>
    </section>

    <section class="card overflow-hidden" aria-labelledby="h-staff">
      <h2 id="h-staff" class="font-extrabold p-5 pb-2">Pengguna toko ({{ $users->count() }})</h2>
      <div class="overflow-x-auto px-2 pb-2">
        <table class="w-full">
          <thead><tr class="border-b border-slate-100"><th class="th">Nama</th><th class="th">Peran</th><th class="th">Status</th><th class="th">Login terakhir</th></tr></thead>
          <tbody class="divide-y divide-slate-50">
            @forelse ($users as $u)
              <tr><td class="td"><div class="font-semibold">{{ $u->name }}</div><div class="text-xs text-slate-500">{{ $u->email }}</div></td>
                <td class="td capitalize">{{ $u->role }}</td>
                <td class="td"><span class="pill {{ $u->is_active ? 'pill-green' : 'pill-slate' }}">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                <td class="td text-xs whitespace-nowrap">{{ $u->last_login_at ? \Illuminate\Support\Carbon::parse($u->last_login_at)->translatedFormat('d M Y H:i') : '—' }}</td></tr>
            @empty
              <tr><td class="td text-center text-slate-500 py-8" colspan="4">Belum ada pengguna.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  </div>

  <aside class="space-y-4 min-w-0">
    <section class="card p-5" aria-labelledby="h-act">
      <h2 id="h-act" class="font-extrabold mb-3">Tindakan</h2>

      @if ($business->status === 'suspended')
        <p class="text-sm text-slate-500 mb-3">Toko ini ditangguhkan. Pengguna tokonya tidak bisa masuk.</p>
        <form method="POST" action="{{ route('platform.businesses.status', $business) }}" onsubmit="return confirm('Aktifkan kembali {{ addslashes($business->name) }}?')">
          @csrf<input type="hidden" name="action" value="activate">
          <button class="btn-primary w-full">Aktifkan kembali</button>
        </form>
      @else
        <form method="POST" action="{{ route('platform.businesses.status', $business) }}" onsubmit="return confirm('Tangguhkan {{ addslashes($business->name) }}? Semua penggunanya langsung terkunci.')">
          @csrf<input type="hidden" name="action" value="suspend">
          <label for="reason" class="label">Alasan penangguhan (wajib)</label>
          <textarea id="reason" name="reason" rows="2" maxlength="200" required minlength="5" class="input" placeholder="Contoh: pelanggaran ketentuan layanan"></textarea>
          <button class="btn-danger w-full mt-2">Tangguhkan toko</button>
        </form>
      @endif
    </section>

    <section class="card p-5" aria-labelledby="h-log">
      <h2 id="h-log" class="font-extrabold mb-3">Riwayat tindakan platform</h2>
      @forelse ($log as $l)
        <div class="py-2.5 border-b border-slate-100 last:border-0">
          <div class="flex items-center justify-between gap-2"><span class="pill pill-slate">{{ $l->action }}</span><span class="text-[11px] text-slate-400">{{ $l->created_at?->translatedFormat('d M H:i') }}</span></div>
          <div class="text-xs text-slate-500 mt-1">{{ $l->admin?->name ?? '—' }}@if ($l->meta) · {{ json_encode($l->meta, JSON_UNESCAPED_UNICODE) }}@endif</div>
        </div>
      @empty
        <p class="text-sm text-slate-500">Belum ada tindakan.</p>
      @endforelse
    </section>
  </aside>
</div>
@endsection
