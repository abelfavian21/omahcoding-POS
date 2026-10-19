@extends('layouts.app')
@section('title', $customer->exists ? 'Edit Pelanggan' : 'Tambah Pelanggan')
@section('content')
@php $editing = $customer->exists; @endphp
<div class="max-w-2xl">
  <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-1 h-9 px-3 -ml-3 rounded-full text-sm font-semibold text-slate-500 hover:bg-white hover:text-slate-900 transition"><span class="material-symbols-outlined" style="font-size:18px">arrow_back</span> Kembali ke pelanggan</a>
  <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-2 mb-6">{{ $editing ? 'Edit Pelanggan' : 'Tambah Pelanggan Baru' }}</h1>

  <form method="POST" class="card p-5 sm:p-6 space-y-4" action="{{ $editing ? route('customers.update', $customer) : route('customers.store') }}">
    @csrf @if ($editing) @method('PUT') @endif
    <input type="hidden" name="return_to" value="{{ request('return_to') }}">

    <div class="grid sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2"><label class="label">Nama lengkap *</label>
        <input name="name" class="input" value="{{ old('name', $customer->name) }}" required autofocus>
        @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
      <div><label class="label">No. telepon / WhatsApp</label>
        <input name="phone" class="input" value="{{ old('phone', $customer->phone) }}" placeholder="+62 812…">
        @error('phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
      <div><label class="label">Email</label>
        <input name="email" type="email" class="input" value="{{ old('email', $customer->email) }}">
        @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
      @if (auth()->user()->isAdmin())
      <div><label class="label">Tier member</label>
        <select name="tier" class="input">@foreach (\App\Models\Customer::TIERS as $k => $v)<option value="{{ $k }}" @selected(old('tier', $customer->tier) === $k)>{{ $v }}</option>@endforeach</select></div>
      @endif
      <div><label class="label">Tanggal lahir</label>
        <input name="birthday" type="date" class="input" value="{{ old('birthday', $customer->birthday?->format('Y-m-d')) }}">
        @error('birthday')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
      <div class="sm:col-span-2"><label class="label">Alergi (peringatan untuk dapur)</label>
        <input name="allergy" class="input" value="{{ old('allergy', $customer->allergy) }}" placeholder="mis. Alergi kacang"></div>
      <div class="sm:col-span-2"><label class="label">Catatan / preferensi</label>
        <textarea name="notes" rows="3" class="input" placeholder="mis. Suka meja dekat jendela, oat milk">{{ old('notes', $customer->notes) }}</textarea></div>
    </div>

    <div class="flex flex-col-reverse sm:flex-row gap-3 pt-2"><button class="btn-primary">Simpan</button><a href="{{ route('customers.index') }}" class="btn-ghost">Batal</a></div>
  </form>
</div>
@endsection
