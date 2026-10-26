<!-- MARQUEE -->
<section class="border-y border-edge bg-panel/50 py-4 overflow-hidden" aria-label="Cocok untuk">
    <div class="mq"><div class="marquee-track text-sm font-bold text-slate-300">
        @for ($i = 0; $i < 2; $i++)
            @foreach (['Kafe & Coffee Shop', 'Restoran', 'Minimarket & Retail', 'Bakery & Pastry', 'Warung & Kedai', 'Toko Kelontong'] as $t)
                <span class="flex items-center gap-8 px-4 whitespace-nowrap">{{ $t }} <span class="w-1.5 h-1.5 rounded-full bg-neon"></span></span>
            @endforeach
        @endfor
    </div></div>
</section>
