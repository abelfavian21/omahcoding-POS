<!-- CARA MULAI -->
<section id="cara-kerja" class="border-y border-edge bg-panel/50 scroll-mt-16">
    <div class="max-w-6xl mx-auto px-5 py-14 sm:py-20 lg:py-28">
        <div class="max-w-xl mb-14 rv">
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Mulai jualan dalam 3 langkah.</h2>
            <p class="mt-4 text-slate-300 text-lg">Tanpa install, tanpa kartu kredit. Setup kurang dari 5 menit.</p>
        </div>
        <ol class="relative grid md:grid-cols-3 gap-10 md:gap-6 rv">
            <div class="hidden md:block absolute left-[16.66%] right-[16.66%] top-6 h-0.5 bg-edge" aria-hidden="true"><div class="tl-fill"></div></div>

            <li class="relative text-center">
                <span class="step-no relative z-10 inline-flex w-12 h-12 items-center justify-center rounded-full bg-neon text-[#181829] font-extrabold text-lg">1</span>
                <div class="card p-6 mt-6 text-left">
                    <div class="h-36 rounded-2xl bg-[#111222] border border-edge p-4 flex flex-col justify-center gap-2.5" aria-hidden="true">
                        <div class="rounded-xl border border-edge px-3 py-2 text-xs"><span class="text-slate-500">Nama usaha</span><div class="font-bold text-sm mt-0.5"><span class="type">Kopi Nusantara</span></div></div>
                        <div class="rounded-xl border border-edge px-3 py-2 text-xs text-slate-500">Email &bull; WhatsApp</div>
                        <div class="rounded-full bg-neon text-[#181829] text-xs font-extrabold text-center py-2">Buat akun</div>
                    </div>
                    <h3 class="text-lg font-bold mt-5 mb-1.5">Daftar akun</h3>
                    <p class="text-sm text-slate-300 leading-relaxed">Isi nama usaha, email, dan nomor WhatsApp. Selesai dalam hitungan menit.</p>
                </div>
            </li>

            <li class="relative text-center">
                <span class="step-no relative z-10 inline-flex w-12 h-12 items-center justify-center rounded-full bg-neon text-[#181829] font-extrabold text-lg">2</span>
                <div class="card p-6 mt-6 text-left">
                    <div class="h-36 rounded-2xl bg-[#111222] border border-edge p-4 grid grid-cols-2 gap-2.5 content-center" aria-hidden="true">
                        @foreach (['Kafe', 'Restoran', 'Retail', 'Bakery'] as $n)
                            <span class="pick rounded-xl border border-edge text-xs font-bold text-center py-3.5" style="animation-delay: {{ $loop->index * 1.5 }}s">{{ $n }}</span>
                        @endforeach
                    </div>
                    <h3 class="text-lg font-bold mt-5 mb-1.5">Pilih kategori usaha</h3>
                    <p class="text-sm text-slate-300 leading-relaxed">Alur kasir dan kategori produk langsung menyesuaikan jenis usahamu.</p>
                </div>
            </li>

            <li class="relative text-center">
                <span class="step-no relative z-10 inline-flex w-12 h-12 items-center justify-center rounded-full bg-neon text-[#181829] font-extrabold text-lg">3</span>
                <div class="card p-6 mt-6 text-left">
                    <div class="h-36 rounded-2xl bg-[#111222] border border-edge p-4 flex items-center" aria-hidden="true">
                        <div class="notif w-full rounded-2xl bg-panel border border-neon/40 p-3.5 flex items-center gap-3 shadow-[0_0_30px_-6px_rgba(210,248,57,.4)]">
                            <span class="w-10 h-10 rounded-full bg-neon text-[#181829] flex items-center justify-center"><span class="material-symbols-outlined" style="font-size:22px">check</span></span>
                            <div class="flex-1"><div class="text-sm font-bold">Pesanan pertama masuk</div><div class="text-xs text-slate-400">Rp 38.000 &bull; QRIS</div></div>
                        </div>
                    </div>
                    <h3 class="text-lg font-bold mt-5 mb-1.5">Mulai jualan</h3>
                    <p class="text-sm text-slate-300 leading-relaxed">Tambah menu, beri PIN ke kasir, lalu terima pesanan pertamamu.</p>
                </div>
            </li>
        </ol>
        <div class="mt-12 text-center rv">
            <a href="#auth-section" onclick="switchAuthTab('register')" class="btn-neon">Mulai sekarang, gratis <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span></a>
        </div>
    </div>
</section>
