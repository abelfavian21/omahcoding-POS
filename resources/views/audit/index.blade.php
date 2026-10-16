@extends('layouts.app')
@section('title', 'Audit Log')
@section('content')
<div class="max-w-6xl">
  <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
    <div>
      <h1 class="text-3xl font-extrabold tracking-tight">Audit Log</h1>
      <p class="text-sm text-slate-500">Jejak aktivitas penting. Catatan ini tidak bisa diubah atau dihapus dari aplikasi.</p>
    </div>
    <form method="GET" class="flex gap-2">
      <select name="action" class="input !w-56" onchange="this.form.submit()">
        <option value="">Semua aktivitas</option>
        @foreach (['login.failed' => 'Login gagal', 'login.success' => 'Login berhasil', 'order.void' => 'Pembatalan transaksi', 'order.discount' => 'Diskon manual', 'shift' => 'Shift kas', 'stock' => 'Perubahan stok', 'staff' => 'Perubahan staff', 'report.export' => 'Ekspor laporan', 'settings' => 'Pengaturan'] as $k => $v)
          <option value="{{ $k }}" @selected(request('action') === $k)>{{ $v }}</option>
        @endforeach
      </select>
    </form>
  </div>
  <div class="card overflow-x-auto">
    <table class="w-full">
      <thead><tr class="border-b border-slate-100"><th class="th">Waktu</th><th class="th">Pengguna</th><th class="th">Aktivitas</th><th class="th">Objek</th><th class="th">Detail</th><th class="th">IP</th></tr></thead>
      <tbody class="divide-y divide-slate-50">
        @forelse ($logs as $l)
          <tr>
            <td class="td whitespace-nowrap">{{ $l->created_at?->translatedFormat('d M Y H:i:s') }}</td>
            <td class="td">{{ $l->user?->name ?? '—' }}</td>
            <td class="td"><span class="pill {{ str_contains($l->action, 'failed') || str_contains($l->action, 'void') ? 'pill-red' : 'pill-slate' }}">{{ $l->action }}</span></td>
            <td class="td">{{ $l->target ?? '—' }}</td>
            <td class="td text-xs text-slate-500 max-w-xs truncate" title="{{ json_encode($l->meta, JSON_UNESCAPED_UNICODE) }}">{{ $l->meta ? json_encode($l->meta, JSON_UNESCAPED_UNICODE) : '' }}</td>
            <td class="td text-xs">{{ $l->ip }}</td>
          </tr>
        @empty
          <tr><td class="td text-center text-slate-500" colspan="6">Belum ada catatan.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $logs->links() }}</div>
</div>
@endsection
