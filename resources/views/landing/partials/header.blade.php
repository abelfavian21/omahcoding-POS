<a href="#fitur" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60] btn-neon">Lewati ke konten</a>

<header class="sticky top-0 z-50 backdrop-blur-xl bg-[#0E0F1A]/80 border-b border-edge">
    <div class="max-w-6xl mx-auto px-5 h-16 flex items-center justify-between gap-4">
        <a href="{{ url('/') }}" class="flex items-center gap-2.5" aria-label="CashierStore beranda">
            <span class="w-9 h-9 rounded-xl bg-neon text-[#181829] flex items-center justify-center shadow-[0_0_18px_rgba(210,248,57,.4)]">
                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm0 6h16v8a2 2 0 01-2 2H6a2 2 0 01-2-2v-8zm5 3a1 1 0 000 2h6a1 1 0 100-2H9z" /></svg>
            </span>
            <span class="text-base sm:text-lg font-extrabold tracking-tight">Cashier<span class="text-neon">Store</span></span>
        </a>
        <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-300" aria-label="Menu utama">
            <a href="#fitur" class="hover:text-neon transition-colors">Fitur</a>
            <a href="#cara-kerja" class="hover:text-neon transition-colors">Cara mulai</a>
            <a href="#faq" class="hover:text-neon transition-colors">Tanya jawab</a>
        </nav>
        <div class="flex items-center gap-2">
            @auth
                <a href="{{ auth()->user()->isAdmin() ? route('dashboard') : route('pos') }}" class="btn-neon !h-10 !px-4 sm:!px-5 whitespace-nowrap">Buka dashboard</a>
            @else
                <a href="#masuk" onclick="switchAuthTab('login')" class="text-sm font-bold text-slate-200 hover:text-neon px-2 sm:px-3 py-2">Masuk</a>
                <a href="#auth-section" onclick="switchAuthTab('register')" class="btn-neon !h-10 !px-4 sm:!px-5 whitespace-nowrap">Coba gratis</a>
            @endauth
        </div>
    </div>
</header>
