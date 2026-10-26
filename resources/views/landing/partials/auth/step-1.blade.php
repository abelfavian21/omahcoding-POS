{{-- Langkah 1 --}}
<div id="step-1-content" class="max-w-xl mx-auto space-y-6 hidden">
    <div class="text-center space-y-1">
        <h3 class="text-xl font-black text-slate-900">Langkah 1: Registrasi Akun Pemilik
        </h3>
        <p class="text-xs text-slate-500">Buat identitas akun utama yang akan mengelola
            seluruh toko &amp; staf.</p>
    </div>
    <div class="space-y-4">
        <div><label
                class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama
                Lengkap Pemilik</label>
            <input id="reg-owner-name" name="owner_name" required maxlength="120"
                value="{{ old('owner_name') }}" placeholder="Contoh: Budi Santoso"
                autocomplete="name" class="{{ $inp }}">
            <p id="err-owner_name" class="hidden mt-1.5 text-xs font-semibold text-rose-600" role="alert"></p>
        </div>
        <div><label
                class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama
                Usaha / Brand</label>
            <input id="reg-business-name" name="business_name" required maxlength="120"
                value="{{ old('business_name') }}" placeholder="Contoh: Kopi Nusantara"
                class="{{ $inp }}">
            <p id="err-business_name" class="hidden mt-1.5 text-xs font-semibold text-rose-600" role="alert"></p>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label
                    class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email
                    Bisnis</label>
                <input id="reg-email" type="email" name="email" required
                    value="{{ old('email') }}" placeholder="owner@tokomu.id"
                    autocomplete="email" class="{{ $inp }}">
                <p id="err-email" class="hidden mt-1.5 text-xs font-semibold text-rose-600" role="alert"></p>
            </div>
            <div><label
                    class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor
                    WhatsApp Aktif</label>
                <input id="reg-phone" type="tel" name="phone" required
                    minlength="8" value="{{ old('phone') }}"
                    placeholder="0812-xxxx-xxxx" autocomplete="tel" class="{{ $inp }}">
                <p id="err-phone" class="hidden mt-1.5 text-xs font-semibold text-rose-600" role="alert"></p>
            </div>
        </div>
        <livewire:register-password-field />
    </div>
    <div class="pt-4">
        <button
            type="button"
            id="btn-next-step-1"
            onclick="nextStep(1)"
            disabled
            class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-black text-sm rounded-xl shadow-lg shadow-blue-600/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
            Lanjut: Pilih Kategori Usaha →
        </button>
    </div>
</div>
