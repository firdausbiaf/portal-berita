@extends('layouts.public')

@section('title', $category->name . ' - ' . config('app.name', 'Portal Berita'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-8">
    <!-- Breadcrumb -->
    <nav class="flex items-center text-xs text-stone-500 gap-2" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-red-600 transition-colors">Beranda</a>
        <span>/</span>
        <span class="text-stone-400">Kategori</span>
        <span>/</span>
        <span class="text-stone-900 font-semibold">{{ $category->name }}</span>
    </nav>

    <!-- Category Header Banner -->
    <div class="bg-white rounded-2xl border border-stone-200 p-6 sm:p-8 shadow-xs">
        <div class="flex items-center gap-2 mb-2">
            <span class="w-2.5 h-6 bg-red-600 rounded-xs"></span>
            <span class="text-xs font-bold uppercase tracking-wider text-red-600">Kanal Berita</span>
        </div>
        <h1 class="text-2xl sm:text-4xl font-extrabold text-stone-950 tracking-tight">
            {{ $category->name }}
        </h1>
        @if($category->description)
            <p class="mt-2 text-sm sm:text-base text-stone-600 max-w-3xl leading-relaxed">
                {{ $category->description }}
            </p>
        @endif
        <div class="mt-4 pt-4 border-t border-stone-100 flex items-center gap-4 text-xs text-stone-500">
            <span>Menampilkan berita terverifikasi dalam kategori {{ $category->name }}</span>
        </div>
    </div>

    <!-- Article List / Grid -->
    @if($articles->isEmpty())
        <div class="bg-white rounded-2xl border border-stone-200 p-12 text-center shadow-xs">
            <div class="w-16 h-16 bg-stone-100 text-stone-400 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
            </div>
            <h2 class="text-lg font-bold text-stone-900">Belum ada berita pada kategori ini.</h2>
            <p class="mt-2 text-xs sm:text-sm text-stone-500 max-w-md mx-auto">
                Kanal {{ $category->name }} belum memiliki artikel terpublikasi saat ini. Silakan kunjungi kanal lainnya atau kembali ke beranda.
            </p>
            <div class="mt-6">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-stone-900 text-white text-xs font-semibold hover:bg-stone-800 transition-colors">
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    @else
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
                        </div>

                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <h2 class="font-bold text-base sm:text-lg text-stone-900 group-hover:text-red-600 transition-colors line-clamp-2 leading-snug">
                                    {{ $article->title }}
                                </h2>

                                @if($article->excerpt)
                                    <p class="mt-2.5 text-xs sm:text-sm text-stone-600 line-clamp-3 leading-relaxed">
                                        {{ $article->excerpt }}
                                    </p>
                                @endif
                            </div>

                            <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between text-xs text-stone-400">
                                <span>{{ $article->author?->name ?? 'Redaksi' }}</span>
                                <time datetime="{{ $article->published_at?->toIso8601String() }}">{{ $article->published_at?->translatedFormat('d M Y') }}</time>
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
    @endif
</div>
@endsection
