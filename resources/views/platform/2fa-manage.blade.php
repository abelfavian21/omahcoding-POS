@extends('layouts.platform')
@section('title', 'Verifikasi 2 langkah')
@section('content')
<div class="max-w-xl">
  <div class="mb-5"><h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Verifikasi 2 langkah</h1>
    <p class="text-sm font-medium text-slate-500 mt-1">Aktif sejak {{ $admin->totp_confirmed_at?->translatedFormat('d M Y H:i') }}. Kode pemulihan tersisa: <b>{{ $left }}</b>.</p></div>

  @if (session('recovery_codes'))
  <section class="card p-5 sm:p-6 mb-4" aria-labelledby="h-codes">
    <h2 id="h-codes" class="font-extrabold mb-1">Simpan kode pemulihan ini sekarang</h2>
    <p class="text-sm text-slate-600 mb-3">Hanya tampil sekali. Tiap kode bisa dipakai satu kali bila ponsel hilang. Simpan di pengelola password atau tempat aman yang terpisah dari ponsel.</p>
    <ul class="grid grid-cols-2 gap-2 font-mono text-base font-bold" aria-label="Kode pemulihan">
      @foreach (session('recovery_codes') as $c)<li class="rounded-xl bg-slate-100 px-3 py-2 select-all">{{ $c }}</li>@endforeach
    </ul>
  </section>
  @endif

  <section class="card p-5 sm:p-6" aria-labelledby="h-regen">
    <h2 id="h-regen" class="font-extrabold mb-1">Buat set kode pemulihan baru</h2>
    <p class="text-sm text-slate-600 mb-3">Set lama langsung tidak berlaku. Perlu password dan kode dari aplikasi authenticator (bukan kode pemulihan).</p>
    <form method="POST" action="{{ route('platform.2fa.regenerate') }}" class="space-y-3">
      @csrf
      <div><label for="rp" class="label">Password</label><input id="rp" class="input" type="password" name="password" required autocomplete="current-password"></div>
      <div><label for="rc" class="label">Kode 6 digit</label><input id="rc" class="input" type="text" name="code" required inputmode="numeric" pattern="[0-9 ]*" maxlength="16" autocomplete="one-time-code"></div>
      @if ($errors->any())<p class="rounded-2xl bg-red-50 border border-red-100 text-red-700 px-4 py-2.5 text-sm font-semibold" role="alert">{{ $errors->first() }}</p>@endif
      <button class="btn-primary">Buat kode baru</button>
    </form>
  </section>

  <p class="text-xs text-slate-500 mt-4">Kehilangan ponsel dan kode pemulihan? Minta pemilik server menjalankan <code>php artisan platform:2fa-reset {{ $admin->email }}</code>.</p>
</div>
@endsection
