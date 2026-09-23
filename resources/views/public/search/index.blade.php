@extends('layouts.public')

@section('title', ($query ? 'Hasil Pencarian "' . $query . '"' : 'Pencarian Berita') . ' - ' . config('site.name', 'JatimNusa'))
@section('meta_description', 'Pencarian berita terkini dan terpercaya di ' . config('site.name', 'JatimNusa') . '.')
@section('meta_robots', 'noindex, follow')
@php
    $searchCanonicalParams = [];
    if ($query !== '') {
        $searchCanonicalParams['q'] = $query;
    }
    if ($articles && $articles->currentPage() > 1) {
        $searchCanonicalParams['page'] = $articles->currentPage();
    }
@endphp
@section('canonical_url', route('search', $searchCanonicalParams))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 space-y-8">
    <!-- Breadcrumb -->
    <nav class="flex items-center text-xs text-stone-500 gap-2" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-red-600 transition-colors">Beranda</a>
        <span>/</span>
        <span class="text-stone-900 font-semibold">Pencarian Berita</span>
    </nav>

    <!-- Search Header & Bar -->
    <div class="bg-white rounded-2xl border border-stone-200 p-6 sm:p-8 shadow-xs">
        <div class="max-w-2xl">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2.5 h-6 bg-red-600 rounded-xs"></span>
                <h1 class="text-xs font-bold uppercase tracking-wider text-red-600">Pusat Pencarian</h1>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-stone-950 tracking-tight">
                Cari Berita & Informasi
            </h2>
            <p class="mt-2 text-xs sm:text-sm text-stone-500">
                Temukan liputan jurnalistik berdasarkan kata kunci judul artikel.
            </p>

            <form action="{{ route('search') }}" method="GET" class="mt-5 flex gap-2" role="search">
                <div class="relative flex-1">
                    <label for="search-input-page" class="sr-only">Kata Kunci</label>
                    <input type="text" id="search-input-page" name="q" value="{{ $query }}" placeholder="Ketik kata kunci judul berita..." maxlength="100" class="w-full bg-stone-50 border border-stone-300 text-stone-900 text-sm rounded-xl pl-10 pr-4 py-2.5 focus:outline-hidden focus:ring-2 focus:ring-red-600 focus:bg-white transition-colors">
                    <svg class="w-5 h-5 text-stone-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold text-sm rounded-xl transition-colors shadow-2xs">
                    Cari
                </button>
            </form>
        </div>
    </div>

    <!-- Search Results Content -->
    @if($query === '')
        <!-- Empty Query State -->
        <div class="bg-white rounded-2xl border border-stone-200 p-12 text-center shadow-xs">
            <div class="w-16 h-16 bg-stone-100 text-stone-400 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-stone-900">Masukkan kata kunci untuk mencari berita.</h3>
            <p class="mt-2 text-xs sm:text-sm text-stone-500 max-w-md mx-auto">
                Silakan ketikkan topik, peristiwa, atau nama tokoh pada kolom pencarian di atas untuk menemukan artikel terkait.
            </p>
        </div>
    @elseif($articles && $articles->isEmpty())
        <!-- No Results State -->
        <div class="bg-white rounded-2xl border border-stone-200 p-12 text-center shadow-xs">
            <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-stone-900">Tidak ditemukan berita untuk "{{ $query }}".</h3>
            <p class="mt-2 text-xs sm:text-sm text-stone-500 max-w-md mx-auto">
                Periksa kembali ejaan kata kunci Anda atau coba gunakan kata kunci yang lebih umum.
            </p>
            <div class="mt-6">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-stone-900 text-white text-xs font-semibold hover:bg-stone-800 transition-colors">
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    @else
        <!-- Results List -->
        <div class="space-y-6">
            <div class="flex items-center justify-between border-b border-stone-200 pb-3">
                <p class="text-xs sm:text-sm text-stone-600">
                    Menampilkan hasil pencarian untuk <strong class="text-stone-900">"{{ $query }}"</strong> ({{ $articles->total() }} berita)
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($articles as $article)
                    <article class="group bg-white rounded-xl border border-stone-200 overflow-hidden shadow-xs hover:border-stone-300 hover:shadow-sm transition-all flex flex-col">
                        <a href="{{ route('articles.show', $article) }}" class="block flex-1 flex flex-col">
                            <div class="relative aspect-video w-full overflow-hidden bg-stone-100">
                                @if($article->featured_image)
                                    <img src="{{ Storage::url($article->featured_image) }}" alt="{{ $article->image_alt ?? $article->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full bg-stone-100 flex items-center justify-center text-stone-400">
                                        <svg class="w-10 h-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="absolute top-2 left-2">
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded bg-stone-900/80 text-white backdrop-blur-xs">
                                        {{ $article->category?->name ?? 'Berita' }}
                                    </span>
                                </div>
                            </div>

                            <div class="p-5 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="font-bold text-base sm:text-lg text-stone-900 group-hover:text-red-600 transition-colors line-clamp-2 leading-snug">
                                        {{ $article->title }}
                                    </h3>

                                    @if($article->excerpt)
                                        <p class="mt-2.5 text-xs sm:text-sm text-stone-600 line-clamp-3 leading-relaxed">
                                            {{ $article->excerpt }}
                                        </p>
                                    @endif
                                </div>

                                <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between text-xs text-stone-400">
                                    <span>{{ $article->author?->name ?? 'Redaksi' }}</span>
                                    <time datetime="{{ $article->published_at?->toIso8601String() }}">
                                        {{ $article->publishedAtDisplay('d M Y', false) }}
                                    </time>
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pt-6">
                {{ $articles->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
