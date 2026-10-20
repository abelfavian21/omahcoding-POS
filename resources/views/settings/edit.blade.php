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

  {{-- ===== QRIS manual: gambar QRIS statis toko ===== --}}
  <div class="card p-5 sm:p-6 space-y-4 mt-5">
    <div class="flex items-center gap-3">
      <span class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 grid place-items-center shrink-0"><span class="material-symbols-outlined">qr_code</span></span>
      <div class="min-w-0"><h2 class="font-extrabold text-base">QRIS manual (gambar QRIS toko)</h2><p class="text-xs text-slate-500">Dipakai bila QRIS dinamis Midtrans tidak aktif. Gambar tampil di jendela pembayaran kasir setelah menekan Bayar dengan QRIS.</p></div>
    </div>
    <form method="POST" action="{{ route('settings.qris') }}" enctype="multipart/form-data" class="space-y-3">
      @csrf @method('PUT')
      @if ($business->hasManualQris())
        <div class="flex items-center gap-4 rounded-2xl bg-slate-50 p-3">
          <img src="{{ route('pos.qris.manual', ['v' => substr(md5($business->qris_image), 0, 8)]) }}" alt="QRIS toko" class="w-24 h-24 rounded-xl bg-white p-1" style="object-fit:contain">
          <div class="text-xs text-slate-500">Gambar QRIS tersimpan. Pilih file baru untuk menggantinya.</div>
        </div>
      @endif
      <div>
        <label class="label" for="qimg">{{ $business->hasManualQris() ? 'Ganti gambar QRIS' : 'Unggah gambar QRIS' }}</label>
        <input id="qimg" name="qris_image" type="file" accept="image/png,image/jpeg,image/webp" class="input !h-auto py-2">
        @error('qris_image')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        <p class="text-[11px] text-slate-400 mt-1.5">PNG, JPG, atau WEBP, maksimal 2 MB, minimal 200x200 piksel. Pakai gambar QRIS resmi toko yang bisa discan.</p>
      </div>
      <label class="flex items-start gap-3 rounded-2xl bg-slate-50 px-4 py-3 text-sm cursor-pointer">
        <input type="checkbox" name="qris_require_ref" value="1" class="mt-1" @checked(old('qris_require_ref', $business->qris_require_ref))>
        <span><b>Minta kode konfirmasi / approval code</b><br><span class="text-xs text-slate-500">Nonaktif (default): kasir hanya menekan tombol Konfirmasi Pembayaran setelah uang masuk. Aktifkan bila Anda ingin ada kode bukti di setiap pembayaran QRIS. Tanpa gambar QRIS, nomor referensi selalu wajib.</span></span>
      </label>
      <div class="flex flex-col sm:flex-row gap-2"><button class="btn-primary">Simpan QRIS manual</button></div>
    </form>
    @if ($business->hasManualQris())
      <form method="POST" action="{{ route('settings.qris') }}" onsubmit="return confirm('Hapus gambar QRIS? Kasir kembali wajib mengisi nomor referensi.')">
        @csrf @method('PUT')<input type="hidden" name="remove_image" value="1">
        <button class="btn-ghost !h-9 !px-3 text-red-600 text-xs">Hapus gambar QRIS</button>
      </form>
    @endif
  </div>

  {{-- ===== QRIS dinamis (Midtrans): satu Server Key per bisnis ===== --}}
  @php $mid = \App\Services\MidtransClient::forBusiness($business); @endphp
  <div class="card p-5 sm:p-6 space-y-4 mt-5">
    <div class="flex items-start justify-between gap-3">
      <div class="flex items-center gap-3 min-w-0">
        <span class="w-10 h-10 rounded-xl bg-[#181829] text-lime-accent grid place-items-center shrink-0"><span class="material-symbols-outlined">qr_code_2</span></span>
        <div class="min-w-0"><h2 class="font-extrabold text-base">QRIS dinamis (Midtrans)</h2><p class="text-xs text-slate-500">Pembayaran terkonfirmasi otomatis; uang masuk ke akun Midtrans bisnis ini.</p></div>
      </div>
      @if ($mid->enabled())
        <span class="pill {{ $mid->isProduction() ? 'pill-green' : 'pill-amber' }} shrink-0">● {{ $mid->isProduction() ? 'Produksi' : 'Sandbox' }}</span>
      @else
        <span class="pill pill-slate shrink-0">Belum aktif</span>
      @endif
    </div>

    <form method="POST" action="{{ route('settings.midtrans') }}" class="space-y-3" autocomplete="off">
      @csrf @method('PUT')
      <div>
        <label class="label" for="mkey">Midtrans Server Key</label>
        <input id="mkey" name="midtrans_server_key" type="password" class="input" autocomplete="new-password" spellcheck="false" maxlength="200"
               placeholder="{{ $mid->enabled() ? 'Tersimpan (' . $business->midtransKeyHint() . ') — isi untuk mengganti' : 'SB-Mid-server-xxxxxxxx' }}">
        @error('midtrans_server_key')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        <p class="text-[11px] text-slate-400 mt-1.5">Dashboard Midtrans → Settings → Access Keys. Awalan <b>SB-</b> = sandbox, tanpa awalan = produksi. Key diverifikasi ke Midtrans lalu disimpan terenkripsi dan tidak pernah ditampilkan lagi.</p>
      </div>
      <div class="flex flex-col sm:flex-row gap-2">
        <button class="btn-primary">Simpan &amp; verifikasi</button>
      </div>
    </form>

    @if ($mid->enabled())
      <form method="POST" action="{{ route('settings.midtrans') }}" onsubmit="return confirm('Nonaktifkan QRIS dinamis? Kasir kembali memakai QRIS manual.')">
        @csrf @method('PUT')<input type="hidden" name="remove" value="1">
        <button class="btn-ghost !h-9 !px-3 text-red-600 text-xs">Hapus key &amp; nonaktifkan</button>
      </form>
    @endif

    <div class="rounded-2xl bg-slate-50 px-4 py-3 text-xs space-y-1.5">
      <div class="font-bold text-slate-700">URL notifikasi (opsional, mempercepat konfirmasi)</div>
      <code class="block break-all rounded-lg bg-white/60 px-2 py-1.5 text-[11px]">{{ route('midtrans.notify') }}</code>
      <p class="text-slate-500">Tempel di Midtrans Dashboard → Settings → Configuration → <b>Payment Notification URL</b>. Wajib HTTPS dan bisa diakses publik. Tanpa ini, status tetap dicek otomatis oleh aplikasi saat QR tampil.</p>
    </div>
  </div>
</div>
@endsection
