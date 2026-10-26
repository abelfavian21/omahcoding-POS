@php
    $reg = $registered;
    $firstActive = $categories->firstWhere('is_active', true);
    $selectedSlug = old('category', $firstActive?->slug);
    $loginMode = session('login_mode', 'email');
    $initialTab =
        $reg || old('_form') === 'register'
            ? 'register'
            : (session('login_mode') || $errors->login->any()
                ? 'login'
                : 'register');
    $initialStep = $reg
        ? 'success'
        : ($errors->has('agree')
            ? 3
            : ($errors->hasAny(['category', 'outlet_count', 'location'])
                ? 2
                : 1));
    $inp =
        'w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-semibold focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all';
    $dest = auth()->check() ? (auth()->user()->isAdmin() ? route('dashboard') : route('pos')) : '#';
@endphp
@if (auth()->check() && !$reg)
    <section id="auth-section" class="scroll-mt-16 py-14 sm:py-20 lg:py-28 bg-[#0E0F1A] relative z-20 scroll-mt-16">
        <div id="masuk" class="max-w-xl mx-auto px-6">
            <div
                class="bg-white rounded-3xl shadow-xl border border-slate-200/90 p-8 sm:p-10 text-center space-y-5">
                <div
                    class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center mx-auto">
                    <x-ico v="person" style="font-size:30px" /></div>
                <h2 class="text-2xl font-black text-slate-900">Kamu sedang masuk</h2>
                <p class="text-sm text-slate-600">Akun aktif: <strong>{{ auth()->user()->name }}</strong>
                    ({{ auth()->user()->business?->name ?? config('pos.store_name') }}). Untuk mendaftarkan usaha
                    lain atau masuk dengan akun berbeda, keluar dulu.</p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ $dest }}"
                        class="px-6 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-600/30">Buka
                        Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button type="submit"
                            class="w-full px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl">Keluar
                            &amp; Daftar Usaha Lain</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@else
    <section id="auth-section" class="scroll-mt-16 py-14 sm:py-20 lg:py-28 bg-[#0E0F1A] relative z-20 scroll-mt-16" data-initial-tab="{{ $initialTab }}"
        data-initial-step="{{ $initialStep }}" data-login-mode="{{ $loginMode }}">
        <div id="masuk" class="max-w-6xl mx-auto px-3 sm:px-6">
            <div class="text-center max-w-2xl mx-auto mb-12 space-y-3">
                <div
                    class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-100 text-blue-700 text-xs font-bold tracking-wide">
                    <span>PORTAL ONBOARDING BISNIS</span></div>
                <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Mulai Digitalisasi Usaha
                    Anda</h2>
                <p class="text-slate-600 text-sm sm:text-base">Daftarkan usaha Anda, pilih kategori yang tepat,
                    konfirmasi data, lalu langsung masuk ke dashboard kasir CashierStore.</p>
            </div>

            <div class="bg-white rounded-none sm:rounded-3xl sm:shadow-xl border-y sm:border border-slate-200/90 overflow-hidden -mx-3 sm:mx-0">
                <div class="grid grid-cols-2 border-b border-slate-200 bg-slate-50/70 p-2">
                    <button type="button" id="tab-login-btn" onclick="switchAuthTab('login')"
                        class="py-3 px-3 sm:py-3.5 sm:px-6 rounded-2xl font-bold text-sm text-slate-600 transition-all">Masuk<span class="hidden sm:inline"> Akun Pemilik / Kasir</span></button>
                    <button type="button" id="tab-register-btn" onclick="switchAuthTab('register')"
                        class="py-3 px-3 sm:py-3.5 sm:px-6 rounded-2xl font-bold text-sm text-slate-600 transition-all">Daftar<span class="hidden sm:inline"> &amp; Konfigurasi Bisnis Baru</span></button>
                </div>

                @include('landing.partials.auth.login')

                @include('landing.partials.auth.register')
            </div>
        </div>
    </section>
@endif
