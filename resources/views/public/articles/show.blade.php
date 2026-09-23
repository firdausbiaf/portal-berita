@extends('layouts.public')

@php
    $canonicalUrl = route('articles.show', $article);
    $metaDescription = $article->meta_description;
    $ogImage = $article->featured_image ? url(Storage::url($article->featured_image)) : null;
    $publishedIso = $article->published_at?->toIso8601String();
    $modifiedIso = $article->updated_at?->toIso8601String();
@endphp

@section('title', $article->title . ' - ' . config('site.name', 'JatimNusa'))
@section('meta_description', $metaDescription)
@section('canonical_url', $canonicalUrl)
@section('og_type', 'article')
@section('og_title', $article->title)
@section('og_description', $metaDescription)
@section('og_url', $canonicalUrl)
@if($ogImage)
    @section('og_image', $ogImage)
    @section('twitter_card', 'summary_large_image')
@else
    @section('twitter_card', 'summary')
@endif
@if($publishedIso)
    @section('article_published_time', $publishedIso)
@endif
@if($modifiedIso)
    @section('article_modified_time', $modifiedIso)
@endif
@if($article->category)
    @section('article_section', $article->category->name)
@endif
@section('article_tags')
    @foreach($article->tags as $t)
        <meta property="article:tag" content="{{ $t->name }}">
    @endforeach
@endsection

@section('structured_data')
@php
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => $article->title,
        'datePublished' => $publishedIso,
        'dateModified' => $modifiedIso,
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => $canonicalUrl,
        ],
        'author' => [
            [
                '@type' => 'Person',
                'name' => $article->author?->name ?? 'Redaksi',
            ],
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => config('site.name', 'JatimNusa'),
        ],
        'description' => $metaDescription,
    ];

    if ($ogImage) {
        $jsonLd['image'] = [$ogImage];
    }

    if ($article->category) {
        $jsonLd['articleSection'] = $article->category->name;
    }

    if ($article->tags->isNotEmpty()) {
        $jsonLd['keywords'] = $article->tags->pluck('name')->implode(', ');
    }
@endphp
<script type="application/ld+json">
{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
</script>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
        <!-- MAIN ARTICLE COLUMN (8/12) -->
        <main class="lg:col-span-8 space-y-6">
            <article class="bg-white rounded-2xl border border-stone-200 p-5 sm:p-8 shadow-xs">
                <!-- 1. Breadcrumb -->
                <nav class="flex items-center text-xs text-stone-500 gap-2 flex-wrap mb-4" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="hover:text-red-600 transition-colors">Beranda</a>
                    <span>/</span>
                    @if($article->category)
                        <a href="{{ route('categories.show', $article->category) }}" class="hover:text-red-600 transition-colors font-medium">
                            {{ $article->category->name }}
                        </a>
                        <span>/</span>
                    @endif
                    <span class="text-stone-900 font-semibold truncate max-w-xs sm:max-w-md">{{ $article->title }}</span>
                </nav>

                <!-- 2. Category Label -->
                @if($article->category)
                    <div class="mb-3">
                        <a href="{{ route('categories.show', $article->category) }}" class="inline-block px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-full bg-red-600 text-white hover:bg-red-700 transition-colors">
                            {{ $article->category->name }}
                        </a>
                    </div>
                @endif

                <!-- 3. Headline / Title -->
                <h1 class="text-2xl sm:text-4xl lg:text-4.5xl font-extrabold text-stone-950 tracking-tight leading-tight mb-4">
                    {{ $article->title }}
                </h1>

                <!-- 4. Excerpt / Lead Paragraph -->
                @if($article->excerpt)
                    <div class="mb-5 border-l-4 border-red-600 pl-4 py-1.5 bg-stone-50 rounded-r-lg">
                        <p class="text-base sm:text-lg text-stone-700 font-medium leading-relaxed italic">
                            {{ $article->excerpt }}
                        </p>
                    </div>
                @endif

                <!-- 5. Metadata Bar -->
                <div class="flex flex-wrap items-center justify-between gap-3 text-xs sm:text-sm text-stone-500 py-3.5 border-y border-stone-100 mb-6">
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                        <span class="font-medium text-stone-900">
                            Oleh: <strong class="text-stone-950">{{ $article->author?->name ?? 'Redaksi' }}</strong>
                        </span>
                        <span>&bull;</span>
                        <time datetime="{{ $article->published_at?->toIso8601String() }}">
                            {{ $article->publishedAtDisplay('l, d F Y, H:i') }}
                        </time>
                    </div>

                    <div class="flex items-center gap-1.5 text-stone-500 text-xs">
                        <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <span>{{ number_format($article->view_count) }} kali dibaca</span>
                    </div>
                </div>

                <!-- 6. Share Buttons Bar -->
                @php
                    $encodedUrl = rawurlencode($canonicalUrl);
                    $encodedTitle = rawurlencode($article->title);
                @endphp
                <div class="flex flex-wrap items-center justify-between gap-3 py-3 px-4 bg-stone-50 rounded-xl border border-stone-200/80 mb-6">
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-600">Bagikan Berita:</span>
                    <div class="flex items-center gap-2">
                        <!-- WhatsApp -->
                        <a href="https://api.whatsapp.com/send?text={{ $encodedTitle }}%20{{ $encodedUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Bagikan ke WhatsApp" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition-colors shadow-2xs">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                            </svg>
                            <span>WhatsApp</span>
                        </a>

                        <!-- Facebook -->
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ $encodedUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Bagikan ke Facebook" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition-colors shadow-2xs">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            <span>Facebook</span>
                        </a>

                        <!-- X / Twitter -->
                        <a href="https://twitter.com/intent/tweet?url={{ $encodedUrl }}&text={{ $encodedTitle }}" target="_blank" rel="noopener noreferrer" aria-label="Bagikan ke X" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-stone-900 text-white text-xs font-semibold hover:bg-stone-800 transition-colors shadow-2xs">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                            <span>X</span>
                        </a>

                        <!-- Copy Link -->
                        <button type="button" id="copy-link-btn" data-url="{{ $canonicalUrl }}" aria-label="Salin tautan artikel" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-stone-300 text-stone-700 text-xs font-semibold hover:bg-stone-50 transition-colors shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <span id="copy-link-text">Salin</span>
                        </button>
                    </div>
                </div>

                <!-- 7. Featured Image & Caption -->
                <figure class="mb-8">
                    <div class="rounded-2xl overflow-hidden border border-stone-200 bg-stone-100 aspect-video w-full">
                        @if($article->featured_image)
                            <img src="{{ Storage::url($article->featured_image) }}" alt="{{ $article->image_alt ?? $article->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-stone-100 to-stone-200 flex flex-col items-center justify-center text-stone-400 select-none">
                                <svg class="w-16 h-16 opacity-40 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                </svg>
                                <span class="text-xs font-semibold tracking-wider uppercase text-stone-400">JatimNusa</span>
                            </div>
                        @endif
                    </div>
                    @if($article->image_caption)
                        <figcaption class="text-xs text-stone-500 italic mt-2.5 text-center px-4">
                            Foto: {{ $article->image_caption }}
                        </figcaption>
                    @endif
                </figure>

                <!-- 8. Article Body Content -->
                @if($safeContent)
                    <div class="article-body">
                        {!! $safeContent !!}
                    </div>
                @else
                    <div class="p-6 bg-stone-50 rounded-xl border border-stone-200 text-stone-500 text-sm italic text-center my-6">
                        Konten berita belum tersedia.
                    </div>
                @endif

                <!-- 9. Tags / Topics Section -->
                @if($article->tags->isNotEmpty())
                    <div class="mt-8 pt-6 border-t border-stone-200">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-2 h-4 bg-red-600 rounded-xs"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-stone-700">Topik Terkait</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($article->tags as $tag)
                                <a href="{{ route('tags.show', $tag) }}" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-stone-100 hover:bg-red-50 text-stone-700 hover:text-red-600 border border-stone-200 hover:border-red-200 transition-all">
                                    #{{ $tag->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </article>

            <!-- 10. Related News Section -->
            @if($related->isNotEmpty())
                <section class="bg-white rounded-2xl border border-stone-200 p-5 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-2.5 border-b border-stone-200 pb-3 mb-6">
                        <span class="w-2 h-6 bg-red-600"></span>
                        <h2 class="font-extrabold text-lg sm:text-xl text-stone-950 uppercase tracking-tight">
                            Berita Terkait
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($related as $relArticle)
                            <article class="group border border-stone-200 rounded-xl overflow-hidden hover:border-stone-300 transition-all flex flex-col">
                                <a href="{{ route('articles.show', $relArticle) }}" class="block flex-1 flex flex-col">
                                    <div class="relative aspect-video w-full overflow-hidden bg-stone-100">
                                        @if($relArticle->featured_image)
                                            <img src="{{ Storage::url($relArticle->featured_image) }}" alt="{{ $relArticle->image_alt ?? $relArticle->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        @else
                                            <div class="w-full h-full bg-stone-100 flex items-center justify-center text-stone-400">
                                                <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="absolute top-2 left-2">
                                            <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded bg-stone-900/80 text-white">
                                                {{ $relArticle->category?->name ?? 'Berita' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="p-4 flex-1 flex flex-col justify-between">
                                        <h3 class="font-bold text-sm sm:text-base text-stone-900 group-hover:text-red-600 transition-colors line-clamp-2 leading-snug">
                                            {{ $relArticle->title }}
                                        </h3>
                                        <time class="mt-3 text-[11px] text-stone-400" datetime="{{ $relArticle->published_at?->toIso8601String() }}">
                                            {{ $relArticle->publishedAtDisplay('d M Y, H:i') }}
                                        </time>
                                    </div>
                                </a>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </main>

        <!-- RIGHT SIDEBAR (4/12) -->
        <aside class="lg:col-span-4 space-y-6">
            <!-- 1. Berita Terpopuler -->
            @if($popular->isNotEmpty())
                <div class="bg-white rounded-2xl border border-stone-200 p-5 shadow-xs">
                    <div class="flex items-center gap-2 border-b border-stone-200 pb-3 mb-4">
                        <span class="w-1.5 h-5 bg-red-600 rounded-full"></span>
                        <h2 class="font-extrabold text-base text-stone-900 tracking-tight uppercase">
                            Berita Terpopuler
                        </h2>
                    </div>

                    <div class="divide-y divide-stone-100">
                        @foreach($popular as $index => $popArticle)
                            <article class="group py-3 first:pt-0 last:pb-0 flex items-start gap-3.5">
                                <span class="font-black text-2xl text-stone-300 group-hover:text-red-600 transition-colors font-mono select-none w-7 shrink-0">
                                    {{ sprintf('%02d', $index + 1) }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <span class="text-[10px] font-semibold text-red-600 uppercase tracking-wider block mb-0.5">
                                        {{ $popArticle->category?->name ?? 'Umum' }}
                                    </span>
                                    <h3 class="font-bold text-xs sm:text-sm text-stone-900 group-hover:text-red-600 transition-colors line-clamp-2 leading-snug">
                                        <a href="{{ route('articles.show', $popArticle) }}">
                                            {{ $popArticle->title }}
                                        </a>
                                    </h3>
                                    <div class="mt-1 flex items-center gap-2 text-[10px] text-stone-400">
                                        <time datetime="{{ $popArticle->published_at?->toIso8601String() }}">{{ $popArticle->publishedAtDisplay('d M Y', false) }}</time>
                                        <span>&bull;</span>
                                        <span>{{ number_format($popArticle->view_count) }} views</span>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 2. Berita Terbaru -->
            @if($latest->isNotEmpty())
                <div class="bg-white rounded-2xl border border-stone-200 p-5 shadow-xs">
                    <div class="flex items-center gap-2 border-b border-stone-200 pb-3 mb-4">
                        <span class="w-1.5 h-5 bg-red-600 rounded-full"></span>
                        <h2 class="font-extrabold text-base text-stone-900 tracking-tight uppercase">
                            Berita Terbaru
                        </h2>
                    </div>

                    <div class="space-y-3.5">
                        @foreach($latest as $latestArticle)
                            <article class="group pb-3.5 border-b border-stone-100 last:border-b-0 last:pb-0">
                                <span class="text-[10px] font-bold text-stone-500 uppercase tracking-wider block mb-1">
                                    {{ $latestArticle->category?->name ?? 'Berita' }}
                                </span>
                                <h3 class="font-bold text-xs sm:text-sm text-stone-900 group-hover:text-red-600 transition-colors line-clamp-2 leading-snug">
                                    <a href="{{ route('articles.show', $latestArticle) }}">
                                        {{ $latestArticle->title }}
                                    </a>
                                </h3>
                                <time class="mt-1 block text-[10px] text-stone-400" datetime="{{ $latestArticle->published_at?->toIso8601String() }}">
                                    {{ $latestArticle->publishedAtDisplay('d M Y, H:i') }}
                                </time>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 3. Sedang Trending -->
            @if($trending->isNotEmpty())
                <div class="bg-white rounded-2xl border border-stone-200 p-5 shadow-xs">
                    <div class="flex items-center gap-2 border-b border-stone-200 pb-3 mb-4">
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-600"></span>
                        </span>
                        <h2 class="font-extrabold text-base text-stone-900 tracking-tight uppercase">
                            Sedang Trending
                        </h2>
                    </div>

                    <div class="space-y-3.5">
                        @foreach($trending as $trendArticle)
                            <article class="group pb-3.5 border-b border-stone-100 last:border-b-0 last:pb-0">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-red-50 text-red-600 border border-red-200/60 uppercase">
                                        Trending
                                    </span>
                                    <span class="text-[10px] text-stone-500">
                                        {{ $trendArticle->category?->name ?? 'Umum' }}
                                    </span>
                                </div>
                                <h3 class="font-bold text-xs sm:text-sm text-stone-900 group-hover:text-red-600 transition-colors line-clamp-2 leading-snug">
                                    <a href="{{ route('articles.show', $trendArticle) }}">
                                        {{ $trendArticle->title }}
                                    </a>
                                </h3>
                                <time class="mt-1 block text-[10px] text-stone-400" datetime="{{ $trendArticle->published_at?->toIso8601String() }}">
                                    {{ $trendArticle->publishedAtDisplay('d M Y', false) }}
                                </time>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    </div>
</div>

<!-- Copy Link Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const copyBtn = document.getElementById('copy-link-btn');
        const copyText = document.getElementById('copy-link-text');

        if (copyBtn && copyText) {
            copyBtn.addEventListener('click', async function() {
                const url = copyBtn.getAttribute('data-url');
                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(url);
                    } else {
                        // Fallback for non-https / legacy
                        const textArea = document.createElement('textarea');
                        textArea.value = url;
                        textArea.style.position = 'fixed';
                        textArea.style.left = '-999999px';
                        document.body.appendChild(textArea);
                        textArea.focus();
                        textArea.select();
                        document.execCommand('copy');
                        textArea.remove();
                    }
                    copyText.textContent = 'Disalin!';
                    copyBtn.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
                    setTimeout(function() {
                        copyText.textContent = 'Salin';
                        copyBtn.classList.remove('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
                    }, 2000);
                } catch (err) {
                    copyText.textContent = 'Gagal';
                    setTimeout(function() {
                        copyText.textContent = 'Salin';
                    }, 2000);
                }
            });
        }
    });
</script>
@endsection
