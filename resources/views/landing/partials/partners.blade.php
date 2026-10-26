<!-- DIDUKUNG OLEH -->
<section id="mitra" class="relative border-y border-edge overflow-hidden" style="background-image:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);background-size:44px 44px">
    <div class="max-w-5xl mx-auto px-5 py-20 sm:py-32 lg:py-40 text-center">
        <span class="inline-flex items-center gap-2 rounded-lg bg-neon text-[#181829] font-extrabold text-xs px-3.5 py-2 -rotate-2 rv" style="box-shadow:4px 4px 0 #272942">
            <span class="material-symbols-outlined" style="font-size:18px" aria-hidden="true">handshake</span>PARTNER
        </span>
        <h2 class="mt-6 text-3xl sm:text-5xl font-extrabold tracking-tight rv">Didukung oleh</h2>
        <p class="mt-4 text-slate-300 text-base sm:text-lg max-w-xl mx-auto rv">Terhubung dengan layanan pembayaran dan mitra agar transaksi di kasir berjalan lancar dan tercatat otomatis.</p>
        {{-- Logo dummy: ganti file di public/img/partners/ (svg/png/webp). Tambah mitra = tambah satu baris. --}}
        <div class="mt-10 sm:mt-14 flex flex-wrap items-center justify-center gap-x-10 sm:gap-x-16 gap-y-8 rv">
            @foreach ([['ums', 'UMS'], ['qris', 'QRIS'], ['midtrans', 'MIDTRANS'], ['mitra-2', 'Mitra 2']] as [$code, $name])
                @php
                    $logo = null;
                    foreach (['svg', 'png', 'webp'] as $ext) {
                        if (file_exists(public_path("img/partners/{$code}.{$ext}"))) { $logo = asset("img/partners/{$code}.{$ext}"); break; }
                    }
                @endphp
                @if ($logo)
                    <img src="{{ $logo }}" alt="{{ $name }}" loading="lazy" class="partner-logo h-9 sm:h-12 w-auto">
                @else
                    <span class="text-xl sm:text-2xl font-extrabold text-slate-500">{{ $name }}</span>
                @endif
            @endforeach
        </div>
    </div>
</section>
