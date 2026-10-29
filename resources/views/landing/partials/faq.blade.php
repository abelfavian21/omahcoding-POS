<!-- FAQ -->
<section id="faq" class="max-w-3xl mx-auto px-5 py-14 sm:py-20 lg:py-28">
    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-10 rv">Pertanyaan yang sering muncul</h2>
    <div class="space-y-3">
        @foreach ([
            ['Apakah CashierStore gratis?', 'Ya. Semua fitur bisa dipakai gratis selamanya, tanpa kartu kredit.'],
            ['Perlu install aplikasi atau server?', 'Tidak. CashierStore berbasis cloud dan dibuka lewat browser di laptop, tablet, atau HP.'],
            ['Bagaimana kasir login?', 'Kasir masuk dengan kode toko dan PIN 4 sampai 6 digit. Pemilik masuk dengan email dan kata sandi.'],
            ['Metode pembayaran apa yang didukung kasir?', 'Kasir menerima QRIS otomatis lewat Midtrans, QRIS manual dengan gambar QRIS tokomu, dan tunai. QRIS otomatis memakai akun Midtrans milik tokomu sendiri.'],
            ['Cocok untuk usaha apa saja?', 'Kafe, restoran, minimarket, bakery, dan usaha lain. Pilih kategori saat registrasi, alur kasir menyesuaikan.'],
        ] as [$q, $a])
            <details class="card !rounded-2xl group">
                <summary class="flex items-center justify-between gap-4 p-5 font-bold">
                    <span>{{ $q }}</span>
                    <span class="faq-ic material-symbols-outlined text-neon transition-transform" aria-hidden="true">add</span>
                </summary>
                <p class="px-5 pb-5 text-sm text-slate-300 leading-relaxed">{{ $a }}</p>
            </details>
        @endforeach
    </div>
</section>
