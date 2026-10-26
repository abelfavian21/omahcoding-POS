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
<link rel="stylesheet" href="{{ asset('css/landing-tw.css') }}?v={{ filemtime(public_path('css/landing-tw.css')) }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL@20..48,400,0..1&display=swap" rel="stylesheet">
