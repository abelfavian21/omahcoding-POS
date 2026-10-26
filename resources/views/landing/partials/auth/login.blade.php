{{-- ========== LOGIN ========== --}}
<div id="view-login" class="p-5 sm:p-12 max-w-xl mx-auto hidden">
    <div class="text-center space-y-2 mb-6">
        <h3 class="text-2xl font-black text-slate-900">Selamat Datang Kembali!</h3>
        <p class="text-slate-500 text-xs">Akses dashboard, terminal kasir, dan manajemen toko Anda.
        </p>
    </div>
    <div class="grid grid-cols-2 gap-1 p-1 bg-slate-100 rounded-xl mb-6 text-xs font-bold">
        <button type="button" id="mode-email" onclick="switchLoginMode('email')"
            class="py-2.5 rounded-lg">Pemilik / Admin</button>
        <button type="button" id="mode-pin" onclick="switchLoginMode('pin')"
            class="py-2.5 rounded-lg">Kasir (PIN)</button>
    </div>

    <form id="form-email" method="POST" action="{{ route('login.password') }}"
        class="space-y-5">
        @csrf
        <div>
            <label
                class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Email</label>
            <input type="email" name="email" required autocomplete="username"
                value="{{ old('email') }}" placeholder="owner@tokomu.id"
                class="{{ $inp }}">
            @error('email', 'login')
                <p class="text-xs text-rose-600 font-semibold mt-1.5">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label
                class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Kata
                Sandi</label>
            <input type="password" name="password" required autocomplete="current-password"
                placeholder="••••••••••••" class="{{ $inp }}">
        </div>
        <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
            <input type="checkbox" name="remember" value="1"
                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"> <span>Ingat
                akun di perangkat ini</span>
        </label>
        <button type="submit"
            class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-black text-sm rounded-xl shadow-lg shadow-blue-600/30 transition-all">Masuk
            ke Dashboard CashierStore</button>
    </form>

    <form id="form-pin" method="POST" action="{{ route('login.pin') }}"
        class="space-y-5 hidden">
        @csrf
        <div>
            <label
                class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Kode
                Toko</label>
            <input type="text" name="store_code" required value="{{ old('store_code') }}"
                placeholder="CS-ORELY-0001" autocapitalize="characters"
                class="{{ $inp }} uppercase">
            <p class="text-[11px] text-slate-400 mt-1.5">Kode toko ada di layar konfirmasi saat
                registrasi. Tanyakan ke pemilik jika lupa.</p>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">PIN
                Kasir</label>
            <input type="password" name="pin" required inputmode="numeric" maxlength="6"
                pattern="\d{4,6}" placeholder="••••••" autocomplete="off"
                class="{{ $inp }} tracking-[0.6em] text-center">
            @error('pin', 'login')
                <p class="text-xs text-rose-600 font-semibold mt-1.5">{{ $message }}</p>
            @enderror
            @error('store_code', 'login')
                <p class="text-xs text-rose-600 font-semibold mt-1.5">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit"
            class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-black text-sm rounded-xl shadow-lg shadow-blue-600/30 transition-all">Masuk
            ke Terminal Kasir</button>
    </form>

    <div class="mt-8 pt-6 border-t border-slate-100 text-center text-xs text-slate-500">
        Belum punya akun bisnis? <button type="button" onclick="switchAuthTab('register')"
            class="font-bold text-blue-600 hover:underline">Registrasi Usaha Baru</button>
    </div>
</div>
