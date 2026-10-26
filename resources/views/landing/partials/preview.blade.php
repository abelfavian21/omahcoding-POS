<!-- PREVIEW APLIKASI -->
<section id="tampilan" class="max-w-6xl mx-auto px-5 pt-14 sm:pt-20 lg:pt-28">
    <div class="max-w-2xl mb-8 rv">
        <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Lihat tampilan aplikasinya.</h2>
        <p class="mt-4 text-slate-300 text-lg">Bersih, besar, mudah disentuh. Kasir baru bisa langsung pakai.</p>
    </div>
    <div class="flex gap-2 overflow-x-auto pb-3 mb-4" role="tablist" aria-label="Pilih tampilan">
        @foreach ([['point_of_sale', 'Kasir'], ['monitoring', 'Dashboard'], ['inventory_2', 'Stok'], ['loyalty', 'Member']] as $k => [$ic, $t])
            <button type="button" role="tab" data-tab class="tab"><span class="material-symbols-outlined" style="font-size:18px" aria-hidden="true">{{ $ic }}</span>{{ $t }}</button>
        @endforeach
    </div>
    <div class="frame rv">
        <div class="flex items-center gap-2 px-5 py-3.5 border-b border-edge bg-panel">
            <span class="w-3 h-3 rounded-full bg-[#34364F]"></span><span class="w-3 h-3 rounded-full bg-[#34364F]"></span><span class="w-3 h-3 rounded-full bg-neon"></span>
            <span class="ml-3 text-xs font-semibold text-slate-400">CashierStore</span>
        </div>
        <div class="p-4 sm:p-6 min-h-[22rem]">
            <!-- Kasir -->
            <div data-pane class="grid md:grid-cols-[1fr_17rem] gap-5">
                <div>
                    <div class="flex gap-2 mb-4 overflow-x-auto text-xs font-bold">
                        <span class="rounded-full bg-neon text-[#181829] px-3.5 py-2">Semua</span>
                        @foreach (['Kopi', 'Makanan', 'Snack'] as $c)<span class="rounded-full border border-edge px-3.5 py-2 text-slate-300">{{ $c }}</span>@endforeach
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach ([['coffee', 'Americano', 'Rp 22.000'], ['emoji_food_beverage', 'Matcha Latte', 'Rp 32.000'], ['tapas', 'Chicken Dimsum', 'Rp 38.000'], ['rice_bowl', 'Omurice Bowl', 'Rp 55.000'], ['bakery_dining', 'Croissant', 'Rp 28.000'], ['icecream', 'Affogato', 'Rp 30.000']] as [$ic, $n, $pr])
                            <div class="rounded-2xl border border-edge bg-panel p-3.5">
                                <span class="w-10 h-10 rounded-xl flex items-center justify-center text-neon mb-3" style="background:rgba(210,248,57,.12)"><span class="material-symbols-outlined" aria-hidden="true">{{ $ic }}</span></span>
                                <div class="text-sm font-bold">{{ $n }}</div><div class="text-xs text-slate-400 mt-0.5">{{ $pr }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="rounded-2xl border border-edge bg-panel p-4 flex flex-col">
                    <div class="font-bold text-sm mb-3">Pesanan #1293</div>
                    <div class="space-y-2.5 text-sm flex-1">
                        @foreach ([['Chicken Dimsum x1', 'Rp 38.000'], ['Matcha Latte x1', 'Rp 32.000'], ['Omurice Bowl x2', 'Rp 110.000']] as [$n, $pr])
                            <div class="flex justify-between gap-2"><span class="text-slate-300">{{ $n }}</span><span class="font-bold">{{ $pr }}</span></div>
                        @endforeach
                    </div>
                    <div class="flex justify-between items-end border-t border-edge pt-3 mt-4"><span class="text-xs text-slate-400">Total</span><span class="text-xl font-extrabold text-neon">Rp 180.000</span></div>
                    <div class="mt-3 rounded-full bg-neon text-[#181829] text-center font-extrabold text-sm py-3">Bayar QRIS</div>
                </div>
            </div>
            <!-- Dashboard -->
            <div data-pane hidden>
                <div class="grid sm:grid-cols-3 gap-3 mb-5">
                    @foreach ([['Omzet hari ini', 'Rp 2.845.500'], ['Transaksi', '64'], ['Menu terlaris', 'Matcha Latte']] as [$l, $v])
                        <div class="rounded-2xl border border-edge bg-panel p-4"><div class="text-xs text-slate-400">{{ $l }}</div><div class="text-xl font-extrabold mt-1 {{ $loop->first ? 'text-neon' : '' }}">{{ $v }}</div></div>
                    @endforeach
                </div>
                <div class="grid md:grid-cols-[1.6fr_1fr] gap-5">
                    <div class="rounded-2xl border border-edge bg-panel p-4">
                        <div class="text-sm font-bold mb-4">Penjualan 7 hari</div>
                        <div class="flex items-end gap-2 sm:gap-3 h-44">
                            @foreach ([['Sen', 40], ['Sel', 55], ['Rab', 35], ['Kam', 70], ['Jum', 60], ['Sab', 85], ['Min', 95]] as [$d, $h])
                                <div class="flex-1 h-full flex flex-col justify-end items-center gap-2"><div class="bar w-full {{ $loop->last ? 'hi' : '' }}" style="--h:{{ $h }}%"></div><span class="text-[11px] text-slate-400">{{ $d }}</span></div>
                            @endforeach
                        </div>
                    </div>
                    <div class="rounded-2xl border border-edge bg-panel p-4">
                        <div class="text-sm font-bold mb-4">Menu terlaris</div>
                        <div class="space-y-3.5 text-sm">
                            @foreach ([['Matcha Latte', 42], ['Omurice Bowl', 31], ['Chicken Dimsum', 27]] as [$n, $q])
                                <div><div class="flex justify-between mb-1.5"><span>{{ $n }}</span><span class="text-slate-400">{{ $q }} terjual</span></div><div class="h-2 rounded-full bg-[#272942]"><div class="fill" style="--w:{{ $q * 2 }}%"></div></div></div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <!-- Stok -->
            <div data-pane hidden>
                <div class="rounded-2xl border border-edge bg-panel divide-y divide-[#272942]">
                    @foreach ([['Susu oat', '2,4 L', 72, false], ['Biji kopi', '5 kg', 46, false], ['Bubuk matcha', '300 g', 12, true], ['Telur', '38 butir', 64, false], ['Beras', '9 kg', 55, false]] as [$n, $q, $w, $low])
                        <div class="flex flex-wrap sm:flex-nowrap items-center gap-x-4 gap-y-2.5 p-4 text-sm">
                            <div class="flex-1 sm:flex-none sm:w-44 font-bold">{{ $n }}</div>
                            <div class="order-last w-full sm:order-none sm:w-auto sm:flex-1 h-2 rounded-full bg-[#272942]"><div class="fill {{ $low ? 'low' : '' }}" style="--w:{{ $w }}%"></div></div>
                            <div class="sm:w-20 text-right text-slate-300">{{ $q }}</div>
                            <div class="hidden sm:block w-24 text-right">@if ($low)<span class="rounded-full bg-amber-400/15 text-amber-300 text-[11px] font-bold px-2.5 py-1">Menipis</span>@else<span class="rounded-full bg-neon/15 text-neon text-[11px] font-bold px-2.5 py-1">Aman</span>@endif</div>
                        </div>
                    @endforeach
                </div>
            </div>
            <!-- Member -->
            <div data-pane hidden>
                <div class="grid sm:grid-cols-2 gap-3">
                    @foreach ([['Rina A.', 'Gold', '1.240 poin'], ['Budi S.', 'VIP', '3.870 poin'], ['Dewi K.', 'Silver', '560 poin'], ['Andi P.', 'Bronze', '120 poin']] as [$n, $t, $po])
                        <div class="rounded-2xl border border-edge bg-panel p-4 flex items-center gap-4">
                            <span class="w-11 h-11 rounded-full bg-neon text-[#181829] font-extrabold flex items-center justify-center">{{ mb_substr($n, 0, 1) }}</span>
                            <div class="flex-1"><div class="font-bold text-sm">{{ $n }}</div><div class="text-xs text-slate-400">{{ $po }}</div></div>
                            <span class="rounded-full border border-neon/40 text-neon text-[11px] font-bold px-3 py-1">{{ $t }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <p class="mt-3 text-xs text-slate-500">Tampilan contoh. Data hanya ilustrasi.</p>
</section>
