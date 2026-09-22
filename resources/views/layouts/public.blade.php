<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Portal Berita'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-50 text-stone-900 antialiased min-h-screen flex flex-col font-sans selection:bg-red-500 selection:text-white">
    <!-- Top Utility Bar -->
    <div class="bg-stone-900 text-stone-300 text-xs py-2 px-4 sm:px-6 lg:px-8 border-b border-stone-800">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-2">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-600 text-white">
                    LIVE
                </span>
                <span class="text-stone-300">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
            </div>
            <div class="flex items-center gap-4 text-stone-400">
                <span class="hidden md:inline">Portal Berita Nasional & Terkini</span>
                <span class="hidden md:inline text-stone-700">|</span>
                <a href="{{ route('admin.login') }}" class="hover:text-white transition-colors inline-flex items-center gap-1.5 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Akses Admin
                </a>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="bg-white border-b border-stone-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-5">
            <div class="flex items-center justify-between gap-4">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-3">
                    <!-- Mobile Hamburger Button -->
                    <button type="button" id="mobile-menu-btn" aria-label="Buka menu navigasi" class="lg:hidden p-2 rounded-lg text-stone-700 hover:bg-stone-100 hover:text-stone-950 focus:outline-hidden focus:ring-2 focus:ring-red-600">
                        <svg class="w-6 h-6" id="menu-icon-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="w-6 h-6 hidden" id="menu-icon-close" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <a href="{{ route('home') }}" class="inline-block group">
                        <div class="flex items-baseline gap-1">
                            <span class="font-extrabold text-2xl sm:text-3xl tracking-tight text-stone-950 group-hover:text-red-600 transition-colors">
                                PORTAL<span class="text-red-600">BERITA</span>
                            </span>
                        </div>
                        <p class="text-[11px] text-stone-500 font-medium tracking-wider uppercase hidden sm:block">Aktual, Berimbang & Independen</p>
                    </a>
                </div>

                <!-- Visual Search Bar Placeholder (Non-active as backend search is for later phase) -->
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center relative w-64 md:w-80">
                        <input type="text" disabled placeholder="Cari berita terkini... (segera hadir)" class="w-full bg-stone-100 border border-stone-200 text-stone-500 text-xs rounded-full pl-9 pr-3 py-2 cursor-not-allowed select-none">
                        <svg class="w-4 h-4 text-stone-400 absolute left-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Editorial badge -->
                    <div class="hidden xl:flex items-center gap-2 px-3 py-1.5 rounded-full bg-stone-100 border border-stone-200 text-xs text-stone-700">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="font-medium">Redaksi 24 Jam</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Navigation Bar -->
        <nav class="border-t border-stone-100 bg-white hidden lg:block" aria-label="Navigasi Utama">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-1 py-1 overflow-x-auto scrollbar-none">
                    <a href="{{ route('home') }}" class="px-3.5 py-2 text-sm font-bold border-b-2 transition-colors whitespace-nowrap {{ request()->routeIs('home') ? 'border-red-600 text-red-600' : 'border-transparent text-stone-700 hover:text-red-600 hover:border-stone-300' }}">
                        Beranda
                    </a>

                    @isset($navCategories)
                        @foreach($navCategories as $navCat)
                            @php
                                $isActive = request()->routeIs('categories.show') && isset($category) && $category->id === $navCat->id;
                            @endphp
                            <a href="{{ route('categories.show', $navCat) }}" class="px-3.5 py-2 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap {{ $isActive ? 'border-red-600 text-red-600' : 'border-transparent text-stone-700 hover:text-red-600 hover:border-stone-300' }}">
                                {{ $navCat->name }}
                            </a>
                        @endforeach
                    @endisset
                </div>
            </div>
        </nav>

        <!-- Mobile Drawer Navigation -->
        <div id="mobile-menu" class="hidden lg:hidden border-t border-stone-200 bg-white px-4 py-3 shadow-lg">
            <div class="flex flex-col space-y-1">
                <a href="{{ route('home') }}" class="px-3 py-2 text-sm font-bold rounded-md {{ request()->routeIs('home') ? 'bg-red-50 text-red-600 font-bold' : 'text-stone-700 hover:bg-stone-100' }}">
                    Beranda
                </a>
                <div class="pt-2 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-stone-400">Kategori Berita</div>
                @isset($navCategories)
                    @foreach($navCategories as $navCat)
                        @php
                            $isActive = request()->routeIs('categories.show') && isset($category) && $category->id === $navCat->id;
                        @endphp
                        <a href="{{ route('categories.show', $navCat) }}" class="px-3 py-2 text-sm font-medium rounded-md {{ $isActive ? 'bg-red-50 text-red-600 font-bold' : 'text-stone-700 hover:bg-stone-100' }}">
                            {{ $navCat->name }}
                        </a>
                    @endforeach
                @endisset
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-stone-950 text-stone-400 mt-16 border-t border-stone-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Column 1: Brand & About -->
                <div class="md:col-span-2">
                    <h3 class="font-extrabold text-xl text-white tracking-tight">PORTAL<span class="text-red-500">BERITA</span></h3>
                    <p class="mt-3 text-sm text-stone-400 leading-relaxed max-w-md">
                        Menyajikan kabar terpercaya, analisis mendalam, dan informasi terkini dari seluruh pelosok nusantara dengan standar jurnalistik yang berintegritas dan independen.
                    </p>
                    <div class="mt-4 flex items-center gap-3 text-xs text-stone-500">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Terverifikasi Dewan Pers (Standar MVP)
                        </span>
                    </div>
                </div>

                <!-- Column 2: Categories Nav -->
                <div>
                    <h4 class="font-bold text-xs text-stone-200 uppercase tracking-wider mb-3">Kanal Kategori</h4>
                    <ul class="space-y-2 text-sm">
                        @isset($navCategories)
                            @foreach($navCategories->take(6) as $footerCat)
                                <li>
                                    <a href="{{ route('categories.show', $footerCat) }}" class="text-stone-400 hover:text-white transition-colors">
                                        {{ $footerCat->name }}
                                    </a>
                                </li>
                            @endforeach
                        @endisset
                    </ul>
                </div>

                <!-- Column 3: Redaksi & Kontak -->
                <div>
                    <h4 class="font-bold text-xs text-stone-200 uppercase tracking-wider mb-3">Redaksi & Info</h4>
                    <ul class="space-y-2 text-xs text-stone-400 leading-relaxed">
                        <li>Gedung Pers Merdeka Lt. 4, Jakarta Pusat</li>
                        <li>Email: redaksi@portalberita.test</li>
                        <li>Arsitektur: Laravel 13 & Tailwind CSS v4</li>
                        <li>Status: Tahap 3 (Public Layout & Feed)</li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-stone-900 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-stone-500 gap-4">
                <p>&copy; {{ date('Y') }} PORTALBERITA. Seluruh hak cipta dilindungi undang-undang.</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('home') }}" class="hover:text-stone-400">Beranda</a>
                    <span>&bull;</span>
                    <a href="{{ route('admin.login') }}" class="hover:text-stone-400">Redaksi Login</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Menu Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const iconOpen = document.getElementById('menu-icon-open');
            const iconClose = document.getElementById('menu-icon-close');

            if (menuBtn && mobileMenu) {
                menuBtn.addEventListener('click', function() {
                    const isHidden = mobileMenu.classList.toggle('hidden');
                    if (iconOpen && iconClose) {
                        iconOpen.classList.toggle('hidden', !isHidden);
                        iconClose.classList.toggle('hidden', isHidden);
                    }
                    menuBtn.setAttribute('aria-expanded', !isHidden);
                });
            }
        });
    </script>
</body>
</html>
