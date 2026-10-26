{{-- Langkah 2 --}}
<div id="step-2-content" class="max-w-4xl mx-auto space-y-6 hidden">
    <div class="text-center space-y-1">
        <h3 class="text-xl font-black text-slate-900">Langkah 2: Pilih Kategori Usaha</h3>
        <p class="text-xs text-slate-500">Kategori menentukan alur kasir dan kategori produk bawaan di toko Anda.</p>

    </div>
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 pt-2">
        @foreach ($categories as $c)
            <div class="cat-card relative p-3.5 sm:p-5 rounded-2xl border-2 shadow-sm transition-all {{ $c->is_active ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}"
                data-slug="{{ $c->slug }}" data-name="{{ $c->name }}"
                data-icon="{{ \App\Support\Icons::name($c->icon, 'storefront') }}"
                data-active="{{ $c->is_active ? 1 : 0 }}">
                <div class="flex items-center justify-between mb-3">
                    <div
                        class="w-12 h-12 rounded-xl bg-{{ $c->color }}-100 text-{{ $c->color }}-600 flex items-center justify-center">
                        <x-ico :v="$c->icon" fallback="storefront"
                            style="font-size:26px" /></div>
                    <div
                        class="check-icon w-6 h-6 rounded-full bg-blue-600 text-white items-center justify-center text-xs font-bold hidden">
                        <x-ico v="check" style="font-size:16px" /></div>
                </div>
                <h4 class="font-extrabold text-slate-900 text-base mb-1">
                    {{ $c->name }}</h4>
                <p class="text-xs text-slate-500 leading-relaxed mb-3">{{ $c->tagline }}
                </p>
                <span
                    class="inline-block px-2.5 py-1 {{ $c->is_active ? 'bg-white text-blue-700 border-blue-200' : 'bg-slate-100 text-slate-500 border-slate-200' }} text-[10px] font-bold rounded-md border">{{ $c->is_active ? $c->badge : 'Segera hadir' }}</span>
            </div>
        @endforeach
    </div>
    <div
        class="p-5 bg-slate-50 rounded-2xl border border-slate-200 mt-6 grid sm:grid-cols-2 gap-4">
        <div><label
                class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jumlah
                Outlet Saat Ini</label>
            <select id="reg-outlet-count" name="outlet_count"
                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold bg-white outline-none">
                @foreach (\App\Http\Controllers\RegisterController::OUTLETS as $o)
                    <option value="{{ $o }}" @selected(old('outlet_count', '1 Outlet') === $o)>
                        {{ $o }}</option>
                @endforeach
            </select>
        </div>
        <div><label
                class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kota
                / Lokasi Operasional</label>
            <input id="reg-location" name="location" maxlength="120"
                value="{{ old('location') }}" placeholder="Contoh: Surakarta"
                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold bg-white outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 transition-all">
            <p id="err-location" class="hidden mt-1.5 text-xs font-semibold text-rose-600" role="alert"></p>
        </div>
    </div>
    <p id="step2-error" class="hidden text-center text-xs font-bold text-rose-600"></p>
    <div class="flex items-center justify-between pt-4">
        <button type="button" onclick="goToStep(1)"
            class="px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition-all">←
            Kembali</button>
        <button type="button" id="btn-next-step-2" onclick="nextStep(2)" disabled
            class="px-5 sm:px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-600/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed">Lanjut:
            Konfirmasi Usaha →</button>
    </div>
</div>
