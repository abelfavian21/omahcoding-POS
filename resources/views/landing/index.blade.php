<!DOCTYPE html>
<html lang="id" class="dark scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>CashierStore - Aplikasi Kasir POS Online untuk Kafe, Restoran & Toko</title>
    <meta name="description" content="CashierStore: aplikasi kasir (POS) online untuk kafe, restoran, dan toko. Kasir cepat, stok bahan otomatis, member loyalty, dan laporan PDF/Excel. Gratis 14 hari, setup 5 menit.">
    <meta name="keywords" content="aplikasi kasir, aplikasi POS, kasir online, kasir kafe, kasir restoran, program kasir toko, manajemen stok, laporan penjualan">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta name="theme-color" content="#0E0F1A">
    <link rel="canonical" href="{{ url('/') }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="CashierStore">
    <meta property="og:title" content="CashierStore - Aplikasi Kasir POS Online untuk Kafe, Restoran & Toko">
    <meta property="og:description" content="Kasir cepat, stok bahan otomatis, member loyalty, laporan rapi. Gratis 14 hari.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="CashierStore - Aplikasi Kasir POS Online">
    <meta name="twitter:description" content="Kasir, stok, member, dan laporan dalam satu aplikasi. Gratis 14 hari.">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='9' fill='%23D2F839'/%3E%3Cpath d='M8 10h16v4H8zm0 6h16v6H8z' fill='%23181829'/%3E%3C/svg%3E">
    <script type="application/ld+json">
    [
      {
        "@@context": "https://schema.org",
        "@@type": "SoftwareApplication",
        "name": "CashierStore",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "inLanguage": "id",
        "url": "{{ url('/') }}",
        "description": "Aplikasi kasir (POS) online untuk kafe, restoran, dan toko: kasir, stok bahan, member, dan laporan.",
        "offers": { "@@type": "Offer", "price": "0", "priceCurrency": "IDR", "description": "Uji coba gratis 14 hari" }
      },
      {
        "@@context": "https://schema.org",
        "@@type": "FAQPage",
        "mainEntity": [
          { "@@type": "Question", "name": "Apakah CashierStore gratis?", "acceptedAnswer": { "@@type": "Answer", "text": "Semua fitur bisa dipakai gratis selama 14 hari pertama, tanpa kartu kredit." } },
          { "@@type": "Question", "name": "Perlu install aplikasi atau server?", "acceptedAnswer": { "@@type": "Answer", "text": "Tidak. CashierStore berbasis cloud dan dibuka lewat browser di laptop, tablet, atau HP." } },
          { "@@type": "Question", "name": "Bagaimana kasir login?", "acceptedAnswer": { "@@type": "Answer", "text": "Kasir masuk dengan kode toko dan PIN 4 sampai 6 digit. Pemilik masuk dengan email dan kata sandi." } },
          { "@@type": "Question", "name": "Metode pembayaran apa yang didukung kasir?", "acceptedAnswer": { "@@type": "Answer", "text": "Kasir menerima QRIS otomatis lewat Midtrans, QRIS manual dengan gambar QRIS tokomu, dan tunai. QRIS otomatis memakai akun Midtrans milik tokomu sendiri." } },
          { "@@type": "Question", "name": "Cocok untuk usaha apa saja?", "acceptedAnswer": { "@@type": "Answer", "text": "Kafe, restoran, minimarket, bakery, dan usaha lain. Pilih kategori saat registrasi, alur kasir menyesuaikan." } }
        ]
      }
    ]
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL@20..48,400,0..1&display=swap" rel="stylesheet">
    <script>
        const lime = { 50:'#F6FDE3',100:'#EBFCC2',200:'#E1F98F',300:'#D2F839',400:'#C2EA1C',500:'#9DBA00',600:'#536500',700:'#445400',800:'#3E4C00',900:'#2C3600' };
        tailwind.config = { theme: { extend: {
            fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
            colors: { blue: lime, indigo: lime, neon: '#D2F839', ink: '#0E0F1A', panel: '#181928', edge: '#272942' }
        } } }
    </script>
    @verbatim
    <style>
        :root { --neon:#D2F839; --ink:#0E0F1A; --panel:#181928; --edge:#272942; }
        html { -webkit-tap-highlight-color: transparent; }
        body { background: var(--ink); color: #F1F2F8; padding-top: env(safe-area-inset-top, 0px); }
        ::selection { background: var(--neon); color: #181829; }
        a:focus-visible, button:focus-visible, summary:focus-visible, input:focus-visible { outline: 2px solid var(--neon); outline-offset: 3px; }
        .material-symbols-outlined { font-size: 22px; vertical-align: middle; font-variation-settings: 'FILL' 0; }

        /* tombol neon */
        .btn-neon { white-space:nowrap; display:inline-flex; align-items:center; justify-content:center; gap:.5rem; height:3rem; padding:0 1.5rem; border-radius:999px; background:var(--neon); color:#181829; font-weight:800; font-size:.9rem; transition:transform .15s, box-shadow .2s; box-shadow:0 0 0 0 rgba(210,248,57,.5); }
        .btn-neon:hover { transform: translateY(-2px); box-shadow: 0 0 28px rgba(210,248,57,.45); }
        .btn-ghost { display:inline-flex; align-items:center; justify-content:center; gap:.5rem; height:3rem; padding:0 1.5rem; border-radius:999px; border:1px solid var(--edge); color:#F1F2F8; font-weight:700; font-size:.9rem; transition:border-color .2s, background .2s; }
        .btn-ghost:hover { border-color: var(--neon); background: rgba(210,248,57,.06); }

        /* latar: grid + glow */
        .hero-bg { background-image: radial-gradient(60% 50% at 75% 20%, rgba(210,248,57,.16), transparent 70%), linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px); background-size: auto, 44px 44px, 44px 44px; -webkit-mask-image: linear-gradient(#000 70%, transparent); mask-image: linear-gradient(#000 70%, transparent); }
        .neon-text { color: var(--neon); text-shadow: 0 0 32px rgba(210,248,57,.35); }
        .card { background: var(--panel); border: 1px solid var(--edge); border-radius: 1.5rem; transition: border-color .25s, transform .25s, box-shadow .25s; }
        .card:hover { border-color: rgba(210,248,57,.5); transform: translateY(-3px); box-shadow: 0 0 30px -8px rgba(210,248,57,.3); }

        /* mockup kasir (animasi hero) */
        .pos-row { opacity: 0; transform: translateY(10px); transition: opacity .45s, transform .45s; }
        .pos-row.on { opacity: 1; transform: none; }
        .pay-ok { opacity: 0; transform: scale(.9); transition: opacity .3s, transform .3s; }
        .pay-ok.on { opacity: 1; transform: none; }
        @keyframes floaty { 0%,100% { transform: translateY(0) } 50% { transform: translateY(-8px) } }
        .floaty { animation: floaty 6s ease-in-out infinite; }
        @keyframes marquee { to { transform: translateX(-50%); } }
        .marquee-track { display:flex; width:max-content; animation: marquee 28s linear infinite; }
        @keyframes pingdot { 75%,100% { transform: scale(2.2); opacity: 0; } }
        .dot-live::after { content:""; position:absolute; inset:0; border-radius:999px; background:var(--neon); animation: pingdot 1.6s infinite; }

        /* marquee 2 baris */
        .mq { -webkit-mask-image: linear-gradient(90deg, transparent, #000 10%, #000 90%, transparent); mask-image: linear-gradient(90deg, transparent, #000 10%, #000 90%, transparent); }
        .mq:hover .marquee-track { animation-play-state: paused; }
        .marquee-rev { animation-direction: reverse; animation-duration: 34s; }
        .mq-pill { display:inline-flex; align-items:center; gap:.6rem; margin:0 .4rem; padding:.65rem 1.25rem; border-radius:999px; border:1px solid var(--edge); background:var(--panel); font-weight:700; font-size:.9rem; color:#E2E4EE; white-space:nowrap; transition:border-color .2s, box-shadow .2s, color .2s; }
        .mq-pill:hover { border-color:var(--neon); color:#fff; box-shadow:0 0 22px -4px rgba(210,248,57,.45); }
        .mq-pill .material-symbols-outlined { color:var(--neon); font-size:20px; }
        .mq-pill.alt { background:var(--neon); color:#181829; border-color:transparent; }
        .mq-pill.alt .material-symbols-outlined { color:#181829; }
        /* preview aplikasi */
        .tab { display:inline-flex; align-items:center; gap:.4rem; height:2.5rem; padding:0 1rem; border-radius:999px; border:1px solid var(--edge); background:var(--panel); color:#C5C8D8; font-weight:700; font-size:.85rem; white-space:nowrap; transition:background .2s, color .2s; }
        .tab:hover { border-color:rgba(210,248,57,.5); }
        .tab.on { background:var(--neon); color:#181829; border-color:transparent; }
        .frame { background:#111222; border:1px solid var(--edge); border-radius:1.75rem; overflow:hidden; box-shadow:0 30px 80px -30px rgba(210,248,57,.25); }
        .bar { height:0; border-radius:.6rem .6rem .2rem .2rem; background:repeating-linear-gradient(45deg, rgba(210,248,57,.6) 0 3px, rgba(210,248,57,.15) 3px 8px); transition:height .9s cubic-bezier(.2,.8,.2,1); }
        .bar.hi { background:var(--neon); box-shadow:0 0 18px rgba(210,248,57,.5); }
        [data-pane].on .bar { height:var(--h); }
        [data-pane].on .fill { width:var(--w); }
        .fill { width:0; height:100%; border-radius:999px; background:var(--neon); transition:width .9s cubic-bezier(.2,.8,.2,1); }
        .fill.low { background:#FBBF24; }
        [data-pane] { animation: paneIn .45s ease both; }
        @keyframes paneIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:none; } }
        @media (prefers-reduced-motion: reduce) { .bar { height:var(--h); transition:none; } .fill { width:var(--w); transition:none; } [data-pane] { animation:none; } }
        /* cara mulai */
        .tl-fill { width:0; height:100%; background:var(--neon); box-shadow:0 0 14px rgba(210,248,57,.7); transition:width 1.8s cubic-bezier(.4,0,.2,1) .3s; }
        .in .tl-fill { width:100%; }
        .step-no { box-shadow:0 0 0 6px var(--ink), 0 0 26px rgba(210,248,57,.45); }
        .type { display:inline-block; width:0; overflow:hidden; white-space:nowrap; vertical-align:bottom; border-right:2px solid var(--neon); animation: typing 3.2s steps(14) infinite alternate; }
        @keyframes typing { from { width:0 } 70%,to { width:14ch } }
        .pick { animation: pick 6s infinite; }
        @keyframes pick { 0%,20% { background:var(--neon); color:#181829; border-color:transparent; } 25%,100% { background:transparent; color:#C5C8D8; border-color:var(--edge); } }
        .notif { animation: notif 4.5s infinite; }
        @keyframes notif { 0% { opacity:0; transform:translateY(14px) scale(.96); } 12%,80% { opacity:1; transform:none; } 100% { opacity:0; transform:translateY(-6px); } }
        @media (prefers-reduced-motion: reduce) { .type { animation:none; width:14ch; } .pick, .notif { animation:none; } .tl-fill { transition:none; } }
        /* nyaman di HP */
        @media (max-width: 639px) {
            input, select, textarea { font-size: 16px !important; }
            .hero-cta { flex-direction: column; }
            .hero-cta > a { width: 100%; }
        }
        /* form gelap + lega di HP */
        #auth-section input:not([type=checkbox]):not([type=radio]), #auth-section select, #auth-section textarea { background-color:#111222; color:#F1F2F8; border-color:#34364F; }
        #auth-section input::placeholder { color:#6F7491; }
        #auth-section select option { background:#181928; color:#F1F2F8; }
        #auth-section input:-webkit-autofill { -webkit-box-shadow:0 0 0 1000px #111222 inset; -webkit-text-fill-color:#F1F2F8; caret-color:#F1F2F8; }
        #auth-section input:focus, #auth-section select:focus { border-color:var(--neon); }
        @media (max-width: 639px) {
            #auth-section label { text-transform:none !important; letter-spacing:0 !important; font-size:13px !important; font-weight:700; margin-bottom:.5rem !important; }
            #auth-section .space-y-4 > * + * { margin-top:1.35rem; }
            #auth-section .space-y-5 > * + * { margin-top:1.5rem; }
            #auth-section h3.text-xl { font-size:1.125rem; }
            #auth-section input:not([type=checkbox]), #auth-section select { min-height:3.25rem; }
        }
        .partner-logo { filter: brightness(0) invert(1); opacity:.5; transition: opacity .25s, transform .25s; }
        .partner-logo:hover { opacity:1; transform: translateY(-2px); }
        .grid-bg { background-image: linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px); background-size: 44px 44px; -webkit-mask-image: radial-gradient(ellipse 80% 75% at 50% 25%, #000 35%, transparent 100%); mask-image: radial-gradient(ellipse 80% 75% at 50% 25%, #000 35%, transparent 100%); }
        /* muncul saat scroll */
        .rv { opacity: 0; transform: translateY(18px); transition: opacity .6s, transform .6s; }
        .rv.in { opacity: 1; transform: none; }
        @media (prefers-reduced-motion: reduce) {
            .rv, .pos-row, .pay-ok { opacity: 1 !important; transform: none !important; transition: none; }
            .floaty, .marquee-track, .dot-live::after { animation: none; }
        }
        details > summary { list-style: none; cursor: pointer; }
        details > summary::-webkit-details-marker { display: none; }
        details[open] .faq-ic { transform: rotate(45deg); }

        /* ===== tema gelap untuk form login/registrasi (class bawaan) ===== */
        .dark .bg-white, .dark .bg-slate-50 { background-color: #181928; }
        .dark .bg-slate-50\/70 { background-color: #0E0F1A; }
        .dark .bg-slate-100, .dark .hover\:bg-slate-200:hover { background-color: #272942; }
        .dark .border-slate-100, .dark .border-slate-200, .dark .border-slate-200\/90 { border-color: #272942; }
        .dark .border-slate-300 { border-color: #4A4D6A; }
        .dark .text-slate-900, .dark .text-slate-800, .dark .hover\:text-slate-900:hover { color: #F8F9FC; }
        .dark .text-slate-700 { color: #E2E4EE; }
        .dark .text-slate-600 { color: #C5C8D8; }
        .dark .text-slate-500, .dark .text-slate-400 { color: #A5A9B8; }
        .dark .text-blue-600, .dark .text-blue-700, .dark .text-blue-800 { color: #D2F839; }
        .dark .bg-blue-50, .dark .bg-blue-50\/50 { background-color: rgba(210,248,57,.08); }
        .dark .bg-blue-100 { background-color: rgba(210,248,57,.16); }
        .dark .border-blue-200 { border-color: rgba(210,248,57,.35); }
        .dark .border-blue-600, .dark .focus\:border-blue-600:focus { border-color: #D2F839; }
        .dark .focus\:ring-blue-100:focus, .dark .focus\:ring-blue-500:focus { --tw-ring-color: rgba(210,248,57,.3); }
        .dark .bg-blue-600, .dark .hover\:bg-blue-700:hover, .dark .bg-emerald-600, .dark .hover\:bg-emerald-700:hover { background-color: #D2F839; color: #181829; }
        .dark .shadow-blue-600\/30, .dark .shadow-emerald-600\/30, .dark .shadow-emerald-500\/20 { --tw-shadow-color: rgba(210,248,57,.25); }
        .dark .bg-emerald-100 { background-color: rgba(210,248,57,.16); }
        .dark .text-emerald-600, .dark .text-emerald-500 { color: #D2F839; }
        .dark .bg-rose-50 { background-color: rgba(239,68,68,.13); }
        .dark .border-rose-200 { border-color: rgba(239,68,68,.35); }
        .dark .text-rose-600, .dark .text-rose-700 { color: #F87171; }
        .dark input[type=checkbox] { accent-color: #D2F839; }
        .dark ::-webkit-scrollbar { width: 8px; } .dark ::-webkit-scrollbar-thumb { background: #34364F; border-radius: 999px; }
    </style>
    @endverbatim
</head>

<body class="font-sans antialiased overflow-x-hidden">

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

    <main>
    <!-- HERO (versi clean: tengah, lega, produk di bawah) -->
    <section class="relative overflow-hidden">
        <div class="grid-bg absolute inset-0" aria-hidden="true"></div>
        <div class="absolute inset-x-0 top-0 h-[34rem]" style="background:radial-gradient(50% 60% at 50% 0%, rgba(210,248,57,.13), transparent 72%)" aria-hidden="true"></div>
        <div class="relative max-w-4xl mx-auto px-5 pt-10 sm:pt-24 text-center">
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.06]" style="text-wrap:balance">Aplikasi kasir yang cepat, laporan yang rapi.</h1>
            <p class="mt-6 text-lg sm:text-xl text-slate-300 leading-relaxed max-w-2xl mx-auto">Catat penjualan, pantau stok, kelola member, dan unduh laporan dalam satu aplikasi online untuk kafe, restoran, dan toko.</p>
            <div class="hero-cta mt-9 flex flex-wrap justify-center gap-3">
                <a href="#auth-section" onclick="switchAuthTab('register')" class="btn-neon">Coba gratis 14 hari <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span></a>
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
                                <span class="pay-ok absolute inset-0 rounded-full bg-white text-[#181829] text-sm font-extrabold flex items-center justify-center gap-1.5" id="pos-done"><span class="material-symbols-outlined" style="font-size:18px">check_circle</span>Lunas</span>
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

                    {{-- ========== LOGIN ========== --}}
                    <div id="view-login" class="p-5 sm:p-12 max-w-xl mx-auto hidden">
                        <div class="text-center space-y-2 mb-6">
                            <h3 class="text-2xl font-black text-slate-900">Selamat Datang Kembali!</h3>
                            <p class="text-slate-500 text-xs">Akses dashboard, terminal kasir, dan manajemen toko Anda.
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-1 p-1 bg-slate-100 rounded-xl mb-6 text-xs font-bold">
                            <button type="button" id="mode-email" onclick="switchLoginMode('email')"
                                class="py-2.5 rounded-lg">Pemilik / Admin</button>
                            <button type="button" id="mode-pin" onclick="switchLoginMode('pin')"
                                class="py-2.5 rounded-lg">Kasir (PIN)</button>
                        </div>

                        <form id="form-email" method="POST" action="{{ route('login.password') }}"
                            class="space-y-5">
                            @csrf
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Email</label>
                                <input type="email" name="email" required autocomplete="username"
                                    value="{{ old('email') }}" placeholder="owner@tokomu.id"
                                    class="{{ $inp }}">
                                @error('email', 'login')
                                    <p class="text-xs text-rose-600 font-semibold mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Kata
                                    Sandi</label>
                                <input type="password" name="password" required autocomplete="current-password"
                                    placeholder="••••••••••••" class="{{ $inp }}">
                            </div>
                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
                                <input type="checkbox" name="remember" value="1"
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"> <span>Ingat
                                    akun di perangkat ini</span>
                            </label>
                            <button type="submit"
                                class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-black text-sm rounded-xl shadow-lg shadow-blue-600/30 transition-all">Masuk
                                ke Dashboard CashierStore</button>
                        </form>

                        <form id="form-pin" method="POST" action="{{ route('login.pin') }}"
                            class="space-y-5 hidden">
                            @csrf
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Kode
                                    Toko</label>
                                <input type="text" name="store_code" required value="{{ old('store_code') }}"
                                    placeholder="CS-ORELY-0001" autocapitalize="characters"
                                    class="{{ $inp }} uppercase">
                                <p class="text-[11px] text-slate-400 mt-1.5">Kode toko ada di layar konfirmasi saat
                                    registrasi. Tanyakan ke pemilik jika lupa.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">PIN
                                    Kasir</label>
                                <input type="password" name="pin" required inputmode="numeric" maxlength="6"
                                    pattern="\d{4,6}" placeholder="••••••" autocomplete="off"
                                    class="{{ $inp }} tracking-[0.6em] text-center">
                                @error('pin', 'login')
                                    <p class="text-xs text-rose-600 font-semibold mt-1.5">{{ $message }}</p>
                                @enderror
                                @error('store_code', 'login')
                                    <p class="text-xs text-rose-600 font-semibold mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit"
                                class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-black text-sm rounded-xl shadow-lg shadow-blue-600/30 transition-all">Masuk
                                ke Terminal Kasir</button>
                        </form>

                        <div class="mt-8 pt-6 border-t border-slate-100 text-center text-xs text-slate-500">
                            Belum punya akun bisnis? <button type="button" onclick="switchAuthTab('register')"
                                class="font-bold text-blue-600 hover:underline">Registrasi Usaha Baru</button>
                        </div>
                    </div>

                    {{-- ========== REGISTRASI 3 LANGKAH ========== --}}
                    <div id="view-register" class="p-5 sm:p-10">
                        <div class="max-w-2xl mx-auto mb-10">
                            <div class="flex items-center justify-between relative">
                                <div class="absolute left-0 top-5 w-full h-1 bg-slate-100"></div>
                                <div id="stepper-line"
                                    class="absolute left-0 top-5 w-0 h-1 bg-blue-600 transition-all duration-300">
                                </div>
                                @foreach (['Data Pemilik', 'Kategori Usaha', 'Konfirmasi Usaha'] as $i => $lbl)
                                    <div class="flex flex-col items-center relative">
                                        <div id="step-badge-{{ $i + 1 }}"
                                            class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 font-black text-sm flex items-center justify-center transition-all">
                                            {{ $i + 1 }}</div>
                                        <span id="step-label-{{ $i + 1 }}"
                                            class="text-[11px] font-bold text-slate-400 mt-2">{{ $lbl }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if (old('_form') === 'register' && $errors->any())
                            <div
                                class="max-w-xl mx-auto mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700">
                                <ul class="list-disc pl-4 space-y-1">
                                    @foreach ($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form id="register-form" method="POST" action="{{ route('register') }}" novalidate>
                            @csrf
                            <input type="hidden" name="_form" value="register">
                            <input type="hidden" name="category" id="reg-category" value="{{ $selectedSlug }}">

                            <div id="register-server-errors"
                                class="hidden max-w-xl mx-auto mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700"
                                role="alert" aria-live="polite">
                                <ul id="register-server-errors-list" class="list-disc pl-4 space-y-1"></ul>
                            </div>

                            {{-- Langkah 1 --}}
                            <div id="step-1-content" class="max-w-xl mx-auto space-y-6 hidden">
                                <div class="text-center space-y-1">
                                    <h3 class="text-xl font-black text-slate-900">Langkah 1: Registrasi Akun Pemilik
                                    </h3>
                                    <p class="text-xs text-slate-500">Buat identitas akun utama yang akan mengelola
                                        seluruh toko &amp; staf.</p>
                                </div>
                                <div class="space-y-4">
                                    <div><label
                                            class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama
                                            Lengkap Pemilik</label>
                                        <input id="reg-owner-name" name="owner_name" required maxlength="120"
                                            value="{{ old('owner_name') }}" placeholder="Contoh: Budi Santoso"
                                            autocomplete="name" class="{{ $inp }}">
                                        <p id="err-owner_name" class="hidden mt-1.5 text-xs font-semibold text-rose-600" role="alert"></p>
                                    </div>
                                    <div><label
                                            class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama
                                            Usaha / Brand</label>
                                        <input id="reg-business-name" name="business_name" required maxlength="120"
                                            value="{{ old('business_name') }}" placeholder="Contoh: Kopi Nusantara"
                                            class="{{ $inp }}">
                                        <p id="err-business_name" class="hidden mt-1.5 text-xs font-semibold text-rose-600" role="alert"></p>
                                    </div>
                                    <div class="grid sm:grid-cols-2 gap-4">
                                        <div><label
                                                class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email
                                                Bisnis</label>
                                            <input id="reg-email" type="email" name="email" required
                                                value="{{ old('email') }}" placeholder="owner@tokomu.id"
                                                autocomplete="email" class="{{ $inp }}">
                                            <p id="err-email" class="hidden mt-1.5 text-xs font-semibold text-rose-600" role="alert"></p>
                                        </div>
                                        <div><label
                                                class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor
                                                WhatsApp Aktif</label>
                                            <input id="reg-phone" type="tel" name="phone" required
                                                minlength="8" value="{{ old('phone') }}"
                                                placeholder="0812-xxxx-xxxx" autocomplete="tel" class="{{ $inp }}">
                                            <p id="err-phone" class="hidden mt-1.5 text-xs font-semibold text-rose-600" role="alert"></p>
                                        </div>
                                    </div>
                                    <livewire:register-password-field />
                                </div>
                                <div class="pt-4">
                                    <button
                                        type="button"
                                        id="btn-next-step-1"
                                        onclick="nextStep(1)"
                                        disabled
                                        class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-black text-sm rounded-xl shadow-lg shadow-blue-600/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                        Lanjut: Pilih Kategori Usaha →
                                    </button>
                                </div>
                            </div>

                            {{-- Langkah 2 --}}
                            <div id="step-2-content" class="max-w-4xl mx-auto space-y-6 hidden">
                                <div class="text-center space-y-1">
                                    <h3 class="text-xl font-black text-slate-900">Langkah 2: Pilih Kategori Usaha</h3>
                                    <p class="text-xs text-slate-500">Kategori menentukan alur kasir dan kategori produk bawaan di toko Anda.</p>

                                </div>
                                <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 pt-2">
                                    @foreach ($categories as $c)
                                        <div class="cat-card relative p-3.5 sm:p-5 rounded-2xl border-2 shadow-sm transition-all {{ $c->is_active ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}"
                                            data-slug="{{ $c->slug }}" data-name="{{ $c->name }}"
                                            data-icon="{{ \App\Support\Icons::name($c->icon, 'storefront') }}"
                                            data-active="{{ $c->is_active ? 1 : 0 }}">
                                            <div class="flex items-center justify-between mb-3">
                                                <div
                                                    class="w-12 h-12 rounded-xl bg-{{ $c->color }}-100 text-{{ $c->color }}-600 flex items-center justify-center">
                                                    <x-ico :v="$c->icon" fallback="storefront"
                                                        style="font-size:26px" /></div>
                                                <div
                                                    class="check-icon w-6 h-6 rounded-full bg-blue-600 text-white items-center justify-center text-xs font-bold hidden">
                                                    <x-ico v="check" style="font-size:16px" /></div>
                                            </div>
                                            <h4 class="font-extrabold text-slate-900 text-base mb-1">
                                                {{ $c->name }}</h4>
                                            <p class="text-xs text-slate-500 leading-relaxed mb-3">{{ $c->tagline }}
                                            </p>
                                            <span
                                                class="inline-block px-2.5 py-1 {{ $c->is_active ? 'bg-white text-blue-700 border-blue-200' : 'bg-slate-100 text-slate-500 border-slate-200' }} text-[10px] font-bold rounded-md border">{{ $c->is_active ? $c->badge : 'Segera hadir' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div
                                    class="p-5 bg-slate-50 rounded-2xl border border-slate-200 mt-6 grid sm:grid-cols-2 gap-4">
                                    <div><label
                                            class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jumlah
                                            Outlet Saat Ini</label>
                                        <select id="reg-outlet-count" name="outlet_count"
                                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold bg-white outline-none">
                                            @foreach (\App\Http\Controllers\RegisterController::OUTLETS as $o)
                                                <option value="{{ $o }}" @selected(old('outlet_count', '1 Outlet') === $o)>
                                                    {{ $o }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div><label
                                            class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kota
                                            / Lokasi Operasional</label>
                                        <input id="reg-location" name="location" maxlength="120"
                                            value="{{ old('location') }}" placeholder="Contoh: Surakarta"
                                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold bg-white outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 transition-all">
                                        <p id="err-location" class="hidden mt-1.5 text-xs font-semibold text-rose-600" role="alert"></p>
                                    </div>
                                </div>
                                <p id="step2-error" class="hidden text-center text-xs font-bold text-rose-600"></p>
                                <div class="flex items-center justify-between pt-4">
                                    <button type="button" onclick="goToStep(1)"
                                        class="px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition-all">←
                                        Kembali</button>
                                    <button type="button" id="btn-next-step-2" onclick="nextStep(2)" disabled
                                        class="px-5 sm:px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-600/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed">Lanjut:
                                        Konfirmasi Usaha →</button>
                                </div>
                            </div>

                            {{-- Langkah 3 --}}
                            <div id="step-3-content" class="max-w-2xl mx-auto space-y-6 hidden">
                                <div class="text-center space-y-1">
                                    <div
                                        class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-2 font-bold">
                                        <x-ico v="check" style="font-size:26px" /></div>
                                    <h3 class="text-xl font-black text-slate-900">Langkah 3: Konfirmasi Data Usaha Anda
                                    </h3>
                                    <p class="text-xs text-slate-500">Periksa ringkasan sebelum akun CashierStore Anda
                                        dibuat.</p>
                                </div>
                                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200/90 space-y-5">
                                    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                                        <div>
                                            <span
                                                class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Nama
                                                Bisnis &amp; Kategori</span>
                                            <div
                                                class="text-lg font-black text-slate-900 flex items-center gap-2 mt-0.5">
                                                <span id="review-cat-icon" class="material-symbols-outlined"
                                                    aria-hidden="true"></span> <span id="review-business-name"></span>
                                            </div>
                                            <span id="review-category"
                                                class="inline-block mt-1 text-xs font-extrabold text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200"></span>
                                        </div>
                                        <button type="button" onclick="goToStep(2)"
                                            class="text-xs font-bold text-blue-600 hover:underline">Ubah
                                            Kategori</button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 text-xs">
                                        <div><span class="text-slate-400 font-semibold block">Nama Pemilik:</span><span
                                                id="review-owner" class="font-bold text-slate-800 text-sm"></span>
                                        </div>
                                        <div><span class="text-slate-400 font-semibold block">Email Login:</span><span
                                                id="review-email"
                                                class="font-bold text-slate-800 text-sm break-all"></span></div>
                                        <div><span class="text-slate-400 font-semibold block">No. WhatsApp:</span><span
                                                id="review-phone" class="font-bold text-slate-800 text-sm"></span>
                                        </div>
                                        <div><span class="text-slate-400 font-semibold block">Skala &amp;
                                                Lokasi:</span><span id="review-scale"
                                                class="font-bold text-slate-800 text-sm"></span></div>
                                    </div>
                                    <div class="pt-4 border-t border-slate-200">
                                        <span
                                            class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-2">Modul
                                            yang langsung aktif:</span>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                            @foreach (['Kasir / POS', 'Manajemen Stok', 'CRM & Member Tier', 'Dashboard Penjualan', 'Laporan PDF & Excel', 'Login PIN Kasir'] as $m)
                                                <div
                                                    class="p-2 bg-white rounded-lg border border-slate-200 text-[11px] font-semibold text-slate-700 flex items-center gap-1.5">
                                                    <span class="text-emerald-500"><x-ico v="check"
                                                            style="font-size:14px" /></span> {{ $m }}</div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="pt-3">
                                        <label class="flex items-start gap-2.5 text-xs text-slate-600 cursor-pointer">
                                            <input id="agree-terms" type="checkbox" name="agree" value="1"
                                                @checked(old('agree'))
                                                class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                            <span>Saya menyatakan data usaha di atas valid dan menyetujui Ketentuan
                                                Layanan &amp; Kebijakan Privasi CashierStore.</span>
                                        </label>
                                        <p id="step3-error" class="hidden mt-2 text-xs font-bold text-rose-600"></p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-2">
                                    <button type="button" onclick="goToStep(2)"
                                        class="px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition-all">←
                                        Ubah Data</button>
                                    <button type="submit" id="btn-submit" disabled
                                        class="px-5 sm:px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm rounded-xl shadow-lg shadow-emerald-600/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed">Konfirmasi
                                        &amp; Buat Toko Sekarang</button>
                                </div>
                            </div>
                        </form>

                        {{-- Sukses --}}
                        <div id="step-success" class="max-w-lg mx-auto text-center space-y-6 py-8 hidden">
                            <div
                                class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-3xl flex items-center justify-center mx-auto shadow-lg shadow-emerald-500/20">
                                <x-ico v="celebration" style="font-size:44px" /></div>
                            <div class="space-y-2">
                                <h3 class="text-2xl font-black text-slate-900">Selamat! Usaha Anda Berhasil Didaftarkan
                                </h3>
                                <p class="text-sm text-slate-600">Akun untuk <strong id="success-business-name"
                                        class="text-blue-600">{{ $reg['name'] ?? '' }}</strong> sudah aktif dengan
                                    masa uji coba 14 hari.</p>
                            </div>
                            <div
                                class="p-4 bg-blue-50 rounded-2xl border border-blue-200 text-xs text-blue-800 space-y-1.5">
                                <div class="flex items-center gap-1.5">Kategori Usaha: <strong
                                        class="inline-flex items-center gap-1"><x-ico :v="$reg['icon'] ?? null"
                                            fallback="storefront" style="font-size:18px" /> <span
                                            id="success-category">{{ $reg['category'] ?? '' }}</span></strong></div>
                                <div>Kode Toko: <code id="success-code"
                                        class="font-mono bg-white px-2 py-0.5 rounded border border-blue-200">{{ $reg['code'] ?? '' }}</code>
                                </div>
                                <div class="text-[11px] text-blue-600">Simpan kode toko ini. Kasir memakainya bersama
                                    PIN untuk login cepat.</div>
                            </div>
                            <a id="btn-open-dashboard" href="{{ route('dashboard') }}"
                                class="inline-block px-5 sm:px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-600/30">
                                Buka Dashboard Kasir →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif



    <!-- FAQ -->
    <section id="faq" class="max-w-3xl mx-auto px-5 py-14 sm:py-20 lg:py-28">
        <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-10 rv">Pertanyaan yang sering muncul</h2>
        <div class="space-y-3">
            @foreach ([
                ['Apakah CashierStore gratis?', 'Semua fitur bisa dipakai gratis selama 14 hari pertama, tanpa kartu kredit.'],
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

    <!-- CTA AKHIR -->
    <section class="px-5 pb-14 sm:pb-20 lg:pb-28">
        <div class="relative max-w-5xl mx-auto overflow-hidden rounded-[2rem] bg-neon text-[#181829] px-6 py-14 sm:px-12 text-center">
            <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-white/40 blur-3xl" aria-hidden="true"></div>
            <h2 class="relative text-3xl sm:text-4xl font-extrabold tracking-tight">Siap bikin kasir tokomu lebih rapi?</h2>
            <p class="relative mt-4 text-[#2C3600] font-medium max-w-xl mx-auto">Daftar sekarang, pakai semua fitur gratis 14 hari. Tidak perlu kartu kredit.</p>
            <a href="#auth-section" onclick="switchAuthTab('register')" class="relative inline-flex items-center justify-center gap-2 mt-8 h-12 px-7 rounded-full bg-[#181829] text-neon font-extrabold hover:scale-105 transition-transform">Coba gratis 14 hari <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span></a>
        </div>
    </section>
    </main>

    <footer class="border-t border-edge">
        <div class="max-w-6xl mx-auto px-5 py-10 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-slate-400">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-neon text-[#181829] flex items-center justify-center"><svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm0 6h16v8a2 2 0 01-2 2H6a2 2 0 01-2-2v-8zm5 3a1 1 0 000 2h6a1 1 0 100-2H9z" /></svg></span>
                <span class="font-extrabold text-white">Cashier<span class="text-neon">Store</span></span>
            </div>
            <nav class="flex gap-6 font-medium" aria-label="Footer">
                <a href="#fitur" class="hover:text-neon">Fitur</a>
                <a href="#cara-kerja" class="hover:text-neon">Cara mulai</a>
                <a href="#faq" class="hover:text-neon">Tanya jawab</a>
            </nav>
            <p>&copy; {{ date('Y') }} CashierStore</p>
        </div>
    </footer>

    @verbatim
    <script>
        (() => {
            const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
            /* muncul saat scroll */
            const rv = document.querySelectorAll('.rv');
            if ('IntersectionObserver' in window && !reduce) {
                const io = new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }), { threshold: .15 });
                rv.forEach((el, i) => { el.style.transitionDelay = (i % 3) * 80 + 'ms'; io.observe(el); });
            } else rv.forEach(el => el.classList.add('in'));

            /* tab preview */
            const tabs = [...document.querySelectorAll('[data-tab]')], panes = [...document.querySelectorAll('[data-pane]')];
            if (tabs.length) {
                let cur = 0, timer;
                const show = i => {
                    cur = i;
                    tabs.forEach((t, k) => { t.classList.toggle('on', k === i); t.setAttribute('aria-selected', k === i); });
                    panes.forEach((p, k) => { p.classList.remove('on'); p.hidden = k !== i; });
                    setTimeout(() => panes[i].classList.add('on'), 40);
                };
                tabs.forEach((t, k) => t.addEventListener('click', () => { clearInterval(timer); show(k); }));
                show(0);
                if (!reduce) timer = setInterval(() => show((cur + 1) % tabs.length), 6500);
            }

            /* demo kasir di hero */
            const rows = [...document.querySelectorAll('#pos-rows .pos-row')];
            const total = document.getElementById('pos-total');
            const done = document.getElementById('pos-done');
            const fmt = n => 'Rp ' + n.toLocaleString('id-ID');
            const sum = n => rows.slice(0, n).reduce((a, r) => a + +r.dataset.p, 0);
            if (!rows.length) return;
            if (reduce) { rows.forEach(r => r.classList.add('on')); total.textContent = fmt(sum(rows.length)); done.classList.add('on'); return; }
            const count = (from, to) => {
                const t0 = performance.now();
                (function step(t) {
                    const k = Math.min((t - t0) / 600, 1);
                    total.textContent = fmt(Math.round(from + (to - from) * (1 - Math.pow(1 - k, 3))));
                    if (k < 1) requestAnimationFrame(step);
                })(t0);
            };
            const wait = ms => new Promise(r => setTimeout(r, ms));
            (async function loop() {
                for (;;) {
                    rows.forEach(r => r.classList.remove('on')); done.classList.remove('on'); total.textContent = fmt(0);
                    await wait(700);
                    for (let i = 0; i < rows.length; i++) { rows[i].classList.add('on'); count(sum(i), sum(i + 1)); await wait(1100); }
                    await wait(500); done.classList.add('on');
                    await wait(3200);
                }
            })();
        })();
    </script>
    @endverbatim

    @verbatim
        <script>
            (() => {
                const $ = id => document.getElementById(id);
                let passwordIsValid = false;
                let currentStep = 1;
                let isSubmitting = false;
                let lockedUntil = 0;

                /* ---------- Helper error per field ---------- */
                const touched = new Set();

                const setFieldError = (id, msg) => {
                    const el = $(id);
                    const err = $('err-' + (el?.name || id));
                    if (el) {
                        el.classList.toggle('border-rose-500', !!msg);
                        el.classList.toggle('border-slate-200', !msg);
                        el.setAttribute('aria-invalid', msg ? 'true' : 'false');
                    }
                    if (err) {
                        err.textContent = msg || '';
                        err.classList.toggle('hidden', !msg);
                    }
                };

                /* ---------- Aturan validasi (samakan dengan RegisterController) ---------- */
                const rules = {
                    'reg-owner-name': v => {
                        v = v.trim();
                        if (!v) return 'Nama pemilik wajib diisi.';
                        if (v.length < 3) return 'Nama pemilik minimal 3 karakter.';
                        if (v.length > 120) return 'Nama pemilik maksimal 120 karakter.';
                        return '';
                    },
                    'reg-business-name': v => {
                        v = v.trim();
                        if (!v) return 'Nama usaha wajib diisi.';
                        if (v.length < 2) return 'Nama usaha minimal 2 karakter.';
                        if (v.length > 120) return 'Nama usaha maksimal 120 karakter.';
                        return '';
                    },
                    'reg-email': v => {
                        v = v.trim();
                        if (!v) return 'Email wajib diisi.';
                        if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return 'Format email tidak valid.';
                        return '';
                    },
                    'reg-phone': v => {
                        v = v.trim();
                        if (!v) return 'Nomor WhatsApp wajib diisi.';
                        if (!/^[0-9+\-\s()]+$/.test(v)) return 'Nomor hanya boleh berisi angka, spasi, +, - atau ().';
                        const digits = v.replace(/\D/g, '');
                        if (digits.length < 8) return 'Nomor WhatsApp minimal 8 digit.';
                        if (digits.length > 15) return 'Nomor WhatsApp maksimal 15 digit.';
                        return '';
                    },
                    'reg-location': v => {
                        v = v.trim();
                        if (!v) return 'Kota / lokasi operasional wajib diisi.';
                        if (v.length < 3) return 'Lokasi minimal 3 karakter.';
                        if (v.length > 120) return 'Lokasi maksimal 120 karakter.';
                        return '';
                    },
                };

                const step1Fields = ['reg-owner-name', 'reg-business-name', 'reg-email', 'reg-phone'];

                const fieldMsg = id => rules[id]($(id)?.value ?? '');

                // Hitung validitas langkah. show=true -> tampilkan pesan error di field.
                function checkStep(step, show = false) {
                    let ok = true;

                    if (step === 1) {
                        step1Fields.forEach(id => {
                            const msg = fieldMsg(id);
                            if (msg) ok = false;
                            if (show || touched.has(id)) setFieldError(id, msg);
                        });
                        const pass = $('reg-pass');
                        if (!pass || !pass.value || !passwordIsValid) ok = false;
                    }

                    if (step === 2) {
                        const hasCat = !!$('reg-category').value;
                        const catErr = $('step2-error');
                        if (!hasCat) ok = false;
                        if (show || touched.has('category')) {
                            catErr.textContent = hasCat ? '' : 'Pilih salah satu kategori usaha.';
                            catErr.classList.toggle('hidden', hasCat);
                        }
                        const msg = fieldMsg('reg-location');
                        if (msg) ok = false;
                        if (show || touched.has('reg-location')) setFieldError('reg-location', msg);
                    }

                    if (step === 3) {
                        const agreed = $('agree-terms').checked;
                        if (!agreed) ok = false;
                        if (show || touched.has('agree-terms')) {
                            const e = $('step3-error');
                            e.textContent = agreed ? '' : 'Centang persetujuan ketentuan layanan terlebih dahulu.';
                            e.classList.toggle('hidden', agreed);
                        }
                    }
                    return ok;
                }

                // Perbarui status tombol "lanjut" / "submit" sesuai langkah aktif.
                function refreshButtons() {
                    const locked = Date.now() < lockedUntil;
                    const b1 = $('btn-next-step-1'), b2 = $('btn-next-step-2'), b3 = $('btn-submit');
                    if (b1) b1.disabled = !checkStep(1);
                    if (b2) b2.disabled = !checkStep(2);
                    if (b3) b3.disabled = isSubmitting || locked || !(checkStep(1) && checkStep(2) && checkStep(3));
                }

                function onFieldInput(e) {
                    touched.add(e.target.id);
                    // tampilkan error langkah yang sedang aktif
                    checkStep(currentStep);
                    refreshButtons();
                }

                document.addEventListener('password-validation-updated', event => {
                    passwordIsValid = event.detail.valid === true;
                    refreshButtons();
                });

                [...step1Fields, 'reg-location', 'agree-terms'].forEach(id => {
                    const f = $(id);
                    if (!f) return;
                    f.addEventListener('input', onFieldInput);
                    f.addEventListener('change', onFieldInput);
                    f.addEventListener('blur', onFieldInput);
                });
                // Password (Livewire): ikut hidupkan/matikan tombol saat diketik
                $('reg-pass')?.addEventListener('input', refreshButtons);

                refreshButtons();

                const auth = $('auth-section');
                const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                

                // user sudah login: form registrasi tidak dirender (hanya kartu "Kamu sedang masuk")
                if (!auth || !auth.dataset.initialTab) {
                    window.switchAuthTab = () => {};
                    return;
                }

                /* ---------- Tab Masuk / Registrasi ---------- */
                const ON = 'py-3 px-3 sm:py-3.5 sm:px-6 rounded-2xl font-bold text-sm bg-white text-blue-600 shadow-sm transition-all';
                const OFF = 'py-3 px-3 sm:py-3.5 sm:px-6 rounded-2xl font-bold text-sm text-slate-600 hover:text-slate-900 transition-all';
                window.switchAuthTab = tab => {
                    const login = tab === 'login';
                    $('view-login').classList.toggle('hidden', !login);
                    $('view-register').classList.toggle('hidden', login);
                    $('tab-login-btn').className = login ? ON : OFF;
                    $('tab-register-btn').className = login ? OFF : ON;
                };
                const MON = 'py-2.5 rounded-lg bg-white text-blue-600 shadow-sm',
                    MOFF = 'py-2.5 rounded-lg text-slate-500';
                window.switchLoginMode = mode => {
                    const pin = mode === 'pin';
                    $('form-email').classList.toggle('hidden', pin);
                    $('form-pin').classList.toggle('hidden', !pin);
                    $('mode-email').className = pin ? MOFF : MON;
                    $('mode-pin').className = pin ? MON : MOFF;
                };

                /* ---------- Wizard registrasi ---------- */
                const badge = (i, state) => {
                    const b = $('step-badge-' + i),
                        l = $('step-label-' + i);
                    const base =
                        'w-10 h-10 rounded-full font-black text-sm flex items-center justify-center transition-all ';
                    if (state === 'done') {
                        b.className = base + 'bg-emerald-600 text-white shadow-md';
                        b.innerHTML = '<span class="material-symbols-outlined" style="font-size:20px">check</span>';
                        l.className = 'text-[11px] font-bold text-emerald-600 mt-2';
                    } else if (state === 'active') {
                        b.className = base + 'bg-blue-600 text-white shadow-md';
                        b.textContent = i;
                        l.className = 'text-[11px] font-bold text-blue-600 mt-2';
                    } else {
                        b.className = base + 'bg-slate-100 text-slate-400';
                        b.textContent = i;
                        l.className = 'text-[11px] font-bold text-slate-400 mt-2';
                    }
                };
                window.goToStep = step => {
                    ['1', '2', '3'].forEach(n => $('step-' + n + '-content').classList.add('hidden'));
                    $('register-form').classList.remove('hidden');
                    $('step-success').classList.add('hidden');
                    if (step === 'success') {
                        $('register-form').classList.add('hidden');
                        $('step-success').classList.remove('hidden');
                        [1, 2, 3].forEach(i => badge(i, 'done'));
                        $('stepper-line').style.width = '100%';
                        return;
                    }
                    if (step === 3) fillReview();
                    currentStep = step;
                    $('step-' + step + '-content').classList.remove('hidden');
                    $('stepper-line').style.width = ['0%', '50%', '100%'][step - 1];
                    [1, 2, 3].forEach(i => badge(i, i < step ? 'done' : i === step ? 'active' : 'idle'));
                    if (step === 2) touched.add('category');
                    checkStep(step);
                    refreshButtons();
                };
                const fail = (id, msg) => {
                    const e = $(id);
                    e.textContent = msg;
                    e.classList.remove('hidden');
                };

                window.nextStep = from => {
                    if (from === 1) {
                        if (!checkStep(1, true)) {
                            const bad = step1Fields.find(id => fieldMsg(id));
                            $(bad || 'reg-pass')?.focus();
                            return;
                        }
                        goToStep(2);
                    } else if (from === 2) {
                        if (!checkStep(2, true)) {
                            if (fieldMsg('reg-location')) $('reg-location').focus();
                            return;
                        }
                        goToStep(3);
                    }
                };

                const ownerName = document.getElementById('reg-owner-name');
                const email = document.getElementById('reg-email');

                // Kirim konteks (nama & email) ke Livewire: debounce + hanya jika berubah.
                let ctxTimer = null, lastCtx = null;
                function sendPasswordContext() {
                    clearTimeout(ctxTimer);
                    const name = ownerName?.value ?? '';
                    const mail = email?.value ?? '';
                    const key = name + '|' + mail;
                    if (key === lastCtx) return;                 // tidak berubah
                    if (lastCtx === null && !name && !mail) {    // masih kosong semua, tidak perlu kirim
                        lastCtx = key;
                        return;
                    }
                    lastCtx = key;
                    Livewire.dispatch('registration-context-updated', { name, email: mail });
                }
                function updatePasswordContext() {
                    clearTimeout(ctxTimer);
                    ctxTimer = setTimeout(sendPasswordContext, 600);
                }

                ownerName?.addEventListener('input', updatePasswordContext);
                email?.addEventListener('input', updatePasswordContext);
                // Pastikan konteks terbaru sudah terkirim sebelum password divalidasi
                $('reg-pass')?.addEventListener('focus', sendPasswordContext);
                $('reg-pass')?.addEventListener('keydown', sendPasswordContext, { once: false });

                /* ---------- Kategori usaha ---------- */
                const cards = [...document.querySelectorAll('.cat-card')];
                const paintCards = () => {
                    const cur = $('reg-category').value;
                    cards.forEach(c => {
                        const sel = c.dataset.slug === cur && c.dataset.active === '1';
                        c.classList.toggle('border-blue-600', sel);
                        c.classList.toggle('bg-blue-50/50', sel);
                        c.classList.toggle('border-slate-200', !sel);
                        c.classList.toggle('bg-white', !sel);
                        const ck = c.querySelector('.check-icon');
                        ck.classList.toggle('hidden', !sel);
                        ck.classList.toggle('flex', sel);
                    });
                };
                cards.forEach(c => c.addEventListener('click', () => {
                    if (c.dataset.active !== '1') return;
                    $('reg-category').value = c.dataset.slug;
                    touched.add('category');
                    paintCards();
                    checkStep(2);
                    refreshButtons();
                }));

                function fillReview() {
                    const cur = cards.find(c => c.dataset.slug === $('reg-category').value);
                    $('review-business-name').textContent = $('reg-business-name').value;
                    $('review-category').textContent = cur ? cur.dataset.name : '-';
                    $('review-cat-icon').textContent = cur ? cur.dataset.icon : '';
                    $('review-owner').textContent = $('reg-owner-name').value;
                    $('review-email').textContent = $('reg-email').value;
                    $('review-phone').textContent = $('reg-phone').value;
                    $('review-scale').textContent = $('reg-outlet-count').value + ' • ' + $('reg-location').value;
                }

                $('register-form').addEventListener('submit', async e => {
                    e.preventDefault();

                    // Cegah submit dari Enter di langkah 1/2 & klik ganda
                    if (currentStep !== 3 || isSubmitting || Date.now() < lockedUntil) {
                        if (currentStep === 1 || currentStep === 2) nextStep(currentStep);
                        return;
                    }
                    if (!(checkStep(1, true) && checkStep(2, true) && checkStep(3, true))) {
                        if (!checkStep(1)) goToStep(1);
                        else if (!checkStep(2)) goToStep(2);
                        return;
                    }

                    const form = $('register-form');
                    const button = $('btn-submit');
                    const errorBox = $('register-server-errors');
                    const errorList = $('register-server-errors-list');
                    const step3Error = $('step3-error');

                    errorBox.classList.add('hidden');
                    errorList.replaceChildren();
                    step3Error.classList.add('hidden');

                    const originalText = button.textContent;
                    isSubmitting = true;
                    button.disabled = true;
                    button.textContent = 'Membuat toko…';

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: new FormData(form),
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });

                        const result = await response.json();

                        if (!response.ok) {
                            if (response.status === 422 && result.errors) {
                                const errors = result.errors;

                                const map = {
                                    owner_name: 'reg-owner-name', business_name: 'reg-business-name',
                                    email: 'reg-email', phone: 'reg-phone', location: 'reg-location'
                                };
                                for (const [field, messages] of Object.entries(errors)) {
                                    if (map[field]) { touched.add(map[field]); setFieldError(map[field], messages[0]); }
                                    for (const message of messages) {
                                        const li = document.createElement('li');
                                        li.textContent = message;
                                        errorList.appendChild(li);
                                    }
                                }

                                errorBox.classList.remove('hidden');

                                const fieldsStep1 = [
                                    'owner_name',
                                    'business_name',
                                    'email',
                                    'phone',
                                    'password'
                                ];

                                const fieldsStep2 = [
                                    'category',
                                    'outlet_count',
                                    'location'
                                ];

                                if (Object.keys(errors).some(key => fieldsStep1.includes(key))) {
                                    goToStep(1);
                                } else if (Object.keys(errors).some(key => fieldsStep2.includes(key))) {
                                    goToStep(2);
                                } else {
                                    goToStep(3);
                                }

                                errorBox.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });

                                return;
                            }

                            if (response.status === 429) {
                                const wait = parseInt(response.headers.get('Retry-After'), 10) || 60;
                                lockedUntil = Date.now() + wait * 1000;
                                setTimeout(refreshButtons, wait * 1000 + 50);
                                throw new Error(`Terlalu banyak percobaan. Coba lagi dalam ${wait} detik.`);
                            }
                            throw new Error(
                                result.message || 'Registrasi gagal. Silakan coba lagi.'
                            );
                        }

                        if (!result.success || !result.registered) {
                            throw new Error('Respons registrasi dari server tidak sesuai.');
                        }

                        $('success-business-name').textContent = result.registered.name;
                        $('success-category').textContent = result.registered.category;
                        $('success-code').textContent = result.registered.code;

                        goToStep('success');

                    } catch (error) {
                        console.error('Registrasi gagal:', error);

                        fail(
                            'step3-error',
                            error.message || 'Terjadi kesalahan koneksi. Silakan coba lagi.'
                        );
                    } finally {
                        isSubmitting = false;
                        button.textContent = originalText;
                        refreshButtons();
                    }
                });

                /* ---------- Inisialisasi ---------- */
                paintCards();
                let step = auth.dataset.initialStep;
                step = step === 'success' ? step : parseInt(step, 10);
                goToStep(step);
                refreshButtons();
                switchLoginMode(auth.dataset.loginMode);
                switchAuthTab(location.hash === '#masuk' ? 'login' : auth.dataset.initialTab);
            })
            ();
        </script>
    @endverbatim


    @if (config('chat.enabled'))
        @include('landing.chat-widget')
    @endif
</body>

</html>