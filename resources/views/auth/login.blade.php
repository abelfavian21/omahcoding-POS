<!DOCTYPE html>
<html lang="id">
<head>
  <title>Masuk · CashierStore</title>
  @include('partials.head')
</head>
<body class="min-h-screen grid place-items-center p-4 bg-gradient-to-br from-canvas to-brand-50">
@php
  $tab = session('tab', $errors->has('pin') ? 'pin' : 'admin');
  $todayOrders = \App\Models\Order::paid()->whereDate('paid_at', today());
@endphp
<div class="w-full max-w-5xl grid lg:grid-cols-2 card overflow-hidden rounded-3xl shadow-xl">
  <div class="p-8 md:p-12">
    <div class="flex items-center gap-3 mb-8">
      <div class="w-11 h-11 rounded-xl bg-brand-600 text-white grid place-items-center"><span class="material-symbols-outlined">shopping_cart</span></div>
      <div class="leading-tight">
        <div class="font-extrabold text-lg">CashierStore</div>
        <div class="text-xs text-slate-500">{{ config('pos.store_name') }} Management Portal</div>
      </div>
    </div>

    <h1 class="text-3xl font-extrabold tracking-tight">Selamat datang kembali</h1>
    <p class="text-sm text-slate-500 mt-2 mb-6">Kelola pesanan, laporan penjualan, stok, dan staff kafe secara real-time.</p>

    <div class="inline-flex p-1 rounded-xl bg-brand-50 mb-6">
      <button type="button" data-tab="admin" class="tab px-4 h-10 rounded-lg text-sm font-semibold">Admin / Manager</button>
      <button type="button" data-tab="pin" class="tab px-4 h-10 rounded-lg text-sm font-semibold">PIN Kasir</button>
    </div>

    <form id="form-admin" method="POST" action="{{ route('login.password') }}" class="space-y-4">
      @csrf
      <div>
        <label class="label" for="email">Email</label>
        <input id="email" name="email" type="email" class="input" value="{{ old('email') }}" placeholder="admin@cashierstore.test" required>
        @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>
      <div>
        <label class="label" for="password">Password</label>
        <input id="password" name="password" type="password" class="input" placeholder="••••••••" required>
      </div>
      <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600"> Ingat saya</label>
      <button class="btn-primary w-full">Masuk <span class="material-symbols-outlined">arrow_forward</span></button>
    </form>

    <form id="form-pin" method="POST" action="{{ route('login.pin') }}" class="hidden text-center">
      @csrf
      <div class="font-bold mb-1">Masukkan PIN Terminal Kasir</div>
      <p class="text-xs text-slate-500 mb-5">PIN 4 digit untuk kasir terdaftar</p>
      <input type="hidden" name="pin" id="pinValue">
      <div class="flex justify-center gap-3 mb-3">
        @for ($i = 0; $i < 4; $i++)
          <input inputmode="numeric" maxlength="1" autocomplete="off" class="pin-box w-14 h-14 text-center text-xl font-bold rounded-xl border border-slate-200 bg-brand-50 focus:border-brand-600 focus:ring-4 focus:ring-brand-600/15">
        @endfor
      </div>
      @error('pin') <p class="text-xs text-red-600 mb-3">{{ $message }}</p> @enderror
      <button class="btn-primary w-full mt-3">Buka Sesi Kasir <span class="material-symbols-outlined">arrow_forward</span></button>
    </form>

    <div class="flex items-center gap-2 text-xs text-slate-500 mt-10"><span class="material-symbols-outlined text-emerald-600" style="font-size:16px">verified_user</span> Percobaan login dibatasi demi keamanan</div>
  </div>

  <div class="hidden lg:flex flex-col justify-center gap-4 p-10 bg-gradient-to-br from-brand-50 to-blue-100/60">
    <div class="card p-4 flex items-center justify-between">
      <div><div class="font-bold">{{ config('pos.store_name') }}</div><div class="text-xs text-slate-500">{{ config('pos.store_address') }}</div></div>
      <span class="pill pill-green">● Live</span>
    </div>
    <div class="card p-5">
      <div class="text-xs text-slate-500">Pendapatan toko hari ini</div>
      <div class="text-3xl font-extrabold my-1">@rupiah((clone $todayOrders)->sum('total'))</div>
      <div class="text-sm text-slate-500">{{ (clone $todayOrders)->count() }} pesanan selesai</div>
    </div>
    <div class="card p-4 flex gap-3 bg-amber-50/60">
      <span class="material-symbols-outlined text-amber-600">lightbulb</span>
      <p class="text-xs text-slate-600"><b class="text-amber-700">TIPS:</b> Kasir bisa masuk cepat memakai PIN 4 digit agar antrean tetap lancar.</p>
    </div>
  </div>
</div>

<script>
  const showTab = t => {
    document.getElementById('form-admin').classList.toggle('hidden', t !== 'admin');
    document.getElementById('form-pin').classList.toggle('hidden', t !== 'pin');
    document.querySelectorAll('.tab').forEach(b => {
      const on = b.dataset.tab === t;
      b.classList.toggle('bg-white', on); b.classList.toggle('text-brand-700', on); b.classList.toggle('shadow-sm', on);
      b.classList.toggle('text-slate-500', !on);
    });
    if (t === 'pin') document.querySelector('.pin-box').focus();
  };
  document.querySelectorAll('.tab').forEach(b => b.addEventListener('click', () => showTab(b.dataset.tab)));
  showTab(@json($tab));

  const boxes = [...document.querySelectorAll('.pin-box')], out = document.getElementById('pinValue');
  const sync = () => out.value = boxes.map(b => b.value).join('');
  boxes.forEach((b, i) => {
    b.addEventListener('input', () => { b.value = b.value.replace(/\D/g, ''); sync(); if (b.value && boxes[i+1]) boxes[i+1].focus(); });
    b.addEventListener('keydown', e => { if (e.key === 'Backspace' && !b.value && boxes[i-1]) boxes[i-1].focus(); });
  });
</script>
</body>
</html>
