@extends('layouts.platform')
@section('title', 'Akun saya')
@section('content')
<div class="max-w-xl">
  <div class="mb-5"><h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Akun saya</h1>
    <p class="text-sm font-medium text-slate-500 mt-1">{{ $admin->name }} · {{ $admin->email }}</p></div>
  <section class="card p-5 sm:p-6" aria-labelledby="h-pw">
    <h2 id="h-pw" class="font-extrabold mb-3">Ganti password</h2>
    <form method="POST" action="{{ route('platform.account.update') }}" class="space-y-3">
      @csrf @method('PUT')
      <div><label for="cp" class="label">Password saat ini</label><input id="cp" class="input" type="password" name="current_password" required autocomplete="current-password"></div>
      <div><label for="np" class="label">Password baru</label><input id="np" class="input" type="password" name="password" required autocomplete="new-password">
        <p class="text-xs text-slate-500 mt-1.5">Minimal 12 karakter, huruf besar dan kecil, angka.</p></div>
      <div><label for="np2" class="label">Ulangi password baru</label><input id="np2" class="input" type="password" name="password_confirmation" required autocomplete="new-password"></div>
      <button class="btn-primary">Simpan password</button>
    </form>
  </section>
</div>
@endsection
