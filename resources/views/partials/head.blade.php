<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL@20..48,400,0..1&display=swap" rel="stylesheet">
<script>
  (function () {
    try {
      var t = localStorage.getItem('theme');
      if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark');
    } catch (e) {}
    window.toggleTheme = function () {
      var d = document.documentElement.classList.toggle('dark');
      try { localStorage.setItem('theme', d ? 'dark' : 'light'); } catch (e) {}
    };
  })();
</script>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<script>
  tailwind.config = {
    darkMode: 'class',
    theme: { extend: {
      colors: { brand: { 50:'#eff6ff', 100:'#dbeafe', 500:'#3b82f6', 600:'#2563eb', 700:'#1d4ed8', 800:'#1e40af' },
                canvas:'#F8FAFC' },
      fontFamily: { sans: ['"Plus Jakarta Sans"','system-ui','sans-serif'] },
    } }
  }
</script>
<style type="text/tailwindcss">
  @layer base { body { @apply font-sans text-slate-900 bg-canvas antialiased; font-variant-numeric: tabular-nums; } }
  @layer components {
    .card { @apply bg-white border border-slate-100 rounded-2xl shadow-[0_1px_3px_rgba(15,23,42,.04)]; }
    .btn { @apply inline-flex items-center justify-center gap-2 h-11 px-4 rounded-xl text-sm font-semibold transition; }
    .btn-primary { @apply btn bg-brand-600 text-white hover:bg-brand-700 shadow-sm; }
    .btn-secondary { @apply btn bg-brand-50 text-brand-700 border border-brand-100 hover:bg-brand-100; }
    .btn-ghost { @apply btn text-slate-600 hover:bg-slate-100; }
    .btn-danger { @apply btn bg-red-50 text-red-700 border border-red-100 hover:bg-red-100; }
    .input { @apply w-full h-11 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-600 focus:ring-4 focus:ring-brand-600/15; }
    textarea.input { @apply h-auto py-2.5; }
    .label { @apply block text-xs font-semibold text-slate-600 mb-1.5; }
    .pill { @apply inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide border; }
    .pill-green { @apply bg-emerald-50 text-emerald-600 border-emerald-200; }
    .pill-amber { @apply bg-amber-50 text-amber-600 border-amber-200; }
    .pill-blue { @apply bg-brand-50 text-brand-600 border-blue-200; }
    .pill-red { @apply bg-red-50 text-red-600 border-red-200; }
    .pill-slate { @apply bg-slate-100 text-slate-700 border-slate-200; }
    .th { @apply px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500; }
    .td { @apply px-4 py-3 text-sm; }
  }
  .material-symbols-outlined { font-size: 20px; vertical-align: middle; }
</style>

<style>
  /* ===== DARK MODE ===== */
  html.dark { color-scheme: dark; }
  .dark body { background:#0f172a; color:#e2e8f0; }

  /* surface */
  .dark .bg-white { background-color:#1e293b; }
  .dark .bg-slate-50, .dark .hover\:bg-slate-50:hover, .dark .hover\:bg-slate-50\/60:hover { background-color:#243247; }
  .dark .bg-slate-100, .dark .hover\:bg-slate-100:hover { background-color:#2b3a52; }
  .dark .bg-slate-200 { background-color:#3b4a63; }
  .dark .bg-slate-800 { background-color:#020617; box-shadow:0 0 0 1px #334155; }

  /* border */
  .dark .border-slate-50, .dark .border-slate-100 { border-color:#2b3a52; }
  .dark .border-slate-200 { border-color:#3b4a63; }
  .dark .border-slate-300 { border-color:#51617a; }
  .dark .border-white { border-color:#1e293b; }
  .dark .divide-slate-50 > :not([hidden]) ~ :not([hidden]) { border-color:#2b3a52; }

  /* text */
  .dark .text-slate-900 { color:#f8fafc; }
  .dark .text-slate-700 { color:#e2e8f0; }
  .dark .text-slate-600 { color:#cbd5e1; }
  .dark .text-slate-500 { color:#94a3b8; }
  .dark .placeholder\:text-slate-400::placeholder { color:#64748b; }

  /* brand */
  .dark .text-brand-600 { color:#60a5fa; }
  .dark .text-brand-700 { color:#93c5fd; }
  .dark .bg-brand-50, .dark .hover\:bg-brand-50:hover { background-color:rgba(59,130,246,.14); }
  .dark .bg-brand-50\/50, .dark .bg-brand-50\/60 { background-color:rgba(59,130,246,.09); }
  .dark .bg-brand-100, .dark .hover\:bg-brand-100:hover { background-color:rgba(59,130,246,.25); }
  .dark .text-brand-700 .material-symbols-outlined { color:inherit; }

  /* status colors */
  .dark .bg-amber-50, .dark .bg-amber-50\/50, .dark .bg-amber-50\/60 { background-color:rgba(245,158,11,.13); }
  .dark .bg-amber-100 { background-color:rgba(245,158,11,.22); }
  .dark .text-amber-700, .dark .text-amber-600, .dark .text-amber-500 { color:#fbbf24; }
  .dark .border-amber-200 { border-color:rgba(245,158,11,.35); }
  .dark .bg-emerald-50 { background-color:rgba(16,185,129,.13); }
  .dark .text-emerald-700, .dark .text-emerald-600 { color:#34d399; }
  .dark .border-emerald-200 { border-color:rgba(16,185,129,.35); }
  .dark .bg-red-50 { background-color:rgba(239,68,68,.13); }
  .dark .text-red-700, .dark .text-red-600 { color:#f87171; }
  .dark .border-red-200 { border-color:rgba(239,68,68,.35); }

  /* gradients */
  .dark .from-canvas { background-image:linear-gradient(to bottom right,#0b1220,#13213d); }
  .dark .from-brand-50 { background-image:linear-gradient(to bottom right,#13213d,#1a2c52); }
  .dark .from-amber-50 { background-image:linear-gradient(to bottom,#2b2a22,#2f2820); }

  /* components */
  .dark .card { background-color:#1e293b; border-color:#2b3a52; box-shadow:0 1px 3px rgba(0,0,0,.35); }
  .dark .input { background-color:#0f172a; border-color:#3b4a63; color:#f1f5f9; }
  .dark .input:focus { border-color:#60a5fa; --tw-ring-color:rgba(96,165,250,.25); }
  .dark .btn-secondary { background-color:rgba(59,130,246,.14); border-color:rgba(59,130,246,.3); color:#93c5fd; }
  .dark .btn-secondary:hover { background-color:rgba(59,130,246,.25); }
  .dark .btn-ghost { color:#cbd5e1; }
  .dark .btn-ghost:hover { background-color:#2b3a52; }
  .dark .btn-danger { background-color:rgba(239,68,68,.13); border-color:rgba(239,68,68,.35); color:#f87171; }
  .dark .btn-danger:hover { background-color:rgba(239,68,68,.22); }
  .dark .label { color:#cbd5e1; }
  .dark .th { color:#94a3b8; }
  .dark .pill-green { background-color:rgba(16,185,129,.13); color:#34d399; border-color:rgba(16,185,129,.35); }
  .dark .pill-amber { background-color:rgba(245,158,11,.13); color:#fbbf24; border-color:rgba(245,158,11,.35); }
  .dark .pill-blue  { background-color:rgba(59,130,246,.14); color:#93c5fd; border-color:rgba(59,130,246,.35); }
  .dark .pill-red   { background-color:rgba(239,68,68,.13);  color:#f87171; border-color:rgba(239,68,68,.35); }
  .dark .pill-slate { background-color:#2b3a52; color:#cbd5e1; border-color:#3b4a63; }
  .dark ::-webkit-scrollbar-thumb { background:#3b4a63; }
</style>