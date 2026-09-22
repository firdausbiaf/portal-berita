@extends('layouts.public')

@section('title', config('site.name', 'Portal Berita') . ' - Jurnalisme Cerdas, Kritis & Terpercaya')
@section('meta_description', config('site.description'))
@section('canonical_url', route('home'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-12">
    @if(! $headline)
        <!-- Empty State when no published articles exist -->
        <div class="bg-white rounded-2xl border border-stone-200 p-12 text-center my-12 shadow-xs">
            <div class="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
            </div>
            <h2 class="text-xl font-bold text-stone-900">Belum ada berita yang dipublikasikan.</h2>
            <p class="mt-2 text-sm text-stone-500 max-w-md mx-auto">
                Redaksi saat ini sedang menyusun dan memverifikasi liputan berita terkini. Silakan periksa kembali beberapa saat lagi.
            </p>
        </div>
    @else
        <!-- TOP SECTION: MAIN HEADLINE & SECONDARY FEATURED + POPULAR SIDEBAR -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left 8 Columns: Featured Area -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Main Headline -->
                <article class="group bg-white rounded-xl border border-stone-200 overflow-hidden shadow-xs hover:border-stone-300 transition-all">
                    <a href="{{ route('articles.show', $headline) }}" class="block">
                        <div class="relative aspect-video sm:aspect-[16/9] w-full overflow-hidden bg-stone-100">
                            @if($headline->featured_image)
                                <img src="{{ Storage::url($headline->featured_image) }}" alt="{{ $headline->image_alt ?? $headline->title }}" class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-300">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-stone-100 to-stone-200 flex flex-col items-center justify-center text-stone-400 select-none">
                                    <svg class="w-16 h-16 opacity-40 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                    </svg>
                                    <span class="text-xs font-semibold tracking-wider uppercase text-stone-400">Headline Berita</span>
                                </div>
                            @endif

                            <div class="absolute top-4 left-4">
                                <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-full bg-red-600 text-white shadow-sm">
                                    {{ $headline->category?->name ?? 'Berita' }}
                                </span>
                            </div>
                        </div>

                        <div class="p-5 sm:p-7">
                            <div class="flex items-center gap-2 text-xs text-stone-500 mb-2.5">
                                <span>{{ $headline->author?->name ?? 'Redaksi' }}</span>
                                <span>&bull;</span>
                                <time datetime="{{ $headline->published_at?->toIso8601String() }}">{{ $headline->publishedAtDisplay('d F Y, H:i') }}</time>
                            </div>

                            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-stone-950 group-hover:text-red-600 transition-colors leading-tight">
                                {{ $headline->title }}
                            </h1>

                            @if($headline->excerpt)
                                <p class="mt-3 text-sm sm:text-base text-stone-600 line-clamp-3 leading-relaxed">
                                    {{ $headline->excerpt }}
                                </p>
                            @endif
                        </div>
                    </a>
                </article>

                <!-- Secondary Featured Cards (up to 4) -->
                @if($secondaryFeatured->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($secondaryFeatured as $secArticle)
                            <article class="group bg-white rounded-xl border border-stone-200 overflow-hidden shadow-xs hover:border-stone-300 transition-all flex flex-col">
                                <a href="{{ route('articles.show', $secArticle) }}" class="block flex-1 flex flex-col">
                                    <div class="relative aspect-video w-full overflow-hidden bg-stone-100">
                                        @if($secArticle->featured_image)
                                            <img src="{{ Storage::url($secArticle->featured_image) }}" alt="{{ $secArticle->image_alt ?? $secArticle->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        @else
                                            <div class="w-full h-full bg-stone-100 flex items-center justify-center text-stone-400">
                                                <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="absolute top-2 left-2">
                                            <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded bg-stone-900/80 text-white backdrop-blur-xs">
                                                {{ $secArticle->category?->name ?? 'Berita' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="p-4 flex-1 flex flex-col justify-between">
                                        <div>
                                            <h2 class="font-bold text-base text-stone-900 group-hover:text-red-600 transition-colors line-clamp-2 leading-snug">
                                                {{ $secArticle->title }}
                                            </h2>
                                            @if($secArticle->excerpt)
                                                <p class="mt-2 text-xs text-stone-500 line-clamp-2 leading-relaxed">
                                                    {{ $secArticle->excerpt }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="mt-3 pt-3 border-t border-stone-100 text-[11px] text-stone-400 flex items-center justify-between">
                                            <span>{{ $secArticle->author?->name ?? 'Redaksi' }}</span>
                                            <time datetime="{{ $secArticle->published_at?->toIso8601String() }}">{{ $secArticle->publishedAtDisplay('d M Y, H:i') }}</time>
                                        </div>
                                    </a>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Right 4 Columns: Popular News Sidebar -->
            <aside class="lg:col-span-4 space-y-6">
                @if($popular->isNotEmpty())
                    <div class="bg-white rounded-xl border border-stone-200 p-5 shadow-xs">
                        <div class="flex items-center gap-2 border-b border-stone-200 pb-3 mb-4">
                            <span class="w-1.5 h-5 bg-red-600 rounded-full"></span>
                            <h2 class="font-extrabold text-base text-stone-900 tracking-tight uppercase">
                                Berita Terpopuler
                            </h2>
                        </div>

                        <div class="divide-y divide-stone-100">
                            @foreach($popular as $index => $popArticle)
                                <article class="group py-3.5 first:pt-0 last:pb-0 flex items-start gap-4">
                                    <span class="font-black text-2xl sm:text-3xl text-stone-300 group-hover:text-red-600 transition-colors font-mono select-none w-8 shrink-0">
                                        {{ sprintf('%02d', $index + 1) }}
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <span class="text-[11px] font-semibold text-red-600 uppercase tracking-wider block mb-1">
                                            {{ $popArticle->category?->name ?? 'Umum' }}
                                        </span>
                                        <h3 class="font-bold text-sm text-stone-900 group-hover:text-red-600 transition-colors line-clamp-2 leading-snug">
                                            <a href="{{ route('articles.show', $popArticle) }}" class="focus:outline-hidden focus:underline">
                                                {{ $popArticle->title }}
                                            </a>
                                        </h3>
                                        <div class="mt-1.5 flex items-center gap-2 text-[11px] text-stone-400">
                                            <time datetime="{{ $popArticle->published_at?->toIso8601String() }}">{{ $popArticle->publishedAtDisplay('d M Y', false) }}</time>
                                            <span>&bull;</span>
                                            <span>{{ number_format($popArticle->view_count) }} dibaca</span>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Portal Info Widget -->
                <div class="bg-stone-900 text-stone-300 rounded-xl p-5 shadow-xs">
                    <div class="flex items-center gap-2 text-xs text-red-400 font-bold uppercase tracking-wider mb-2">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                        Fokus Pemberitaan
                    </div>
                    <h3 class="font-bold text-base text-white">Standar Jurnalistik Akurat</h3>
                    <p class="mt-2 text-xs text-stone-400 leading-relaxed">
                        Setiap artikel melalui proses penyuntingan dan verifikasi fakta sebelum dipublikasikan untuk menjaga integritas ruang informasi publik.
                    </p>
                </div>
            </aside>
        </section>

        <!-- MIDDLE SECTION: TWO-COLUMN MAIN CONTENT (LATEST NEWS 2/3, TRENDING 1/3) -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 pt-4">
            <!-- Left 8 Columns: Latest News List -->
            <div class="lg:col-span-8 space-y-4">
                <div class="flex items-center justify-between border-b-2 border-stone-900 pb-2 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-6 bg-red-600"></span>
                        <h2 class="font-black text-xl text-stone-950 uppercase tracking-tight">
                            Berita Terbaru
                        </h2>
                    </div>
                    <span class="text-xs text-stone-500">Kabar Terkini Nusantara</span>
                </div>

                <div class="space-y-4">
                    @forelse($latest as $latestArticle)
                        <article class="group bg-white rounded-xl border border-stone-200 p-4 sm:p-5 hover:border-stone-300 hover:shadow-xs transition-all flex flex-col sm:flex-row gap-4 sm:gap-5">
                            <div class="relative w-full sm:w-48 md:w-56 aspect-video sm:aspect-[4/3] shrink-0 rounded-lg overflow-hidden bg-stone-100">
                                @if($latestArticle->featured_image)
                                    <img src="{{ Storage::url($latestArticle->featured_image) }}" alt="{{ $latestArticle->image_alt ?? $latestArticle->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full bg-stone-100 flex items-center justify-center text-stone-400">
                                        <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="absolute top-2 left-2 sm:hidden">
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded bg-stone-900/80 text-white">
                                        {{ $latestArticle->category?->name ?? 'Berita' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex-1 flex flex-col justify-between">
                                <div>
                                    <div class="hidden sm:flex items-center gap-2 mb-1.5">
                                        <span class="text-xs font-bold text-red-600 uppercase tracking-wider">
                                            {{ $latestArticle->category?->name ?? 'Berita' }}
                                        </span>
                                    </div>

                                    <h3 class="font-extrabold text-base sm:text-lg text-stone-900 group-hover:text-red-600 transition-colors leading-snug">
                                        <a href="{{ route('articles.show', $latestArticle) }}" class="focus:outline-hidden focus:underline">
                                            {{ $latestArticle->title }}
                                        </a>
                                    </h3>

                                    @if($latestArticle->excerpt)
                                        <p class="mt-2 text-xs sm:text-sm text-stone-600 line-clamp-2 leading-relaxed">
                                            {{ $latestArticle->excerpt }}
                                        </p>
                                    @endif
                                </div>

                                <div class="mt-3 flex items-center gap-2 text-xs text-stone-400">
                                    <span>{{ $latestArticle->author?->name ?? 'Redaksi' }}</span>
                                    <span>&bull;</span>
                                    <time datetime="{{ $latestArticle->published_at?->toIso8601String() }}">{{ $latestArticle->publishedAtDisplay('d M Y, H:i') }}</time>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="py-12 text-center text-stone-500 bg-white rounded-xl border border-stone-200">
                            Belum ada berita terbaru yang dipublikasikan.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right 4 Columns: Trending News Sidebar -->
            <aside class="lg:col-span-4 space-y-6">
                @if($trending->isNotEmpty())
                    <div class="bg-white rounded-xl border border-stone-200 p-5 shadow-xs">
                        <div class="flex items-center gap-2 border-b border-stone-200 pb-3 mb-4">
                            <span class="flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-600"></span>
                            </span>
                            <h2 class="font-extrabold text-base text-stone-900 tracking-tight uppercase">
                                Sedang Trending
                            </h2>
                        </div>

                        <div class="space-y-4">
                            @foreach($trending as $trendArticle)
                                <article class="group pb-4 border-b border-stone-100 last:border-b-0 last:pb-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-red-50 text-red-600 border border-red-200/60 uppercase">
                                            Trending
                                        </span>
                                        <span class="text-[11px] font-semibold text-stone-500">
                                            {{ $trendArticle->category?->name ?? 'Umum' }}
                                        </span>
                                    </div>
                                    <h3 class="font-bold text-sm text-stone-900 group-hover:text-red-600 transition-colors leading-snug">
                                        <a href="{{ route('articles.show', $trendArticle) }}" class="focus:outline-hidden focus:underline">
                                            {{ $trendArticle->title }}
                                        </a>
                                    </h3>
                                    <time class="mt-1.5 block text-[11px] text-stone-400" datetime="{{ $trendArticle->published_at?->toIso8601String() }}">
                                        {{ $trendArticle->publishedAtDisplay('d M Y, H:i') }}
                                    </time>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </section>

        <!-- BOTTOM SECTION: CATEGORY SECTIONS -->
        @if($categorySections->isNotEmpty())
            <section class="space-y-10 pt-6">
                @foreach($categorySections as $secCategory)
                    @if($secCategory->articles->isNotEmpty())
                        <div class="border-t border-stone-200 pt-8">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2.5 h-6 bg-red-600 rounded-xs"></span>
                                    <h2 class="font-black text-xl sm:text-2xl text-stone-950 uppercase tracking-tight">
                                        {{ $secCategory->name }}
                                    </h2>
                                </div>
                                <a href="{{ route('categories.show', $secCategory) }}" class="inline-flex items-center gap-1 text-xs font-bold text-red-600 hover:text-red-700 transition-colors">
                                    Berita Lainnya
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                                @foreach($secCategory->articles as $catArticle)
                                    <article class="group bg-white rounded-xl border border-stone-200 overflow-hidden shadow-xs hover:border-stone-300 transition-all flex flex-col">
                                        <a href="{{ route('articles.show', $catArticle) }}" class="block flex-1 flex flex-col">
                                            <div class="relative aspect-video w-full overflow-hidden bg-stone-100">
                                                @if($catArticle->featured_image)
                                                    <img src="{{ Storage::url($catArticle->featured_image) }}" alt="{{ $catArticle->image_alt ?? $catArticle->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                                @else
                                                    <div class="w-full h-full bg-stone-100 flex items-center justify-center text-stone-400">
                                                        <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="p-4 flex-1 flex flex-col justify-between">
                                                <h3 class="font-bold text-sm text-stone-900 group-hover:text-red-600 transition-colors line-clamp-2 leading-snug">
                                                    {{ $catArticle->title }}
                                                </h3>
                                                <div class="mt-3 text-[11px] text-stone-400">
                                                    <time datetime="{{ $catArticle->published_at?->toIso8601String() }}">{{ $catArticle->publishedAtDisplay('d M Y', false) }}</time>
                                                </div>
                                            </div>
                                        </a>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </section>
        @endif
    @endif
</div>
@endsection
