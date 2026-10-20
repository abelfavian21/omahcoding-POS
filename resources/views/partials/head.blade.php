<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#151624">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL@20..48,400,0..1&display=swap" rel="stylesheet">
<script>
  (function () {
    try {
      var t = localStorage.getItem('theme');
      if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark');
    } catch (e) {}
    window.setTheme = function (mode) {
      document.documentElement.classList.toggle('dark', mode === 'dark');
      try { localStorage.setItem('theme', mode); } catch (e) {}
    };
    window.toggleTheme = function () {
      window.setTheme(document.documentElement.classList.contains('dark') ? 'light' : 'dark');
    };
  })();
</script>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<script>
  /* Skala "brand" = lime/olive. blue/indigo/cyan/violet dialihkan ke skala yang sama
     supaya halaman yang belum dirombak ikut berganti warna tanpa edit satu-satu. */
  const limeScale = { 50:'#F6FDE3', 100:'#EBFCC2', 200:'#E1F98F', 300:'#D2F839', 400:'#C2EA1C', 500:'#9DBA00', 600:'#536500', 700:'#445400', 800:'#3E4C00', 900:'#2C3600', 950:'#171E00' };
  tailwind.config = {
    darkMode: 'class',
    theme: { extend: {
      colors: {
        brand: limeScale, blue: limeScale, indigo: limeScale, cyan: limeScale, violet: limeScale,
        'sidebar-bg': '#151624', 'sidebar-darker': '#0F101B',
        'lime-accent': '#D2F839', 'lime-light': '#E9FD87', 'lime-subtle': '#F3FDE0',
        'dark-card': '#181928', 'card-border': '#E8ECF2',
        canvas: '#F1F3F6', 'canvas-bg': '#F1F3F6',
      },
      fontFamily: { sans: ['"Plus Jakarta Sans"','system-ui','sans-serif'] },
      boxShadow: { card: '0 4px 20px -2px rgba(24,24,41,.04), 0 2px 6px -1px rgba(24,24,41,.02)', lime: '0 0 20px rgba(210,248,57,.35)' },
      screens: { xs: '420px' },
    } }
  }
</script>
<style type="text/tailwindcss">
  :root {
    --canvas:#F1F3F6; --surface:#FFFFFF; --line:#E8ECF2; --field:#F4F6F9; --ink:#11121C; --muted:#7A7F9A;
    --chip-on-bg:#181829; --chip-on-fg:#D2F839;
  }
  .dark {
    --canvas:#0E0F1A; --surface:#181928; --line:#272942; --field:#111222; --ink:#F1F2F8; --muted:#A5A9B8;
    --chip-on-bg:#D2F839; --chip-on-fg:#181829;
  }
  @layer base {
    html { -webkit-tap-highlight-color: transparent; }
    body { @apply font-sans antialiased; background: var(--canvas); color: var(--ink); font-variant-numeric: tabular-nums; }
    ::selection { background:#D2F839; color:#181829; }
  }
  @layer components {
    .card { background: var(--surface); border: 1px solid var(--line); border-radius: 1.5rem; box-shadow: 0 4px 20px -2px rgba(24,24,41,.04), 0 2px 6px -1px rgba(24,24,41,.02); }
    .card-dark { @apply text-white relative overflow-hidden; background:#181829; border-radius:1.5rem; border:1px solid #26283D; }
    .dark .card-dark { background:#1D1E35; border-color:rgba(210,248,57,.25); }

    .btn { @apply inline-flex items-center justify-center gap-2 h-11 px-5 rounded-full text-sm font-bold transition active:scale-[.98] whitespace-nowrap; }
    .btn-primary { @apply btn; background:#D2F839; color:#181829; }
    .btn-primary:hover { background:#DCFA5C; }
    .btn-dark { @apply btn; background:#181829; color:#fff; }
    .btn-dark:hover { background:#25263d; }
    .dark .btn-dark { background:#272942; }
    .btn-secondary { @apply btn border; background: var(--field); border-color: var(--line); color: var(--ink); }
    .btn-secondary:hover { background: var(--line); }
    .btn-ghost { @apply btn; color: var(--muted); }
    .btn-ghost:hover { background: var(--field); color: var(--ink); }
    .btn-danger { @apply btn bg-red-50 text-red-700 border border-red-100 hover:bg-red-100; }

    .input { @apply w-full h-11 rounded-2xl text-sm border px-4 focus:ring-4 focus:ring-[#D2F839]/40; background: var(--field); color: var(--ink); border-color: var(--line); }
    .input::placeholder { color:#9DA2B4; }
    .input:focus { border-color:#B1D501; background: var(--surface); }
    textarea.input { @apply h-auto py-2.5; }
    .input-pill { @apply rounded-full pl-11; }
    .label { @apply block text-xs font-semibold mb-1.5; color: var(--muted); }

    .chip { @apply inline-flex items-center h-10 px-4 rounded-full text-sm font-semibold whitespace-nowrap transition border; background: var(--field); border-color: var(--line); color: var(--ink); }
    .chip:hover { background: var(--line); }
    .chip-on, .chip-on:hover { background: var(--chip-on-bg); color: var(--chip-on-fg); border-color: transparent; }

    .pill { @apply inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide border; }
    .pill-green { @apply bg-emerald-50 text-emerald-600 border-emerald-200; }
    .pill-amber { @apply bg-amber-50 text-amber-700 border-amber-200; }
    .pill-blue  { background:#F3FDE0; color:#3E4C00; border-color:rgba(177,213,1,.5); }
    .pill-red   { @apply bg-red-50 text-red-600 border-red-200; }
    .pill-slate { @apply bg-slate-100 text-slate-600 border-slate-200; }

    .th { @apply px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider; color:#9DA2B4; }
    .td { @apply px-4 py-3.5 text-sm; }
    .no-scrollbar { scrollbar-width: none; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
  }
  .material-symbols-outlined { font-size: 20px; vertical-align: middle; font-variation-settings: 'FILL' 0; }
  .fill-1 { font-variation-settings: 'FILL' 1 !important; }
  /* bar bergaris (Stitch) */
  .hatch { background: repeating-linear-gradient(45deg,#C5EE2C 0 3px,#EBFD92 3px 8px); }
  .dark .hatch { background: repeating-linear-gradient(45deg,rgba(210,248,57,.55) 0 3px,rgba(210,248,57,.12) 3px 8px); }
  /* area aman iPhone untuk bottom nav */
  .safe-b { padding-bottom: env(safe-area-inset-bottom, 0px); }
</style>

<style>
  /* ===== DARK MODE (utility lama dipetakan ke palet navy) ===== */
  html.dark { color-scheme: dark; }

  .dark .bg-white { background-color:#181928; }
  .dark .bg-white\/90 { background-color:rgba(24,25,40,.9); }
  .dark .bg-slate-50, .dark .hover\:bg-slate-50:hover, .dark .hover\:bg-slate-50\/60:hover, .dark .hover\:bg-slate-50\/70:hover { background-color:#1F2035; }
  .dark .bg-slate-100, .dark .hover\:bg-slate-100:hover { background-color:#272942; }
  .dark .bg-slate-200, .dark .hover\:bg-slate-200:hover { background-color:#34364F; }
  .dark .bg-slate-800 { background-color:#020617; box-shadow:0 0 0 1px #34364F; }

  .dark .border-slate-50, .dark .border-slate-100, .dark .border-card-border { border-color:#272942; }
  .dark .border-slate-200, .dark .border-slate-200\/80 { border-color:#34364F; }
  .dark .border-slate-300 { border-color:#4A4D6A; }
  .dark .border-white { border-color:#181928; }
  .dark .divide-slate-50 > :not([hidden]) ~ :not([hidden]),
  .dark .divide-slate-100 > :not([hidden]) ~ :not([hidden]) { border-color:#272942; }

  .dark .text-slate-900 { color:#F8F9FC; }
  .dark .text-slate-800 { color:#EEF0F7; }
  .dark .text-slate-700 { color:#E2E4EE; }
  .dark .text-slate-600 { color:#C5C8D8; }
  .dark .text-slate-500 { color:#A5A9B8; }
  .dark .text-slate-400 { color:#8F94AB; }

  /* brand */
  .dark .text-brand-600, .dark .text-brand-700, .dark .text-blue-600, .dark .text-blue-700 { color:#D2F839; }
  .dark .bg-brand-50, .dark .hover\:bg-brand-50:hover { background-color:rgba(210,248,57,.10); }
  .dark .bg-brand-50\/50, .dark .bg-brand-50\/60 { background-color:rgba(210,248,57,.06); }
  .dark .bg-brand-100, .dark .hover\:bg-brand-100:hover { background-color:rgba(210,248,57,.18); }
  .dark .bg-brand-600, .dark .hover\:bg-brand-700:hover { background-color:#D2F839; color:#181829; }

  /* status */
  .dark .bg-amber-50, .dark .bg-amber-50\/50, .dark .bg-amber-50\/60 { background-color:rgba(245,158,11,.13); }
  .dark .bg-amber-100 { background-color:rgba(245,158,11,.22); }
  .dark .text-amber-800, .dark .text-amber-700, .dark .text-amber-600, .dark .text-amber-500 { color:#FBBF24; }
  .dark .border-amber-200 { border-color:rgba(245,158,11,.35); }
  .dark .bg-emerald-50 { background-color:rgba(16,185,129,.13); }
  .dark .text-emerald-700, .dark .text-emerald-600 { color:#34D399; }
  .dark .border-emerald-200 { border-color:rgba(16,185,129,.35); }
  .dark .bg-red-50 { background-color:rgba(239,68,68,.13); }
  .dark .text-red-700, .dark .text-red-600 { color:#F87171; }
  .dark .border-red-100, .dark .border-red-200 { border-color:rgba(239,68,68,.35); }

  .dark .from-canvas { background-image:linear-gradient(to bottom right,#0E0F1A,#181928); }
  .dark .from-brand-50 { background-image:linear-gradient(to bottom right,#181928,#1F2035); }

  .dark .btn-danger { background-color:rgba(239,68,68,.13); border-color:rgba(239,68,68,.35); color:#F87171; }
  .dark .btn-danger:hover { background-color:rgba(239,68,68,.22); }
  .dark .pill-green { background-color:rgba(16,185,129,.13); color:#34D399; border-color:rgba(16,185,129,.35); }
  .dark .pill-amber { background-color:rgba(245,158,11,.13); color:#FBBF24; border-color:rgba(245,158,11,.35); }
  .dark .pill-blue  { background-color:rgba(210,248,57,.12); color:#D2F839; border-color:rgba(210,248,57,.3); }
  .dark .pill-red   { background-color:rgba(239,68,68,.13);  color:#F87171; border-color:rgba(239,68,68,.35); }
  .dark .pill-slate { background-color:#272942; color:#C5C8D8; border-color:#34364F; }
  .dark .th { color:#8F94AB; }
  .dark ::-webkit-scrollbar-thumb { background:#34364F; }
</style>
