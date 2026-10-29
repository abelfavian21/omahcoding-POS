<!-- HERO (versi clean: tengah, lega, produk di bawah) -->
<section class="relative overflow-hidden">
    <div class="grid-bg absolute inset-0" aria-hidden="true"></div>
    <div class="absolute inset-x-0 top-0 h-[34rem]" style="background:radial-gradient(50% 60% at 50% 0%, rgba(210,248,57,.13), transparent 72%)" aria-hidden="true"></div>
    <div class="relative max-w-4xl mx-auto px-5 pt-10 sm:pt-24 text-center">
        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.06]" style="text-wrap:balance">Aplikasi kasir yang cepat, laporan yang rapi.</h1>
        <p class="mt-6 text-lg sm:text-xl text-slate-300 leading-relaxed max-w-2xl mx-auto">Catat penjualan, pantau stok, kelola member, dan unduh laporan dalam satu aplikasi online untuk kafe, restoran, dan toko.</p>
        <div class="hero-cta mt-9 flex flex-wrap justify-center gap-3">
            <a href="#auth-section" onclick="switchAuthTab('register')" class="btn-neon">Gratis selamanya <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span></a>
            <a href="#tampilan" class="btn-ghost">Lihat tampilan</a>
        </div>
        <ul class="mt-7 flex flex-wrap justify-center gap-x-7 gap-y-2 text-sm text-slate-400 font-medium">
            <li class="flex items-center gap-1.5"><span class="material-symbols-outlined text-neon" style="font-size:18px" aria-hidden="true">check</span>Tanpa kartu kredit</li>
            <li class="flex items-center gap-1.5"><span class="material-symbols-outlined text-neon" style="font-size:18px" aria-hidden="true">check</span>Setup kurang dari 5 menit</li>
            <li class="flex items-center gap-1.5"><span class="material-symbols-outlined text-neon" style="font-size:18px" aria-hidden="true">check</span>Buka di browser</li>
        </ul>
    </div>

    <div class="relative max-w-4xl mx-auto px-5 mt-10 sm:mt-16 pb-14 sm:pb-28" aria-hidden="true">
        <div class="absolute inset-x-10 top-10 bottom-10 rounded-[3rem] bg-neon/10 blur-3xl"></div>
        <div class="frame relative">
            <div class="flex items-center gap-2 px-5 py-3.5 border-b border-edge bg-panel">
                <span class="w-3 h-3 rounded-full bg-[#34364F]"></span><span class="w-3 h-3 rounded-full bg-[#34364F]"></span><span class="w-3 h-3 rounded-full bg-neon"></span>
                <span class="ml-3 text-xs font-semibold text-slate-400">Contoh tampilan</span>
                <span class="ml-auto rounded-full bg-neon/15 text-neon text-[11px] font-bold px-2.5 py-1">Live</span>
            </div>
            <div class="grid md:grid-cols-[1.15fr_1fr] gap-5 p-4 sm:p-6 text-left">
                <div class="rounded-2xl border border-edge bg-panel p-5 flex flex-col">
                    <div class="font-bold text-sm mb-4">Pesanan #1293 &bull; Meja 05</div>
                    <div class="space-y-2.5 flex-1" id="pos-rows">
                        <div class="pos-row flex items-center justify-between rounded-2xl bg-[#111222] border border-edge p-3" data-p="38000"><div class="flex items-center gap-3"><span class="w-9 h-9 rounded-xl flex items-center justify-center text-neon" style="background:rgba(210,248,57,.12)"><span class="material-symbols-outlined" style="font-size:20px">tapas</span></span><div><div class="text-sm font-bold">Chicken Dimsum</div><div class="text-[11px] text-slate-400">x1</div></div></div><span class="text-sm font-extrabold">Rp 38.000</span></div>
                        <div class="pos-row flex items-center justify-between rounded-2xl bg-[#111222] border border-edge p-3" data-p="32000"><div class="flex items-center gap-3"><span class="w-9 h-9 rounded-xl flex items-center justify-center text-neon" style="background:rgba(210,248,57,.12)"><span class="material-symbols-outlined" style="font-size:20px">emoji_food_beverage</span></span><div><div class="text-sm font-bold">Matcha Latte</div><div class="text-[11px] text-slate-400">x1</div></div></div><span class="text-sm font-extrabold">Rp 32.000</span></div>
                        <div class="pos-row flex items-center justify-between rounded-2xl bg-[#111222] border border-edge p-3" data-p="110000"><div class="flex items-center gap-3"><span class="w-9 h-9 rounded-xl flex items-center justify-center text-neon" style="background:rgba(210,248,57,.12)"><span class="material-symbols-outlined" style="font-size:20px">rice_bowl</span></span><div><div class="text-sm font-bold">Omurice Bowl</div><div class="text-[11px] text-slate-400">x2</div></div></div><span class="text-sm font-extrabold">Rp 110.000</span></div>
                    </div>
                    <div class="flex items-end justify-between pt-4 mt-4 border-t border-edge">
                        <div><div class="text-xs text-slate-400">Total</div><div class="text-2xl font-extrabold text-neon" id="pos-total">Rp 0</div></div>
                        <div class="relative h-11 min-w-[9.5rem]">
                            <span class="absolute inset-0 rounded-full bg-neon text-[#181829] text-sm font-extrabold flex items-center justify-center">Bayar QRIS</span>
                            <span class="pay-ok absolute inset-0 rounded-full bg-[#fff] text-[#181829] text-sm font-extrabold flex items-center justify-center gap-1.5" id="pos-done"><span class="material-symbols-outlined" style="font-size:18px">check_circle</span>Lunas</span>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-edge bg-panel p-5 flex flex-col">
                    <div class="text-xs text-slate-400">Omzet hari ini</div>
                    <div class="text-2xl font-extrabold mt-1">Rp 2.845.500</div>
                    <div class="text-xs text-slate-400 mt-0.5">64 transaksi</div>
                    <div class="flex items-end gap-2 h-32 mt-5 flex-1">
                        @foreach ([40, 55, 35, 70, 60, 85, 95] as $h)
                            <div class="flex-1 rounded-t-lg {{ $loop->last ? 'bg-neon' : '' }}" style="height:{{ $h }}%; {{ $loop->last ? 'box-shadow:0 0 18px rgba(210,248,57,.5)' : 'background:rgba(210,248,57,.22)' }}"></div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
