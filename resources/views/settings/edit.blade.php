@extends('layouts.app')
@section('title', 'Pengaturan Toko')
@section('content')
<div class="max-w-2xl">
  <div class="text-xs text-slate-400 font-semibold mb-1">Dashboard › <span class="text-slate-700">Pengaturan</span></div>
  <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-6">Pengaturan Toko</h1>
  <form method="POST" action="{{ route('settings.update') }}" class="card p-5 sm:p-6 space-y-4">
    @csrf @method('PUT')
    <div class="grid sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2"><label class="label">Nama toko *</label><input name="name" class="input" required maxlength="120" value="{{ old('name', $business->name) }}">@error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
      <div><label class="label">Telepon</label><input name="phone" class="input" maxlength="30" value="{{ old('phone', $business->phone) }}"></div>
      <div><label class="label">Lokasi</label><input name="location" class="input" maxlength="120" value="{{ old('location', $business->location) }}"></div>
      <div><label class="label">Tarif pajak (%) *</label><input name="tax_rate_pct" type="number" step="0.01" min="0" max="100" class="input" required value="{{ old('tax_rate_pct', round($business->taxRate() * 100, 2)) }}">@error('tax_rate_pct')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
      <div><label class="label">Nama pajak di struk *</label><input name="tax_label" class="input" required maxlength="40" value="{{ old('tax_label', $business->tax_label ?? config('pos.tax_label')) }}"></div>
      <div class="sm:col-span-2"><label class="label">Teks bawah struk</label><input name="receipt_footer" class="input" maxlength="255" value="{{ old('receipt_footer', $business->receipt_footer ?? config('pos.receipt_footer')) }}"></div>
    </div>
    <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3 text-sm"><span class="material-symbols-outlined text-slate-400">key</span><span class="text-slate-500">Kode toko untuk login PIN kasir:</span> <b class="tracking-wider">{{ $business->code }}</b></div>
    <div class="pt-2"><button class="btn-primary w-full sm:w-auto">Simpan</button></div>
  </form>
</div>
@endsection
