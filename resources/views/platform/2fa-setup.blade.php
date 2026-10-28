<!DOCTYPE html>
<html lang="id">
<head>
  <title>Aktifkan 2FA · CashierStore Platform</title>
  <meta name="robots" content="noindex,nofollow">
  @include('partials.head')
  <style>.label { color:#565B75; }</style>
</head>
<body class="min-h-screen bg-sidebar-bg">
<main class="min-h-screen grid place-items-center p-4">
  <div class="w-full max-w-lg">
    <div class="rounded-3xl bg-white p-6 sm:p-8">
      <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Aktifkan verifikasi 2 langkah</h1>
      <p class="text-sm font-medium text-slate-500 mt-1 mb-5">Panel platform bisa menangguhkan semua toko, jadi login wajib memakai kode dari ponsel Anda.</p>

      <ol class="space-y-4 text-sm text-slate-700 mb-6">
        <li><b>1.</b> Pasang aplikasi authenticator (Google Authenticator, Authy, 1Password, Aegis, dll).</li>
        <li><b>2.</b> Tambah akun baru dengan <b>kunci manual</b> (jenis: berbasis waktu):
          <div class="mt-2 rounded-2xl bg-slate-100 px-4 py-3 font-mono text-base font-bold tracking-wider text-slate-900 break-all select-all" aria-label="Kunci rahasia">{{ $groups }}</div>
          <p class="mt-2 text-xs text-slate-500">Di ponsel yang sama, Anda juga bisa mencoba <a class="font-bold underline" href="{{ $uri }}">membuka langsung di aplikasi authenticator</a>. Jangan bagikan kunci ini.</p>
        </li>
        <li><b>3.</b> Masukkan password dan 6 digit yang tampil di aplikasi untuk memastikan sudah benar.</li>
      </ol>

      <form method="POST" action="{{ route('platform.2fa.confirm') }}" class="space-y-4">
        @csrf
        <div><label for="password" class="label">Password Anda</label>
          <input id="password" class="input" type="password" name="password" required autocomplete="current-password"></div>
        <div><label for="code" class="label">Kode 6 digit</label>
          <input id="code" class="input" type="text" name="code" required inputmode="numeric" pattern="[0-9 ]*" maxlength="16" autocomplete="one-time-code"></div>
        @if (session('warning'))<p class="rounded-2xl bg-amber-50 border border-amber-100 text-amber-800 px-4 py-2.5 text-sm font-semibold" role="status">{{ session('warning') }}</p>@endif
        @if ($errors->any())<p class="rounded-2xl bg-red-50 border border-red-100 text-red-700 px-4 py-2.5 text-sm font-semibold" role="alert">{{ $errors->first() }}</p>@endif
        <button type="submit" class="btn-primary w-full h-12">Aktifkan 2FA</button>
      </form>

      <form method="POST" action="{{ route('platform.logout') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-sm font-bold text-slate-500 hover:underline">Keluar</button>
      </form>
    </div>
  </div>
</main>
</body>
</html>
