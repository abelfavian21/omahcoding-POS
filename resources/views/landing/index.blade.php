<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CashierStore - Next-Gen POS & Operating System untuk Bisnis Modern</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL@20..48,400,0..1&display=swap"
        rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    @verbatim
        <style>
            .material-symbols-outlined {
                font-family: 'Material Symbols Outlined';
                font-weight: normal;
                font-style: normal;
                font-size: 20px;
                line-height: 1;
                letter-spacing: normal;
                text-transform: none;
                display: inline-block;
                white-space: nowrap;
                direction: ltr;
                vertical-align: middle;
                font-variation-settings: 'FILL' 0;
                -webkit-font-smoothing: antialiased;
            }

            .fill-1 {
                font-variation-settings: 'FILL' 1 !important;
            }

            /* Parallax & Ambient Background Styles */
            .bg-grid-pattern {
                background-size: 40px 40px;
                background-image:
                    linear-gradient(to right, rgba(37, 99, 235, 0.05) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(37, 99, 235, 0.05) 1px, transparent 1px);
            }

            .parallax-orb {
                filter: blur(80px);
                pointer-events: none;
                transition: transform 0.2s cubic-bezier(0.2, 0, 0.2, 1);
                will-change: transform;
            }

            .parallax-layer {
                will-change: transform;
                transition: transform 0.15s ease-out;
            }

            @keyframes floatSlow {

                0%,
                100% {
                    transform: translateY(0px) rotate(0deg);
                }

                50% {
                    transform: translateY(-12px) rotate(1deg);
                }
            }

            @keyframes pulseGlow {

                0%,
                100% {
                    opacity: 0.6;
                    transform: scale(1);
                }

                50% {
                    opacity: 0.85;
                    transform: scale(1.04);
                }
            }

            .animate-float-slow {
                animation: floatSlow 7s ease-in-out infinite;
            }

            .animate-pulse-glow {
                animation: pulseGlow 5s ease-in-out infinite;
            }

            /* Hide scrollbar for custom clean look */
            ::-webkit-scrollbar {
                width: 8px;
            }

            ::-webkit-scrollbar-track {
                background: #f1f5f9;
            }

            ::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 999px;
            }

            ::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }
        </style>
    @endverbatim
</head>

<body
    class="font-sans bg-[#f8faff] text-slate-800 antialiased overflow-x-hidden selection:bg-blue-500 selection:text-white">

    <!-- Floating Parallax Ambient Orbs -->
    <div id="orb1"
        class="fixed -top-40 -left-40 w-[600px] h-[600px] bg-blue-300/30 rounded-full parallax-orb -z-10 animate-pulse-glow">
    </div>
    <div id="orb2"
        class="fixed top-1/3 -right-40 w-[550px] h-[550px] bg-indigo-200/40 rounded-full parallax-orb -z-10"></div>
    <div id="orb3"
        class="fixed bottom-10 left-1/4 w-[500px] h-[500px] bg-cyan-200/30 rounded-full parallax-orb -z-10"></div>

    <!-- Persistent Sticky Header / Navbar -->
    <header
        class="sticky top-0 z-50 backdrop-blur-xl bg-white/85 border-b border-slate-200/70 transition-all duration-300 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="#" class="flex items-center gap-3 group">
                <div
                    class="w-11 h-11 rounded-xl bg-gradient-to-tr from-blue-700 to-blue-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/25 group-hover:scale-105 transition-transform duration-200">
                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                        <path
                            d="M4 6a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm0 6h16v8a2 2 0 01-2 2H6a2 2 0 01-2-2v-8zm5 3a1 1 0 000 2h6a1 1 0 100-2H9z" />
                    </svg>
                </div>
                <div>
                    <span
                        class="text-xl font-black tracking-tight text-slate-900 group-hover:text-blue-600 transition-colors">Cashier<span
                            class="text-blue-600">Store</span></span>
                    <span
                        class="block text-[10px] font-semibold text-slate-400 uppercase tracking-widest -mt-1">Enterprise
                        POS & OS</span>
                </div>
            </a>

            <!-- Center Navigation Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                <a href="#services" class="hover:text-blue-600 transition-colors flex items-center gap-1.5">
                    <span>Layanan & Produk</span>
                </a>
                <a href="#solutions" class="hover:text-blue-600 transition-colors">Kategori Usaha</a>
                <a href="#stats" class="hover:text-blue-600 transition-colors">Keunggulan</a>
                <a href="#auth-section" class="hover:text-blue-600 transition-colors">Mulai Registrasi</a>
            </nav>

            <!-- Auth & Action CTA -->
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('dashboard') : route('pos') }}"
                        class="text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 px-5 py-2.5 rounded-xl shadow-lg shadow-blue-600/30 hover:-translate-y-0.5 transition-all">Buka
                        Dashboard</a>
                @else
                    <a href="#masuk" onclick="switchAuthTab('login')"
                        class="text-sm font-bold text-slate-700 hover:text-blue-600 px-4 py-2.5 rounded-xl hover:bg-slate-100 transition-all">Masuk
                        Akun</a>
                    <a href="#auth-section" onclick="switchAuthTab('register')"
                        class="text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 px-5 py-2.5 rounded-xl shadow-lg shadow-blue-600/30 hover:shadow-blue-600/40 hover:-translate-y-0.5 transition-all">Daftar
                        Usaha Baru</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- HERO SECTION WITH PARALLAX DEPTH -->
    <section class="relative pt-12 pb-24 lg:pt-20 lg:pb-32 overflow-hidden bg-grid-pattern">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid lg:grid-cols-12 gap-12 items-center">

                <!-- Left Hero Content (Parallax layer 1) -->
                <div class="lg:col-span-7 space-y-7 parallax-layer" data-speed="0.05">
                    <div
                        class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-blue-50 border border-blue-200/80 text-blue-700 text-xs font-bold tracking-wide shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-blue-600 animate-ping"></span>
                        <span>All-in-One Cloud POS System &bull; Versi 3.4 Live</span>
                    </div>

                    <h1
                        class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-[1.12]">
                        Tumbuhkan Bisnis F&B & Retail Anda dengan <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">Sistem
                            Kasir Pintar</span>.
                    </h1>

                    <p class="text-lg text-slate-600 leading-relaxed max-w-2xl font-normal">
                        CashierStore menghadirkan otomasi meja kasir terintegrasi, manajemen stok multi-cabang,
                        loyalitas pelanggan cerdas, dan sinkronisasi real-time cloud dalam satu sistem operasi yang
                        elegan dan mudah digunakan.
                    </p>

                    <div class="flex flex-wrap items-center gap-4 pt-2">
                        <a href="#auth-section"
                            class="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold text-base rounded-2xl shadow-xl shadow-blue-600/30 hover:shadow-blue-600/40 hover:-translate-y-1 transition-all flex items-center gap-3">
                            <span>Registrasi Bisnis Sekarang</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                        <a href="#services"
                            class="px-7 py-4 bg-white hover:bg-slate-50 text-slate-700 font-bold text-base rounded-2xl border border-slate-200 shadow-sm hover:border-slate-300 transition-all flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Lihat Demo & Layanan</span>
                        </a>
                    </div>

                    <!-- Trust Badges -->
                    <div
                        class="pt-6 border-t border-slate-200/80 flex flex-wrap items-center gap-8 text-slate-500 text-xs font-semibold">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Free 14 Hari Uji Coba</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Setup Kurang dari 5 Menit</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>256-bit Cloud TLS Security</span>
                        </div>
                    </div>
                </div>

                <!-- Right Hero Visuals with Layered Floating Mockups (Parallax layer 2) -->
                <div class="lg:col-span-5 relative parallax-layer" data-speed="-0.08">
                    <div class="relative w-full max-w-lg mx-auto">
                        <!-- Background Glow Card -->
                        <div
                            class="absolute -inset-2 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-3xl blur-2xl opacity-20 transform -rotate-1">
                        </div>

                        <!-- Main POS Mockup Card -->
                        <div
                            class="relative bg-white rounded-3xl p-6 shadow-2xl border border-slate-100 animate-float-slow">
                            <!-- Mockup Browser Bar -->
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-3 h-3 rounded-full bg-rose-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                                </div>
                                <div
                                    class="text-[11px] font-mono text-slate-400 px-3 py-1 bg-slate-50 rounded-lg border border-slate-200/60">
                                    cashierstore.com/orely-pos
                                </div>
                                <div class="w-3 h-3"></div>
                            </div>

                            <!-- Mini POS Interface Simulation -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-bold text-slate-900 text-sm">Orely Cafe &bull; Order #12930
                                        </h4>
                                        <p class="text-xs text-slate-400">Meja 05 &bull; Pelayan: Clara (Shift 1)</p>
                                    </div>
                                    <span
                                        class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-200">Live
                                        Sync</span>
                                </div>

                                <!-- Menu Item Rows -->
                                <div class="space-y-2.5">
                                    <div
                                        class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center font-bold text-xs">
                                                <x-ico v="tapas" style="font-size:18px" /></div>
                                            <div>
                                                <div class="text-xs font-bold text-slate-800">Chicken Dimsum (x1)</div>
                                                <div class="text-[10px] text-slate-400">Sides & Appetizer</div>
                                            </div>
                                        </div>
                                        <span class="text-xs font-black text-slate-900">Rp 38.000</span>
                                    </div>

                                    <div
                                        class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs">
                                                <x-ico v="emoji_food_beverage" style="font-size:18px" /></div>
                                            <div>
                                                <div class="text-xs font-bold text-slate-800">Matcha Latte (x1)</div>
                                                <div class="text-[10px] text-slate-400">Drinks &bull; Oat Milk</div>
                                            </div>
                                        </div>
                                        <span class="text-xs font-black text-slate-900">Rp 32.000</span>
                                    </div>

                                    <div
                                        class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-xs">
                                                <x-ico v="rice_bowl" style="font-size:18px" /></div>
                                            <div>
                                                <div class="text-xs font-bold text-slate-800">Omurice Bowl (x2)</div>
                                                <div class="text-[10px] text-slate-400">Main Course</div>
                                            </div>
                                        </div>
                                        <span class="text-xs font-black text-slate-900">Rp 110.000</span>
                                    </div>
                                </div>

                                <!-- Total & Checkout Pill -->
                                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                    <div>
                                        <span class="text-xs text-slate-400 block">Total Transaksi</span>
                                        <span class="text-xl font-black text-blue-600">Rp 198.000</span>
                                    </div>
                                    <button
                                        class="px-4 py-2 bg-blue-600 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-500/20">
                                        Proses Bayar (QRIS/Cash)
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Floating KPI Pill 1 (Bottom Left) -->
                        <div class="absolute -bottom-6 -left-6 bg-white/95 backdrop-blur-md p-4 rounded-2xl shadow-xl border border-slate-100 flex items-center gap-3.5 animate-bounce"
                            style="animation-duration: 4s;">
                            <div
                                class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs text-slate-400 font-semibold">Omset Harian Toko</div>
                                <div class="text-base font-extrabold text-slate-900">Rp 2.845.500 <span
                                        class="text-emerald-500 text-xs font-bold">+18.4%</span></div>
                            </div>
                        </div>

                        <!-- Floating KPI Pill 2 (Top Right) -->
                        <div
                            class="absolute -top-6 -right-6 bg-white/95 backdrop-blur-md p-3.5 rounded-2xl shadow-xl border border-slate-100 flex items-center gap-3">
                            <div
                                class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-[11px] text-slate-400 font-semibold">Member Loyalitas</div>
                                <div class="text-sm font-extrabold text-slate-900">1,428 Pelanggan</div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- STATS & CREDIBILITY BAR -->
    <section id="stats" class="py-12 bg-white border-y border-slate-200/70">
        <div class="max-w-7xl mx-auto px-6">
            <div
                class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-y md:divide-y-0 md:divide-x divide-slate-100">
                <div class="space-y-1 pt-4 md:pt-0">
                    <div class="text-3xl lg:text-4xl font-black text-slate-900 tracking-tight">12,500+</div>
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Merchant Terdaftar</div>
                </div>
                <div class="space-y-1 pt-4 md:pt-0">
                    <div class="text-3xl lg:text-4xl font-black text-blue-600 tracking-tight">99.98%</div>
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">SLA Cloud Uptime</div>
                </div>
                <div class="space-y-1 pt-4 md:pt-0">
                    <div class="text-3xl lg:text-4xl font-black text-slate-900 tracking-tight">&lt; 1.2 Detik</div>
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kecepatan Checkout POS</div>
                </div>
                <div class="space-y-1 pt-4 md:pt-0">
                    <div class="text-3xl lg:text-4xl font-black text-emerald-600 tracking-tight">4.9 / 5.0</div>
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Rating Kepuasan Owner</div>
                </div>
            </div>
        </div>
    </section>

    <!-- PRODUK & LAYANAN KAMI (CORE SERVICES & FEATURES) -->
    <section id="services" class="py-24 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">

            <!-- Section Title -->
            <div class="text-center max-w-3xl mx-auto mb-16 space-y-4">
                <div
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-100/70 text-blue-700 text-xs font-bold tracking-wide">
                    EKOSISTEM LENGKAP KASIR
                </div>
                <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                    Layanan & Fitur Unggulan CashierStore
                </h2>
                <p class="text-slate-600 text-base leading-relaxed">
                    Dirancang khusus untuk menyederhanakan operasional harian gerai Anda, mulai dari transaksi pesanan,
                    sinkronisasi dapur, hingga laporan keuangan otomatis.
                </p>
            </div>

            <!-- Feature Cards Grid (with slight Parallax stagger) -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

                <!-- Service Card 1 -->
                <div
                    class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 group">
                    <div
                        class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-blue-600 transition-colors">Smart
                        POS & Order Line</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6">
                        Antarmuka kasir super responsif dengan dukungan layar sentuh, split bill, kustomisasi pesanan
                        (add-on, note alergi), serta cetak struk multi-printer kasir & dapur.
                    </p>
                    <ul class="space-y-2 text-xs font-semibold text-slate-500">
                        <li class="flex items-center gap-2"><span
                                class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Cepat tanpa lag, offline mode
                            support</li>
                        <li class="flex items-center gap-2"><span
                                class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Manajemen meja & status pesanan
                            realtime</li>
                    </ul>
                </div>

                <!-- Service Card 2 -->
                <div
                    class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 group">
                    <div
                        class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-indigo-600 transition-colors">
                        Dashboard Analitik & Sales</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6">
                        Pantau performa penjualan, laba bersih, produk terlaris harian (*Top Items*), dan jam-jam sibuk
                        toko secara langsung dari laptop maupun smartphone Anda.
                    </p>
                    <ul class="space-y-2 text-xs font-semibold text-slate-500">
                        <li class="flex items-center gap-2"><span
                                class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>Grafik pendapatan per jam, hari &
                            bulan</li>
                        <li class="flex items-center gap-2"><span
                                class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>Export laporan pajak & pembukuan
                            otomatis</li>
                    </ul>
                </div>

                <!-- Service Card 3 -->
                <div
                    class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 group">
                    <div
                        class="w-14 h-14 rounded-2xl bg-cyan-50 text-cyan-600 flex items-center justify-center mb-6 group-hover:bg-cyan-600 group-hover:text-white transition-colors">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-cyan-600 transition-colors">CRM &
                        Loyalty Member</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6">
                        Bangun loyalitas pembeli setia dengan sistem tiering (Bronze, Silver, Gold, VIP), pengumpulan
                        poin belanja otomatis, voucher ulang tahun, dan catatan selera pelanggan.
                    </p>
                    <ul class="space-y-2 text-xs font-semibold text-slate-500">
                        <li class="flex items-center gap-2"><span
                                class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>Riwayat kunjungan & total akumulasi
                            belanja</li>
                        <li class="flex items-center gap-2"><span
                                class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>Catatan preferensi & alert alergi
                            staf kasir</li>
                    </ul>
                </div>

                <!-- Service Card 4 -->
                <div
                    class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 group">
                    <div
                        class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-6 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-amber-600 transition-colors">
                        Manajemen Stok & Bahan Baku</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6">
                        Kalkulasi bahan baku terpotong otomatis per porsi menu terjual (HPP & COGS terukur). Notifikasi
                        instan saat stok menipis agar operasional tidak terhenti.
                    </p>
                    <ul class="space-y-2 text-xs font-semibold text-slate-500">
                        <li class="flex items-center gap-2"><span
                                class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Transfer stok antar cabang mudah
                        </li>
                        <li class="flex items-center gap-2"><span
                                class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Laporan opname & selisih bahan
                            akurat</li>
                    </ul>
                </div>

                <!-- Service Card 5 -->
                <div
                    class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 group">
                    <div
                        class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-emerald-600 transition-colors">
                        Integrasi Pembayaran Multi-Channel</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6">
                        Terima segala bentuk transaksi modern: QRIS instan, e-Wallet (GoPay, OVO, ShopeePay), Kartu
                        Debit/Kredit EDC, transfer bank, hingga uang tunai dengan verifikasi cepat.
                    </p>
                    <ul class="space-y-2 text-xs font-semibold text-slate-500">
                        <li class="flex items-center gap-2"><span
                                class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Settlement dana cepat &
                            transparan</li>
                        <li class="flex items-center gap-2"><span
                                class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Bebas biaya sewa mesin
                            tersembunyi</li>
                    </ul>
                </div>

                <!-- Service Card 6 -->
                <div
                    class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 group">
                    <div
                        class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center mb-6 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-purple-600 transition-colors">Hak
                        Akses Karyawan & PIN Shift</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6">
                        Mode pergantian shift super kilat dengan 4-digit PIN kasir. Lindungi privasi finansial dengan
                        pembatasan hak akses kasir, supervisor, dan owner secara ketat.
                    </p>
                    <ul class="space-y-2 text-xs font-semibold text-slate-500">
                        <li class="flex items-center gap-2"><span
                                class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>Catatan jam kerja & shift aktif
                            staf</li>
                        <li class="flex items-center gap-2"><span
                                class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>Cegah kecurangan & void tanpa
                            izin</li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- PARALLAX SHOWCASE INTERMEZZO -->
    <section id="solutions"
        class="py-20 bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-15 bg-grid-pattern"></div>
        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="grid lg:grid-cols-12 gap-10 items-center">
                <div class="lg:col-span-8 space-y-4">
                    <span class="text-blue-400 font-bold text-xs uppercase tracking-widest">Solusi Terpercaya Skala
                        Nasional</span>
                    <h2 class="text-3xl sm:text-4xl font-black tracking-tight leading-tight">
                        Dari Coffee Shop Mandiri hingga Jaringan Restoran Multi-Cabang.
                    </h2>
                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed max-w-2xl font-normal">
                        CashierStore dirancang fleksibel menyesuaikan skala operasional Anda. Tambah cabang baru cukup
                        dalam hitungan klik tanpa perlu investasi server lokal yang rumit.
                    </p>
                </div>
                <div class="lg:col-span-4 flex lg:justify-end">
                    <a href="#auth-section"
                        class="px-7 py-4 bg-white text-blue-700 hover:bg-blue-50 font-extrabold rounded-2xl shadow-xl transition-all hover:scale-105">
                        Konsultasi Kebutuhan Cabang
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================
       AREA NON-PARALLAX: LOGIN, REGISTRASI BISNIS,
       PILIHAN KATEGORI USAHA & KONFIRMASI USAHA
  ======================================================== -->
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
        <section id="auth-section" class="py-20 bg-[#f4f7fc] relative z-20">
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
        <section id="auth-section" class="py-20 bg-[#f4f7fc] relative z-20" data-initial-tab="{{ $initialTab }}"
            data-initial-step="{{ $initialStep }}" data-login-mode="{{ $loginMode }}">
            <div id="masuk" class="max-w-6xl mx-auto px-6">
                <div class="text-center max-w-2xl mx-auto mb-12 space-y-3">
                    <div
                        class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-100 text-blue-700 text-xs font-bold tracking-wide">
                        <span>PORTAL ONBOARDING BISNIS</span></div>
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Mulai Digitalisasi Usaha
                        Anda</h2>
                    <p class="text-slate-600 text-sm sm:text-base">Daftarkan usaha Anda, pilih kategori yang tepat,
                        konfirmasi data, lalu langsung masuk ke dashboard kasir CashierStore.</p>
                </div>

                <div class="bg-white rounded-3xl shadow-xl border border-slate-200/90 overflow-hidden">
                    <div class="grid grid-cols-2 border-b border-slate-200 bg-slate-50/70 p-2">
                        <button type="button" id="tab-login-btn" onclick="switchAuthTab('login')"
                            class="py-3.5 px-6 rounded-2xl font-bold text-sm text-slate-600 transition-all">Masuk Akun
                            Pemilik / Kasir</button>
                        <button type="button" id="tab-register-btn" onclick="switchAuthTab('register')"
                            class="py-3.5 px-6 rounded-2xl font-bold text-sm text-slate-600 transition-all">Registrasi
                            &amp; Konfigurasi Bisnis Baru</button>
                    </div>

                    {{-- ========== LOGIN ========== --}}
                    <div id="view-login" class="p-8 sm:p-12 max-w-xl mx-auto hidden">
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
                    <div id="view-register" class="p-6 sm:p-10">
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
                                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
                                    @foreach ($categories as $c)
                                        <div class="cat-card relative p-5 rounded-2xl border-2 shadow-sm transition-all {{ $c->is_active ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}"
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
                                        class="px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-600/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed">Lanjut:
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
                                        class="px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm rounded-xl shadow-lg shadow-emerald-600/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed">Konfirmasi
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
                                class="inline-block px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-600/30">
                                Buka Dashboard Kasir →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif


    <!-- TESTIMONIALS / CLIENT BRANDS (PARALLAX DEPTH) -->
    <section class="py-24 bg-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16 space-y-3">
                <span class="text-blue-600 font-extrabold text-xs tracking-wider uppercase">DIPERCAYA RATUSAN BRAND
                    KAFE & RETAIL</span>
                <h2 class="text-3xl font-black text-slate-900">Kata Mereka yang Telah Menggunakan CashierStore</h2>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div
                    class="p-8 rounded-3xl bg-slate-50 border border-slate-200/80 space-y-4 hover:shadow-lg transition-all">
                    <div class="flex text-amber-400 gap-0.5">
                        @for ($st = 0; $st < 5; $st++)
                            <span class="material-symbols-outlined fill-1" style="font-size:18px"
                                aria-hidden="true">star</span>


                        @endfor
                    </div>
                    <p class="text-slate-600 text-sm leading-relaxed italic">
                        "Waktu tunggu antrean kopi saat jam makan siang berkurang drastis berkat fitur Quick Barista PIN
                        dan split bill CashierStore. Laporan penjualan harian langsung rapi tanpa lembur rekap manual."
                    </p>
                    <div class="pt-2 flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-sm">
                            SJ</div>
                        <div>
                            <div class="font-bold text-slate-900 text-sm">Sarah Jenkins</div>
                            <div class="text-xs text-slate-500">Founder, Orely Cafe Sydney & Downtown</div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div
                    class="p-8 rounded-3xl bg-slate-50 border border-slate-200/80 space-y-4 hover:shadow-lg transition-all">
                    <div class="flex text-amber-400 gap-0.5">
                        @for ($st = 0; $st < 5; $st++)
                            <span class="material-symbols-outlined fill-1" style="font-size:18px"
                                aria-hidden="true">star</span>
                        @endfor
                    </div>
                    <p class="text-slate-600 text-sm leading-relaxed italic">
                        "Manajemen stok bahan baku di 4 cabang restoran kami sekarang terhubung otomatis dengan resep
                        kitchen display. Pemborosan berkurang hingga 22% di bulan pertama!"
                    </p>
                    <div class="pt-2 flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-sm">
                            DN</div>
                        <div>
                            <div class="font-bold text-slate-900 text-sm">Daniel Nguyen</div>
                            <div class="text-xs text-slate-500">Operations Lead, Dimsum Haven Group</div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div
                    class="p-8 rounded-3xl bg-slate-50 border border-slate-200/80 space-y-4 hover:shadow-lg transition-all">
                    <div class="flex text-amber-400 gap-0.5">
                        @for ($st = 0; $st < 5; $st++)
                            <span class="material-symbols-outlined fill-1" style="font-size:18px"
                                aria-hidden="true">star</span>
                        @endfor
                    </div>
                    <p class="text-slate-600 text-sm leading-relaxed italic">
                        "Setup awal sangat mudah. Begitu pilih kategori retail toko, sistem langsung siap baca barcode
                        ribuan item tanpa ribet konfigurasi server atau instal software berat."
                    </p>
                    <div class="pt-2 flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-sm">
                            CW</div>
                        <div>
                            <div class="font-bold text-slate-900 text-sm">Clara Wijaya</div>
                            <div class="text-xs text-slate-500">Owner, Wijaya Organic Market</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA BANNER WITH PARALLAX GLOW -->
    <section class="py-20 bg-blue-600 text-white relative overflow-hidden">
        <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-400/30 rounded-full blur-3xl pointer-events-none">
        </div>
        <div
            class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-500/30 rounded-full blur-3xl pointer-events-none">
        </div>

        <div class="max-w-5xl mx-auto px-6 text-center space-y-6 relative z-10">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight leading-tight">
                Siap Melejitkan Efisiensi & Omset Bisnis Anda?
            </h2>
            <p class="text-blue-100 text-base sm:text-lg max-w-2xl mx-auto font-normal">
                Bergabunglah bersama ribuan pebisnis sukses lainnya. Nikmati akses penuh ke seluruh fitur tanpa kartu
                kredit selama 14 hari pertama.
            </p>
            <div class="pt-2 flex flex-wrap items-center justify-center gap-4">
                <a href="#auth-section" onclick="switchAuthTab('register')"
                    class="px-8 py-4 bg-white text-blue-700 hover:bg-blue-50 font-black rounded-2xl shadow-xl hover:-translate-y-1 transition-all">
                    Daftar Sekarang - Gratis 14 Hari
                </a>
                <a href="#services"
                    class="px-7 py-4 bg-blue-700/60 hover:bg-blue-700 text-white font-bold rounded-2xl border border-blue-400/40 transition-all">
                    Pelajari Semua Fitur
                </a>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-slate-900 text-slate-400 pt-16 pb-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-10 pb-12 border-b border-slate-800">

                <!-- Brand Info -->
                <div class="col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center text-white font-bold">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                <path
                                    d="M4 6a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm0 6h16v8a2 2 0 01-2 2H6a2 2 0 01-2-2v-8zm5 3a1 1 0 000 2h6a1 1 0 100-2H9z" />
                            </svg>
                        </div>
                        <span class="text-lg font-black text-white">Cashier<span
                                class="text-blue-500">Store</span></span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-sm">
                        Platform sistem kasir POS, operasional kafe, dan manajemen bisnis terintegrasi berbasis cloud
                        untuk pelaku UMKM dan enterprise modern.
                    </p>
                    <div class="text-xs text-slate-500">
                        &copy; 2024 CashierStore Inc. All rights reserved. Orely POS Ecosystem.
                    </div>
                </div>

                <!-- Links 1: Produk -->
                <div class="space-y-3">
                    <div class="text-xs font-bold text-white uppercase tracking-wider">Produk</div>
                    <ul class="space-y-2 text-xs">
                        <li><a href="#services" class="hover:text-white transition-colors">POS Cashier Terminal</a>
                        </li>
                        <li><a href="#services" class="hover:text-white transition-colors">Kitchen Display (KDS)</a>
                        </li>
                        <li><a href="#services" class="hover:text-white transition-colors">Dashboard Analytics</a>
                        </li>
                        <li><a href="#services" class="hover:text-white transition-colors">Loyalty & CRM</a></li>
                    </ul>
                </div>

                <!-- Links 2: Kategori Usaha -->
                <div class="space-y-3">
                    <div class="text-xs font-bold text-white uppercase tracking-wider">Kategori Usaha</div>
                    <ul class="space-y-2 text-xs">
                        <li><a href="#auth-section" class="hover:text-white transition-colors">Coffee Shop & Cafe</a>
                        </li>
                        <li><a href="#auth-section" class="hover:text-white transition-colors">Restoran & Dining</a>
                        </li>
                        <li><a href="#auth-section" class="hover:text-white transition-colors">Minimarket & Retail</a>
                        </li>
                        <li><a href="#auth-section" class="hover:text-white transition-colors">Bakery & Pastry</a>
                        </li>
                        <li><a href="#auth-section" class="hover:text-white transition-colors">Salon & Barbershop</a>
                        </li>
                    </ul>
                </div>

                <!-- Links 3: Bantuan & Hubungi -->
                <div class="space-y-3">
                    <div class="text-xs font-bold text-white uppercase tracking-wider">Bantuan & Kontak</div>
                    <ul class="space-y-2 text-xs">
                        <li><span class="text-slate-300">24/7 Priority Support</span></li>
                        <li><span class="text-slate-400">help@cashierstore.com</span></li>
                        <li><span class="text-slate-400">+62 21 8890 1200</span></li>
                        <li><span class="text-slate-400">Jakarta &bull; Sydney &bull; Tokyo</span></li>
                    </ul>
                </div>

            </div>

            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:text-slate-400">Kebijakan Privasi</a>
                    <span>&bull;</span>
                    <a href="#" class="hover:text-slate-400">Syarat & Ketentuan</a>
                    <span>&bull;</span>
                    <a href="#" class="hover:text-slate-400">Keamanan Cloud TLS</a>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Semua Server & Gateway POS Beroperasi Normal</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- ==========================================
       VANILLA JAVASCRIPT:
       1. PARALLAX SCROLL & MOUSE TRACKING
          (Hanya aktif pada Landing Hero & Service,
           TETAP STABIL & NON-PARALLAX PADA REGISTRASI)
       2. TAB SWITCHER (LOGIN VS REGISTER)
       3. MULTI-STEP WIZARD (DATA -> KATEGORI -> KONFIRMASI)
       4. SELEKSI KATEGORI USAHA & PREVIEW
  ========================================== -->
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

                /* ---------- Parallax (scroll + mouse) ---------- */
                if (!reduce) {
                    window.addEventListener('scroll', () => {
                        const y = window.pageYOffset;
                        document.querySelectorAll('.parallax-layer').forEach(el => {
                            el.style.transform =
                                `translateY(${y * parseFloat(el.dataset.speed || '0.04')}px)`;
                        });
                        const o1 = $('orb1'),
                            o2 = $('orb2');
                        if (o1) o1.style.transform = `translate(${y * 0.05}px, ${y * 0.1}px)`;
                        if (o2) o2.style.transform = `translate(${-y * 0.04}px, ${-y * 0.06}px)`;
                    }, {
                        passive: true
                    });
                    window.addEventListener('mousemove', e => {
                        const o3 = $('orb3');
                        if (o3) o3.style.transform =
                            `translate(${(e.clientX / innerWidth - .5) * 40}px, ${(e.clientY / innerHeight - .5) * 40}px)`;
                    });
                }

                // user sudah login: form registrasi tidak dirender (hanya kartu "Kamu sedang masuk")
                if (!auth || !auth.dataset.initialTab) {
                    window.switchAuthTab = () => {};
                    return;
                }

                /* ---------- Tab Masuk / Registrasi ---------- */
                const ON = 'py-3.5 px-6 rounded-2xl font-bold text-sm bg-white text-blue-600 shadow-sm transition-all';
                const OFF = 'py-3.5 px-6 rounded-2xl font-bold text-sm text-slate-600 hover:text-slate-900 transition-all';
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

</body>

</html>
