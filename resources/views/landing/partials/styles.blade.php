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
[data-pane][hidden] { display:none; } /* utilitas .grid milik pane Kasir tidak boleh mengalahkan atribut hidden */
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
