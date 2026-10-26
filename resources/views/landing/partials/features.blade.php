<!-- FITUR -->
<section id="fitur" class="max-w-6xl mx-auto px-5 py-14 sm:py-20 lg:py-28">
    <div class="max-w-2xl mb-12 rv">
        <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Semua urusan toko, satu aplikasi.</h2>
        <p class="mt-4 text-slate-300 text-lg">Dari kasir sampai laporan. Tidak perlu pindah-pindah aplikasi atau rekap manual.</p>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ([
            ['point_of_sale', 'Kasir cepat', 'Layar sentuh yang ringan. Pilih menu, hitung total, bayar QRIS atau tunai, cetak struk.'],
            ['inventory_2', 'Stok & resep otomatis', 'Bahan baku berkurang sendiri setiap menu terjual. Tahu kapan harus belanja ulang.'],
            ['loyalty', 'Member & poin', 'Simpan data pelanggan, kumpulkan poin, dan beri tingkatan member agar mereka kembali.'],
            ['monitoring', 'Dashboard penjualan', 'Omzet, menu terlaris, dan jam ramai terlihat langsung dari HP atau laptop.'],
            ['description', 'Laporan PDF & Excel', 'Unduh laporan penjualan rapi untuk pembukuan, tanpa lembur rekap.'],
            ['badge', 'Shift & PIN kasir', 'Setiap kasir punya PIN sendiri. Hak akses dibatasi, jadi data keuangan tetap aman.'],
        ] as [$ic, $ttl, $txt])
            <article class="card p-6 sm:p-7 rv">
                <span class="w-12 h-12 rounded-2xl bg-neon/12 text-neon flex items-center justify-center mb-5" style="background:rgba(210,248,57,.12)"><span class="material-symbols-outlined" style="font-size:26px" aria-hidden="true">{{ $ic }}</span></span>
                <h3 class="text-lg font-bold mb-2">{{ $ttl }}</h3>
                <p class="text-sm text-slate-300 leading-relaxed">{{ $txt }}</p>
            </article>
        @endforeach
    </div>
</section>
