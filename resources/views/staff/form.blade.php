@extends('layouts.app')
@section('title', $user->exists ? 'Edit Staff' : 'Tambah Staff')
@section('content')
@php $editing = $user->exists; @endphp
<div class="max-w-2xl">
  <a href="{{ route('staff.index') }}" class="text-sm font-semibold text-brand-600">← Kembali ke staff</a>
  <h1 class="text-3xl font-extrabold tracking-tight mt-2 mb-6">{{ $editing ? 'Edit Staff' : 'Tambah Staff' }}</h1>
  <form method="POST" class="card p-6 space-y-4" action="{{ $editing ? route('staff.update', $user) : route('staff.store') }}">
    @csrf @if ($editing) @method('PUT') @endif
    <div class="grid sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2"><label class="label">Nama *</label><input name="name" class="input" value="{{ old('name', $user->name) }}" required>@error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
      <div><label class="label">Email *</label><input name="email" type="email" class="input" value="{{ old('email', $user->email) }}" required>@error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
      <div><label class="label">Peran *</label><select name="role" class="input"><option value="kasir" @selected(old('role', $user->role) === 'kasir')>Kasir</option><option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option></select>@error('role')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
      <div><label class="label">Password {{ $editing ? '(kosongkan jika tidak diganti)' : '*' }}</label><input name="password" type="password" class="input" minlength="8" {{ $editing ? '' : 'required' }}>@error('password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
      <div><label class="label">PIN 4 digit {{ $editing ? '(kosongkan jika tidak diganti)' : '' }}</label><input name="pin" inputmode="numeric" maxlength="4" pattern="\d{4}" class="input" placeholder="1234">@error('pin')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
      <div><label class="label">No. telepon</label><input name="phone" class="input" value="{{ old('phone', $user->phone) }}"></div>
      <div><label class="label">Shift</label><input name="shift" class="input" value="{{ old('shift', $user->shift) }}" placeholder="07:00 - 15:00"></div>
    </div>
    <label class="flex items-center gap-2 text-sm font-semibold"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" class="rounded text-brand-600" @checked(old('is_active', $user->is_active))> Akun aktif</label>
    <div class="flex gap-3 pt-2"><button class="btn-primary">Simpan</button><a href="{{ route('staff.index') }}" class="btn-ghost">Batal</a></div>
  </form>
</div>
@endsection
