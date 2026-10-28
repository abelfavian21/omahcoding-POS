<!DOCTYPE html>
<html lang="id">
<head>
  <title>Verifikasi 2 langkah · CashierStore Platform</title>
  <meta name="robots" content="noindex,nofollow">
  @include('partials.head')
  <style>.label { color:#565B75; }</style>
</head>
<body class="min-h-screen bg-sidebar-bg">
<main class="min-h-screen grid place-items-center p-4">
  <div class="w-full max-w-md">
    <div class="flex items-center gap-3 mb-6 text-white">
      <div class="w-11 h-11 rounded-2xl bg-lime-accent text-slate-950 grid place-items-center"><span class="material-symbols-outlined fill-1" style="font-size:26px">shield_person</span></div>
      <div class="leading-tight"><div class="font-extrabold text-xl tracking-tight">Cashier<span class="text-lime-accent">.</span>store</div><div class="text-sm font-bold text-slate-400">Platform</div></div>
    </div>
    <div class="rounded-3xl bg-white p-6 sm:p-8">
      <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Verifikasi 2 langkah</h1>
      <p class="text-sm font-medium text-slate-500 mt-1 mb-6">Masukkan 6 digit dari aplikasi authenticator Anda, atau satu kode pemulihan.</p>

      <form method="POST" action="{{ route('platform.2fa.verify') }}" class="space-y-4">
        @csrf
        <div>
          <label for="code" class="label">Kode</label>
          <input id="code" class="input" type="text" name="code" required autofocus autocomplete="one-time-code" inputmode="text" maxlength="32" spellcheck="false" autocapitalize="off">
        </div>
        @if (session('warning'))<p class="rounded-2xl bg-amber-50 border border-amber-100 text-amber-800 px-4 py-2.5 text-sm font-semibold" role="status">{{ session('warning') }}</p>@endif
        @if ($errors->any())<p class="rounded-2xl bg-red-50 border border-red-100 text-red-700 px-4 py-2.5 text-sm font-semibold" role="alert">{{ $errors->first() }}</p>@endif
        <button type="submit" class="btn-primary w-full h-12">Verifikasi</button>
      </form>

      <form method="POST" action="{{ route('platform.logout') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-sm font-bold text-slate-500 hover:underline">Bukan Anda? Keluar</button>
      </form>
    </div>
  </div>
</main>
</body>
</html>
