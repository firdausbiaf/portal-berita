@extends('layouts.public')

@section('title', $article->title . ' - ' . config('app.name', 'Portal Berita'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 space-y-8">
    <!-- Breadcrumb -->
    <nav class="flex items-center text-xs text-stone-500 gap-2 flex-wrap" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-red-600 transition-colors">Beranda</a>
        <span>/</span>
        @if($article->category)
            <a href="{{ route('categories.show', $article->category) }}" class="hover:text-red-600 transition-colors">
                {{ $article->category->name }}
            </a>
            <span>/</span>
        @endif
        <span class="text-stone-900 font-semibold truncate max-w-xs sm:max-w-md">{{ $article->title }}</span>
    </nav>

    <!-- Article Header -->
    <header class="space-y-4">
        @if($article->category)
            <div>
                <a href="{{ route('categories.show', $article->category) }}" class="inline-block px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-full bg-red-600 text-white hover:bg-red-700 transition-colors">
                    {{ $article->category->name }}
                </a>
            </div>
        @endif

        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold text-stone-950 tracking-tight leading-tight">
            {{ $article->title }}
        </h1>

        <!-- Metadata -->
        <div class="flex flex-wrap items-center gap-y-2 gap-x-4 text-xs sm:text-sm text-stone-500 pt-2 border-b border-stone-200 pb-4">
            <div class="flex items-center gap-1.5 font-medium text-stone-800">
                <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Penulis: {{ $article->author?->name ?? 'Redaksi' }}</span>
            </div>
            <span>&bull;</span>
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <time datetime="{{ $article->published_at?->toIso8601String() }}">
                    {{ $article->published_at?->translatedFormat('l, d F Y - H:i') }} WIB
                </time>
            </div>
        </div>
    </header>

    <!-- Featured Image -->
    @if($article->featured_image)
        <figure class="space-y-2">
            <div class="rounded-2xl overflow-hidden border border-stone-200 bg-stone-100 aspect-video w-full">
                <img src="{{ Storage::url($article->featured_image) }}" alt="{{ $article->image_alt ?? $article->title }}" class="w-full h-full object-cover">
            </div>
            @if($article->image_caption)
                <figcaption class="text-xs text-stone-500 italic text-center px-4">
                    Foto: {{ $article->image_caption }}
                </figcaption>
            @endif
        </figure>
    @endif

    <!-- Excerpt / Lead Paragraph -->
    @if($article->excerpt)
        <div class="border-l-4 border-red-600 pl-5 py-2 bg-stone-100/60 rounded-r-xl">
            <p class="text-base sm:text-lg text-stone-800 font-medium leading-relaxed">
                {{ $article->excerpt }}
            </p>
        </div>
    @endif

    <!-- Minimal Stage Bridge Notice -->
    <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-xs flex items-start gap-3.5">
        <svg class="w-5 h-5 text-stone-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="space-y-1">
            <h2 class="text-sm font-bold text-stone-900">Arsip Berita Terverifikasi</h2>
            <p class="text-xs sm:text-sm text-stone-500 leading-relaxed">
                Halaman ini merupakan pratinjau ringkas artikel terpublikasi. Liputan lengkap dan fitur interaksi pembaca akan segera hadir pada pembaharuan rilis berikutnya.
            </p>
        </div>
    </div>

    <!-- Navigation Action -->
    <div class="pt-6 border-t border-stone-200 flex items-center justify-between">
        @if($article->category)
            <a href="{{ route('categories.show', $article->category) }}" class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-stone-700 hover:text-red-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Kanal {{ $article->category->name }}
            </a>
        @else
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-stone-700 hover:text-red-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Beranda
            </a>
        @endif

        <a href="{{ route('home') }}" class="text-xs sm:text-sm font-semibold text-red-600 hover:text-red-700 transition-colors">
            Berita Lainnya &rarr;
        </a>
    </div>
</div>
@endsection
