@extends('layouts.app')
@section('title', 'Audit Log')
@section('content')
<div class="max-w-6xl">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-5">
    <div>
      <div class="text-xs text-slate-400 font-semibold mb-1">Dashboard › <span class="text-slate-700">Audit Log</span></div>
      <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Audit Log</h1>
      <p class="text-sm font-medium text-slate-500 mt-1">Jejak aktivitas penting. Catatan ini tidak bisa diubah atau dihapus dari aplikasi.</p>
    </div>
    <form method="GET" class="w-full sm:w-auto">
      <select name="action" class="input sm:!w-60" onchange="this.form.submit()">
        <option value="">Semua aktivitas</option>
        @foreach (['login.failed' => 'Login gagal', 'login.success' => 'Login berhasil', 'order.void' => 'Pembatalan transaksi', 'order.discount' => 'Diskon manual', 'shift' => 'Shift kas', 'stock' => 'Perubahan stok', 'staff' => 'Perubahan staff', 'report.export' => 'Ekspor laporan', 'settings' => 'Pengaturan', 'qris' => 'Pembayaran QRIS'] as $k => $v)
          <option value="{{ $k }}" @selected(request('action') === $k)>{{ $v }}</option>
        @endforeach
      </select>
    </form>
  </div>

  <div class="card overflow-hidden">
    {{-- tabel (tablet+) --}}
    <div class="hidden md:block overflow-x-auto p-2">
      <table class="w-full">
        <thead><tr class="border-b border-slate-100"><th class="th">Waktu</th><th class="th">Pengguna</th><th class="th">Aktivitas</th><th class="th">Objek</th><th class="th">Detail</th><th class="th">IP</th></tr></thead>
        <tbody class="divide-y divide-slate-50">
          @forelse ($logs as $l)
            <tr class="hover:bg-slate-50/70 transition-colors">
              <td class="td whitespace-nowrap">{{ $l->created_at?->translatedFormat('d M Y H:i:s') }}</td>
              <td class="td">{{ $l->user?->name ?? '—' }}</td>
              <td class="td"><span class="pill {{ str_contains($l->action, 'failed') || str_contains($l->action, 'void') || str_contains($l->action, 'review') || str_contains($l->action, 'mismatch') ? 'pill-red' : 'pill-slate' }}">{{ $l->action }}</span></td>
              <td class="td">{{ $l->target ?? '—' }}</td>
              <td class="td text-xs text-slate-500 max-w-xs truncate" title="{{ json_encode($l->meta, JSON_UNESCAPED_UNICODE) }}">{{ $l->meta ? json_encode($l->meta, JSON_UNESCAPED_UNICODE) : '' }}</td>
              <td class="td text-xs">{{ $l->ip }}</td>
            </tr>
          @empty
            <tr><td class="td text-center text-slate-500 py-10" colspan="6">Belum ada catatan.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- daftar kartu (mobile) --}}
    <ul class="md:hidden divide-y divide-slate-50 px-4">
      @forelse ($logs as $l)
        <li class="py-3.5">
          <div class="flex items-center justify-between gap-2">
            <span class="pill {{ str_contains($l->action, 'failed') || str_contains($l->action, 'void') || str_contains($l->action, 'review') || str_contains($l->action, 'mismatch') ? 'pill-red' : 'pill-slate' }}">{{ $l->action }}</span>
            <span class="text-[11px] text-slate-400">{{ $l->created_at?->translatedFormat('d M H:i') }}</span>
          </div>
          <div class="text-sm font-semibold mt-1.5">{{ $l->user?->name ?? '—' }} <span class="font-normal text-slate-400">· {{ $l->target ?? '—' }}</span></div>
          @if ($l->meta)<div class="text-xs text-slate-500 break-all mt-0.5">{{ json_encode($l->meta, JSON_UNESCAPED_UNICODE) }}</div>@endif
          <div class="text-[11px] text-slate-400 mt-0.5">IP {{ $l->ip }}</div>
        </li>
      @empty
        <li class="py-10 text-center text-sm text-slate-500">Belum ada catatan.</li>
      @endforelse
    </ul>
  </div>
  <div class="mt-4 overflow-x-auto">{{ $logs->links() }}</div>
</div>
@endsection
