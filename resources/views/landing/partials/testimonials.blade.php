{{-- CONTOH TESTIMONI: GANTI dengan testimoni pelanggan asli sebelum rilis. Kosongkan array agar section hilang. Urutan: [kutipan, peran, inisial, fitur, ikon]. Item pertama tampil besar. --}}
@php
    $testimonials = [
        ['Antrean jam makan siang jauh lebih lancar. Kasir baru latihan sebentar, langsung bisa jalan sendiri.', 'Pemilik kafe', 'K', 'Kasir cepat', 'point_of_sale'],
        ['Stok bahan berkurang sendiri tiap menu terjual. Saya tahu kapan harus belanja, tidak lagi menebak.', 'Pemilik restoran', 'R', 'Stok otomatis', 'inventory_2'],
        ['Laporan harian rapi, tinggal unduh. Tidak ada lagi lembur rekap di akhir bulan.', 'Pemilik toko', 'T', 'Laporan', 'description'],
    ];
@endphp
@if (count($testimonials) >= 3)
    <section id="testimoni" class="max-w-6xl mx-auto px-5 py-14 sm:py-20 lg:py-28">
        <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight max-w-xl mb-10 rv">Kata pemilik usaha.</h2>
        <div class="grid lg:grid-cols-5 gap-5">
            @php [$q, $role, $ini, $tag, $ic] = $testimonials[0]; @endphp
            <figure class="lg:col-span-3 lg:row-span-2 relative overflow-hidden rounded-[2rem] bg-neon text-[#181829] p-6 sm:p-12 flex flex-col justify-between min-h-[18rem] sm:min-h-[22rem] rv">
                <span class="absolute -top-6 right-6 text-[12rem] leading-none font-extrabold text-black/10 select-none" aria-hidden="true">&rdquo;</span>
                <span class="relative inline-flex items-center gap-1.5 self-start rounded-full bg-[#181829] text-neon text-xs font-bold px-3 py-1.5"><span class="material-symbols-outlined" style="font-size:16px" aria-hidden="true">{{ $ic }}</span>{{ $tag }}</span>
                <blockquote class="relative text-xl sm:text-3xl font-extrabold leading-snug tracking-tight my-8">{{ $q }}</blockquote>
                <figcaption class="relative flex items-center gap-3">
                    <span class="w-11 h-11 rounded-full bg-[#181829] text-neon font-extrabold flex items-center justify-center">{{ $ini }}</span>
                    <span class="font-bold">{{ $role }}</span>
                </figcaption>
            </figure>
            @foreach (array_slice($testimonials, 1, 2) as [$q, $role, $ini, $tag, $ic])
                <figure class="card lg:col-span-2 p-7 flex flex-col rv">
                    <span class="inline-flex items-center gap-1.5 self-start rounded-full border border-neon/40 text-neon text-xs font-bold px-3 py-1.5"><span class="material-symbols-outlined" style="font-size:16px" aria-hidden="true">{{ $ic }}</span>{{ $tag }}</span>
                    <blockquote class="text-lg font-semibold leading-snug my-5 flex-1">{{ $q }}</blockquote>
                    <figcaption class="flex items-center gap-3 text-sm">
                        <span class="w-9 h-9 rounded-full bg-neon text-[#181829] font-extrabold flex items-center justify-center">{{ $ini }}</span>
                        <span class="font-bold text-slate-300">{{ $role }}</span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </section>
@endif
