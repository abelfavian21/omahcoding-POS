@extends('layouts.platform')
@section('title', 'Admin platform')
@section('content')
<div class="mb-5">
  <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Admin platform</h1>
  <p class="text-sm font-medium text-slate-500 mt-1">Orang yang boleh membuka panel ini. Tidak ada pendaftaran publik; admin baru dibuat dari sini atau lewat <code>php artisan platform:create-admin</code>.</p>
</div>

<div class="grid xl:grid-cols-[minmax(0,1fr)_380px] gap-4 items-start">
  <section class="card overflow-hidden" aria-labelledby="h-list">
    <h2 id="h-list" class="font-extrabold p-5 pb-2">Daftar admin ({{ $admins->count() }})</h2>
    <ul class="divide-y divide-slate-50 px-5 pb-2">
      @foreach ($admins as $a)
        <li class="py-3.5 flex flex-wrap items-center justify-between gap-3">
          <div class="min-w-0">
            <div class="font-bold truncate">{{ $a->name }} @if ($a->is(auth('platform')->user()))<span class="pill pill-blue ml-1">Anda</span>@endif @if ($a->must_change_password)<span class="pill pill-amber ml-1">Belum ganti password</span>@endif</div>
            <div class="text-xs text-slate-500 break-all">{{ $a->email }} · login terakhir {{ $a->last_login_at ? $a->last_login_at->translatedFormat('d M Y H:i') : 'belum pernah' }}</div>
          </div>
          <div class="flex items-center gap-2">
            <span class="pill {{ $a->is_active ? 'pill-green' : 'pill-slate' }}">{{ $a->is_active ? 'Aktif' : 'Nonaktif' }}</span>
            @unless ($a->is(auth('platform')->user()))
              <form method="POST" action="{{ route('platform.admins.toggle', $a) }}" onsubmit="return confirm('{{ $a->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ addslashes($a->name) }}?')">@csrf
                <button class="{{ $a->is_active ? 'btn-danger' : 'btn-secondary' }} !h-9">{{ $a->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
            @endunless
          </div>
        </li>
      @endforeach
    </ul>
  </section>

  <section class="card p-5" aria-labelledby="h-new">
    <h2 id="h-new" class="font-extrabold mb-3">Tambah admin</h2>
    <form method="POST" action="{{ route('platform.admins.store') }}" class="space-y-3" autocomplete="off">
      @csrf
      <div><label for="n" class="label">Nama</label><input id="n" class="input" name="name" value="{{ old('name') }}" required maxlength="120"></div>
      <div><label for="e" class="label">Email</label><input id="e" class="input" type="email" name="email" value="{{ old('email') }}" required maxlength="255"></div>
      <div><label for="p" class="label">Password sementara</label><input id="p" class="input" type="password" name="password" required autocomplete="new-password">
        <p class="text-xs text-slate-500 mt-1.5">Minimal 12 karakter, huruf besar dan kecil, angka. Admin baru wajib menggantinya saat login pertama.</p></div>
      <button class="btn-primary w-full">Buat admin</button>
    </form>
  </section>
</div>
@endsection
