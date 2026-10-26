{{-- Langkah 3 --}}
<div id="step-3-content" class="max-w-2xl mx-auto space-y-6 hidden">
    <div class="text-center space-y-1">
        <div
            class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-2 font-bold">
            <x-ico v="check" style="font-size:26px" /></div>
        <h3 class="text-xl font-black text-slate-900">Langkah 3: Konfirmasi Data Usaha Anda
        </h3>
        <p class="text-xs text-slate-500">Periksa ringkasan sebelum akun CashierStore Anda
            dibuat.</p>
    </div>
    <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200/90 space-y-5">
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div>
                <span
                    class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Nama
                    Bisnis &amp; Kategori</span>
                <div
                    class="text-lg font-black text-slate-900 flex items-center gap-2 mt-0.5">
                    <span id="review-cat-icon" class="material-symbols-outlined"
                        aria-hidden="true"></span> <span id="review-business-name"></span>
                </div>
                <span id="review-category"
                    class="inline-block mt-1 text-xs font-extrabold text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200"></span>
            </div>
            <button type="button" onclick="goToStep(2)"
                class="text-xs font-bold text-blue-600 hover:underline">Ubah
                Kategori</button>
        </div>
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div><span class="text-slate-400 font-semibold block">Nama Pemilik:</span><span
                    id="review-owner" class="font-bold text-slate-800 text-sm"></span>
            </div>
            <div><span class="text-slate-400 font-semibold block">Email Login:</span><span
                    id="review-email"
                    class="font-bold text-slate-800 text-sm break-all"></span></div>
            <div><span class="text-slate-400 font-semibold block">No. WhatsApp:</span><span
                    id="review-phone" class="font-bold text-slate-800 text-sm"></span>
            </div>
            <div><span class="text-slate-400 font-semibold block">Skala &amp;
                    Lokasi:</span><span id="review-scale"
                    class="font-bold text-slate-800 text-sm"></span></div>
        </div>
        <div class="pt-4 border-t border-slate-200">
            <span
                class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-2">Modul
                yang langsung aktif:</span>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach (['Kasir / POS', 'Manajemen Stok', 'CRM & Member Tier', 'Dashboard Penjualan', 'Laporan PDF & Excel', 'Login PIN Kasir'] as $m)
                    <div
                        class="p-2 bg-white rounded-lg border border-slate-200 text-[11px] font-semibold text-slate-700 flex items-center gap-1.5">
                        <span class="text-emerald-500"><x-ico v="check"
                                style="font-size:14px" /></span> {{ $m }}</div>
                @endforeach
            </div>
        </div>
        <div class="pt-3">
            <label class="flex items-start gap-2.5 text-xs text-slate-600 cursor-pointer">
                <input id="agree-terms" type="checkbox" name="agree" value="1"
                    @checked(old('agree'))
                    class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span>Saya menyatakan data usaha di atas valid dan menyetujui Ketentuan
                    Layanan &amp; Kebijakan Privasi CashierStore.</span>
            </label>
            <p id="step3-error" class="hidden mt-2 text-xs font-bold text-rose-600"></p>
        </div>
    </div>
    <div class="flex items-center justify-between pt-2">
        <button type="button" onclick="goToStep(2)"
            class="px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition-all">←
            Ubah Data</button>
        <button type="submit" id="btn-submit" disabled
            class="px-5 sm:px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm rounded-xl shadow-lg shadow-emerald-600/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed">Konfirmasi
            &amp; Buat Toko Sekarang</button>
    </div>
</div>
