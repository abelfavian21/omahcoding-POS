{{-- Sukses --}}
<div id="step-success" class="max-w-lg mx-auto text-center space-y-6 py-8 hidden">
    <div
        class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-3xl flex items-center justify-center mx-auto shadow-lg shadow-emerald-500/20">
        <x-ico v="celebration" style="font-size:44px" /></div>
    <div class="space-y-2">
        <h3 class="text-2xl font-black text-slate-900">Selamat! Usaha Anda Berhasil Didaftarkan
        </h3>
        <p class="text-sm text-slate-600">Akun untuk <strong id="success-business-name"
                class="text-blue-600">{{ $reg['name'] ?? '' }}</strong> sudah aktif dan bisa dipakai
            gratis selamanya.</p>
    </div>
    <div
        class="p-4 bg-blue-50 rounded-2xl border border-blue-200 text-xs text-blue-800 space-y-1.5">
        <div class="flex items-center gap-1.5">Kategori Usaha: <strong
                class="inline-flex items-center gap-1"><x-ico :v="$reg['icon'] ?? null"
                    fallback="storefront" style="font-size:18px" /> <span
                    id="success-category">{{ $reg['category'] ?? '' }}</span></strong></div>
        <div>Kode Toko: <code id="success-code"
                class="font-mono bg-white px-2 py-0.5 rounded border border-blue-200">{{ $reg['code'] ?? '' }}</code>
        </div>
        <div class="text-[11px] text-blue-600">Simpan kode toko ini. Kasir memakainya bersama
            PIN untuk login cepat.</div>
    </div>
    <a id="btn-open-dashboard" href="{{ route('dashboard') }}"
        class="inline-block px-5 sm:px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-600/30">
        Buka Dashboard Kasir →
    </a>
</div>
