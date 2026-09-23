<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $siteName = config('site.name', 'JatimNusa');
        $siteDesc = config('site.description', 'Portal berita independen, cerdas, dan terpercaya menyajikan kabar terkini, mendalam, dan berimbang dari seluruh nusantara.');
        $pageTitle = trim($__env->yieldContent('title')) ?: $siteName;
        $pageDesc = trim($__env->yieldContent('meta_description')) ?: $siteDesc;
        $pageCanonical = trim($__env->yieldContent('canonical_url')) ?: url()->current();
        $ogTitle = trim($__env->yieldContent('og_title')) ?: $pageTitle;
        $ogDesc = trim($__env->yieldContent('og_description')) ?: $pageDesc;
        $ogUrl = trim($__env->yieldContent('og_url')) ?: $pageCanonical;
        $twitterTitle = trim($__env->yieldContent('twitter_title')) ?: $ogTitle;
        $twitterDesc = trim($__env->yieldContent('twitter_description')) ?: $ogDesc;
    @endphp

    <!-- Title & SEO Metadata -->
    <title>{!! $pageTitle !!}</title>
    <meta name="description" content="{!! $pageDesc !!}">
    <meta name="robots" content="@yield('meta_robots', 'index, follow')">
    <link rel="canonical" href="{!! $pageCanonical !!}">

    <!-- Open Graph Metadata -->
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="{!! $ogTitle !!}">
    <meta property="og:description" content="{!! $ogDesc !!}">
    <meta property="og:url" content="{!! $ogUrl !!}">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
    @endif
    @hasSection('article_published_time')
        <meta property="article:published_time" content="@yield('article_published_time')">
    @endif
    @hasSection('article_modified_time')
        <meta property="article:modified_time" content="@yield('article_modified_time')">
    @endif
    @hasSection('article_section')
        <meta property="article:section" content="@yield('article_section')">
    @endif
    @yield('article_tags')

    <!-- Twitter / X Card Metadata -->
    <meta name="twitter:card" content="@yield('twitter_card', 'summary')">
    <meta name="twitter:title" content="{!! $twitterTitle !!}">
    <meta name="twitter:description" content="{!! $twitterDesc !!}">
    @hasSection('og_image')
        <meta name="twitter:image" content="@yield('og_image')">
    @endif

    <!-- JSON-LD Structured Data -->
    @yield('structured_data')

    <!-- Favicon & Icons -->
    <link rel="icon" type="image/png" href="{{ asset('images/branding/favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/branding/logo-mark.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-50 text-stone-900 antialiased min-h-screen flex flex-col font-sans selection:bg-red-500 selection:text-white">
    <!-- Top Utility Bar -->
    <div class="bg-stone-900 text-stone-300 text-xs py-1.5 px-4 sm:px-6 lg:px-8 border-b border-stone-800">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-2">
            <div class="flex items-center gap-2">
                <span class="text-stone-300">{{ \Carbon\Carbon::now(config('site.display_timezone', 'Asia/Jakarta'))->translatedFormat('l, d F Y') }}</span>
            </div>
            <div class="flex items-center gap-4 text-stone-400">
                <span class="hidden md:inline">JatimNusa - Dari Jatim untuk Nusa</span>
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-1 sm:py-1.5">
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

                    <a href="{{ route('home') }}" class="inline-flex items-center group" aria-label="JatimNusa">
                        <img
                            src="{{ asset('images/branding/logo.png') }}"
                            alt="JatimNusa"
                            class="h-11 sm:h-12 md:h-13 lg:h-15 xl:h-16 w-auto object-contain -my-1 sm:-my-1.5 transition-transform group-hover:scale-[1.02]"
                        />
                    </a>
                </div>

                <!-- Desktop Search Form -->
                <div class="flex items-center justify-end">
                    <form action="{{ route('search') }}" method="GET" class="hidden sm:flex items-center relative w-64 md:w-80" role="search">
                        <label for="header-search-desktop" class="sr-only">Cari Berita</label>
                        <input type="text" id="header-search-desktop" name="q" value="{{ request('q') }}" placeholder="Cari berita..." maxlength="100" class="w-full bg-stone-50 border border-stone-300 text-stone-900 text-xs rounded-full pl-9 pr-4 py-2 placeholder-stone-500 focus:outline-hidden focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:bg-white transition-all">
                        <button type="submit" aria-label="Kirim pencarian" class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-500 hover:text-red-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Desktop Navigation Bar -->
        <nav class="border-t border-stone-100 bg-white hidden lg:block relative z-30" aria-label="Navigasi Utama">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-1 py-0.5">
                    <a href="{{ route('home') }}" class="px-3.5 py-1.5 text-sm font-bold border-b-2 transition-colors whitespace-nowrap {{ request()->routeIs('home') ? 'border-red-600 text-red-600' : 'border-transparent text-stone-700 hover:text-red-600 hover:border-stone-300' }}">
                        Beranda
                    </a>

                    @isset($headerCategories)
                        @foreach($headerCategories as $navCat)
                            @php
                                $isActive = request()->routeIs('categories.show') && isset($category) && $category->id === $navCat->id;
                            @endphp
                            <a href="{{ route('categories.show', $navCat) }}" class="px-3.5 py-1.5 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap {{ $isActive ? 'border-red-600 text-red-600' : 'border-transparent text-stone-700 hover:text-red-600 hover:border-stone-300' }}">
                                {{ $navCat->name }}
                            </a>
                        @endforeach
                    @endisset

                    @if(isset($dropdownCategories) && $dropdownCategories->isNotEmpty())
                        @php
                            $isDropdownActive = request()->routeIs('categories.show') && isset($category) && $dropdownCategories->contains('id', $category->id);
                        @endphp
                        <div class="relative inline-block" id="nav-more-dropdown-container">
                            <button
                                type="button"
                                id="nav-more-dropdown-btn"
                                aria-expanded="false"
                                aria-haspopup="true"
                                class="px-3.5 py-1.5 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap inline-flex items-center gap-1 cursor-pointer {{ $isDropdownActive ? 'border-red-600 text-red-600' : 'border-transparent text-stone-700 hover:text-red-600 hover:border-stone-300' }}"
                            >
                                <span>Lainnya</span>
                                <svg class="w-3.5 h-3.5 transition-transform" id="nav-more-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div
                                id="nav-more-dropdown-menu"
                                class="hidden absolute left-0 sm:right-0 sm:left-auto top-full mt-1 w-52 bg-white rounded-xl shadow-xl border border-stone-200 py-2 z-50"
                            >
                                <div class="px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-stone-400 border-b border-stone-100 mb-1">
                                    Kategori Lainnya
                                </div>
                                <div class="max-h-64 overflow-y-auto py-1">
                                    @foreach($dropdownCategories as $dropCat)
                                        @php
                                            $isDropCatActive = request()->routeIs('categories.show') && isset($category) && $category->id === $dropCat->id;
                                        @endphp
                                        <a
                                            href="{{ route('categories.show', $dropCat) }}"
                                            class="block px-3.5 py-2 text-xs font-medium transition-colors {{ $isDropCatActive ? 'bg-red-50 text-red-600 font-bold' : 'text-stone-700 hover:bg-stone-50 hover:text-red-600' }}"
                                        >
                                            {{ $dropCat->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </nav>

        <!-- Mobile Drawer Navigation -->
        <div id="mobile-menu" class="hidden lg:hidden border-t border-stone-200 bg-white px-4 py-4 shadow-lg">
            <!-- Mobile Search Form -->
            <form action="{{ route('search') }}" method="GET" class="relative mb-3 sm:hidden" role="search">
                <label for="header-search-mobile" class="sr-only">Cari Berita</label>
                <input type="text" id="header-search-mobile" name="q" value="{{ request('q') }}" placeholder="Cari berita..." maxlength="100" class="w-full bg-stone-50 border border-stone-300 text-stone-900 text-xs rounded-full pl-9 pr-4 py-2 placeholder-stone-500 focus:outline-hidden focus:border-red-600 focus:ring-1 focus:ring-red-600 focus:bg-white transition-all">
                <button type="submit" aria-label="Kirim pencarian" class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-500 hover:text-red-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </form>

            <div class="flex flex-col space-y-1">
                <a href="{{ route('home') }}" class="px-3 py-2 text-sm font-bold rounded-md {{ request()->routeIs('home') ? 'bg-red-50 text-red-600 font-bold' : 'text-stone-700 hover:bg-stone-100' }}">
                    Beranda
                </a>
                <div class="pt-2 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-stone-400">Kategori Berita</div>
                @isset($mobileCategories)
                    @foreach($mobileCategories as $navCat)
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
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                <!-- Column 1: Brand & About -->
                <div class="md:col-span-4">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 group transition-all" aria-label="JatimNusa">
                        <img src="{{ asset('images/branding/logo-mark.png') }}" alt="" class="h-7 w-7 object-contain transition-transform group-hover:scale-105" />
                        <span class="font-extrabold text-2xl tracking-tight transition-colors text-white group-hover:text-red-400">
                            Jatim<span class="text-red-500 transition-colors group-hover:text-red-400">Nusa</span>
                        </span>
                    </a>
                    <p class="mt-3 text-sm text-stone-400 leading-relaxed max-w-md">
                        Menyajikan kabar terpercaya, analisis mendalam, dan informasi terkini dari seluruh pelosok nusantara dengan standar jurnalistik yang berintegritas dan independen.
                    </p>
                </div>

                <!-- Column 2: Categories Nav -->
                <div class="md:col-span-5">
                    <h4 class="font-bold text-xs text-stone-200 uppercase tracking-wider mb-3">Kanal Kategori</h4>
                    @php
                        $allCats = $footerCategories ?? collect();
                        $totalCats = $allCats->count();
                        $initialLimit = 30;
                        $displayedCats = $allCats->take($initialLimit);
                        $hiddenCats = $allCats->slice($initialLimit);
                        $hasMoreCats = $hiddenCats->isNotEmpty();

                        $displayCount = $displayedCats->count();
                        $numCols = 1;
                        if ($displayCount > 20) {
                            $numCols = 3;
                        } elseif ($displayCount > 10) {
                            $numCols = 2;
                        }

                        $partitionedCols = [];
                        if ($displayCount > 0) {
                            if ($numCols === 1) {
                                $partitionedCols[] = $displayedCats;
                            } else {
                                $perCol = (int) floor($displayCount / $numCols);
                                $remainder = $displayCount % $numCols;
                                $offset = 0;
                                for ($c = 0; $c < $numCols; $c++) {
                                    $take = $perCol + ($c < $remainder ? 1 : 0);
                                    $partitionedCols[] = $displayedCats->slice($offset, $take);
                                    $offset += $take;
                                }
                            }
                        }

                        $hiddenPartitionedCols = [];
                        if ($hasMoreCats) {
                            $hiddenCount = $hiddenCats->count();
                            $perColHidden = (int) floor($hiddenCount / 3);
                            $remainderHidden = $hiddenCount % 3;
                            $offsetHidden = 0;
                            for ($c = 0; $c < 3; $c++) {
                                $takeHidden = $perColHidden + ($c < $remainderHidden ? 1 : 0);
                                $hiddenPartitionedCols[] = $hiddenCats->slice($offsetHidden, $takeHidden);
                                $offsetHidden += $takeHidden;
                            }
                        }
                    @endphp

                    <div class="grid grid-cols-1 {{ $numCols == 2 ? 'sm:grid-cols-2' : '' }} {{ $numCols >= 3 ? 'sm:grid-cols-3' : '' }} gap-x-6 gap-y-2">
                        @foreach($partitionedCols as $colCategories)
                            <ul class="space-y-2 text-sm">
                                @foreach($colCategories as $footerCat)
                                    <li>
                                        <a href="{{ route('categories.show', $footerCat) }}" class="text-stone-400 hover:text-white transition-colors">
                                            {{ $footerCat->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endforeach
                    </div>

                    @if($hasMoreCats)
                        <div id="footer-extra-categories" class="hidden mt-3 pt-3 border-t border-stone-800">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-2">
                                @foreach($hiddenPartitionedCols as $colCategories)
                                    <ul class="space-y-2 text-sm">
                                        @foreach($colCategories as $footerCat)
                                            <li>
                                                <a href="{{ route('categories.show', $footerCat) }}" class="text-stone-400 hover:text-white transition-colors">
                                                    {{ $footerCat->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endforeach
                            </div>
                        </div>

                        <button
                            type="button"
                            id="toggle-all-categories-btn"
                            class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-red-500 hover:text-red-400 transition-colors cursor-pointer"
                            aria-expanded="false"
                        >
                            <span id="toggle-cat-text">Lihat Semua Kategori ({{ $totalCats }})</span>
                            <svg id="toggle-cat-icon" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    @endif
                </div>

                <!-- Column 3: Redaksi & Info -->
                <div class="md:col-span-3">
                    <h4 class="font-bold text-xs text-stone-200 uppercase tracking-wider mb-3">Redaksi & Info</h4>
                    <ul class="space-y-2 text-xs text-stone-400 leading-relaxed">
                        <li>Gedung Pers Merdeka Lt. 4, Jakarta Pusat</li>
                        <li>Email: redaksi@portalberita.test</li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-stone-900 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-stone-500 gap-4">
                <p>&copy; {{ date('Y') }} PT Red Cherry Infinity. Seluruh hak cipta dilindungi undang-undang.</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('home') }}" class="hover:text-stone-400">Beranda</a>
                    <span>&bull;</span>
                    <a href="{{ route('admin.login') }}" class="hover:text-stone-400">Redaksi Login</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Interactive Navigation and Menu Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile Menu Drawer Toggle
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

            // Desktop "Lainnya" Dropdown
            const dropdownContainer = document.getElementById('nav-more-dropdown-container');
            const dropdownBtn = document.getElementById('nav-more-dropdown-btn');
            const dropdownMenu = document.getElementById('nav-more-dropdown-menu');
            const dropdownIcon = document.getElementById('nav-more-icon');

            if (dropdownContainer && dropdownBtn && dropdownMenu) {
                let hideTimeout = null;

                function openDropdown() {
                    if (hideTimeout) clearTimeout(hideTimeout);
                    dropdownMenu.classList.remove('hidden');
                    dropdownBtn.setAttribute('aria-expanded', 'true');
                    if (dropdownIcon) dropdownIcon.classList.add('rotate-180');
                }

                function closeDropdown() {
                    dropdownMenu.classList.add('hidden');
                    dropdownBtn.setAttribute('aria-expanded', 'false');
                    if (dropdownIcon) dropdownIcon.classList.remove('rotate-180');
                }

                dropdownBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const isOpen = !dropdownMenu.classList.contains('hidden');
                    if (isOpen) {
                        closeDropdown();
                    } else {
                        openDropdown();
                    }
                });

                dropdownContainer.addEventListener('mouseenter', function() {
                    openDropdown();
                });

                dropdownContainer.addEventListener('mouseleave', function() {
                    hideTimeout = setTimeout(closeDropdown, 150);
                });

                document.addEventListener('click', function(e) {
                    if (!dropdownContainer.contains(e.target)) {
                        closeDropdown();
                    }
                });

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        closeDropdown();
                    }
                });
            }

            // Footer Categories Toggle
            const toggleCatBtn = document.getElementById('toggle-all-categories-btn');
            const extraCats = document.getElementById('footer-extra-categories');
            const toggleCatText = document.getElementById('toggle-cat-text');
            const toggleCatIcon = document.getElementById('toggle-cat-icon');

            if (toggleCatBtn && extraCats) {
                toggleCatBtn.addEventListener('click', function() {
                    const isHidden = extraCats.classList.toggle('hidden');
                    toggleCatBtn.setAttribute('aria-expanded', !isHidden);
                    if (toggleCatText) {
                        toggleCatText.textContent = isHidden ? 'Lihat Semua Kategori ({{ $totalCats }})' : 'Sembunyikan Sebagian';
                    }
                    if (toggleCatIcon) {
                        toggleCatIcon.classList.toggle('rotate-180', !isHidden);
                    }
                });
            }
        });
    </script>
</body>
</html>
