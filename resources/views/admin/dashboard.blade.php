@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard Administrator')

@section('content')
<div class="space-y-8">
    <!-- Welcome Header Card -->
    <div class="bg-white rounded-2xl border border-stone-200 p-6 sm:p-8 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Portal Berita CMS - Tahap 2 Aktif
                </span>
                <h2 class="text-2xl font-bold text-stone-950 tracking-tight">
                    Selamat datang kembali, {{ auth()->user()->name }}
                </h2>
                <p class="text-stone-600 text-sm mt-1">
                    Kelola berita, kategori, tag, dan publikasi portal berita dengan mudah dari panel ini.
                </p>
            </div>

            <div class="shrink-0 flex items-center gap-3">
                <a href="{{ route('admin.articles.create') }}" class="px-4 py-2.5 text-xs font-semibold rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors shadow-xs inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tulis Berita Baru</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Metrics Cards Grid -->
    <div>
        <h3 class="text-sm font-semibold uppercase tracking-wider text-stone-500 mb-3">Ringkasan Konten</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-4">
            <!-- Total Berita -->
            <div class="bg-white rounded-xl border border-stone-200 p-4 shadow-2xs hover:border-stone-300 transition-colors">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-stone-400 block">Total Berita</span>
                <p class="text-2xl font-bold text-stone-900 mt-1">{{ number_format($stats['total_articles']) }}</p>
                <span class="text-[11px] text-stone-500 mt-1 block">Semua artikel</span>
            </div>

            <!-- Published -->
            <div class="bg-white rounded-xl border border-stone-200 p-4 shadow-2xs hover:border-stone-300 transition-colors">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-emerald-600 block">Published</span>
                <p class="text-2xl font-bold text-emerald-700 mt-1">{{ number_format($stats['published_articles']) }}</p>
                <span class="text-[11px] text-emerald-600 mt-1 block">Tayang di portal</span>
            </div>

            <!-- Draft -->
            <div class="bg-white rounded-xl border border-stone-200 p-4 shadow-2xs hover:border-stone-300 transition-colors">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-amber-600 block">Draft</span>
                <p class="text-2xl font-bold text-amber-700 mt-1">{{ number_format($stats['draft_articles']) }}</p>
                <span class="text-[11px] text-amber-600 mt-1 block">Belum dipublish</span>
            </div>

            <!-- Featured -->
            <div class="bg-white rounded-xl border border-stone-200 p-4 shadow-2xs hover:border-stone-300 transition-colors">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-purple-600 block">Featured</span>
                <p class="text-2xl font-bold text-purple-700 mt-1">{{ number_format($stats['featured_articles']) }}</p>
                <span class="text-[11px] text-purple-600 mt-1 block">Sorotan utama</span>
            </div>

            <!-- Trending -->
            <div class="bg-white rounded-xl border border-stone-200 p-4 shadow-2xs hover:border-stone-300 transition-colors">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-rose-600 block">Trending</span>
                <p class="text-2xl font-bold text-rose-700 mt-1">{{ number_format($stats['trending_articles']) }}</p>
                <span class="text-[11px] text-rose-600 mt-1 block">Topik hangat</span>
            </div>

            <!-- Kategori -->
            <div class="bg-white rounded-xl border border-stone-200 p-4 shadow-2xs hover:border-stone-300 transition-colors">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-stone-400 block">Kategori</span>
                <p class="text-2xl font-bold text-stone-900 mt-1">{{ number_format($stats['total_categories']) }}</p>
                <span class="text-[11px] text-stone-500 mt-1 block">Rubrik aktif</span>
            </div>

            <!-- Tag -->
            <div class="bg-white rounded-xl border border-stone-200 p-4 shadow-2xs hover:border-stone-300 transition-colors">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-stone-400 block">Tag</span>
                <p class="text-2xl font-bold text-stone-900 mt-1">{{ number_format($stats['total_tags']) }}</p>
                <span class="text-[11px] text-stone-500 mt-1 block">Label topik</span>
            </div>
        </div>
    </div>

    <!-- Recent Managed Articles Table -->
    <div class="bg-white rounded-2xl border border-stone-200 shadow-2xs overflow-hidden">
        <div class="p-6 border-b border-stone-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-stone-900">Berita Terbaru Dikelola</h3>
                <p class="text-xs text-stone-500 mt-0.5">5 artikel terakhir yang baru dibuat atau diperbarui</p>
            </div>
            <a href="{{ route('admin.articles.index') }}" class="text-xs font-semibold text-red-600 hover:text-red-700 inline-flex items-center gap-1 transition-colors">
                <span>Lihat Semua Berita</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>

        @if($recentArticles->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-xl bg-stone-100 border border-stone-200 flex items-center justify-center mx-auto text-stone-400 mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                </div>
                <h4 class="text-sm font-semibold text-stone-800">Belum ada berita yang dikelola</h4>
                <p class="text-xs text-stone-500 mt-1 max-w-sm mx-auto">Mulai tambahkan artikel berita pertama Anda untuk mengisi konten portal.</p>
                <div class="mt-4">
                    <a href="{{ route('admin.articles.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Berita Baru
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-stone-700">
                    <thead class="bg-stone-50 text-[11px] font-semibold uppercase tracking-wider text-stone-500 border-b border-stone-200">
                        <tr>
                            <th scope="col" class="py-3 px-6">Judul Berita</th>
                            <th scope="col" class="py-3 px-6">Kategori</th>
                            <th scope="col" class="py-3 px-6">Status</th>
                            <th scope="col" class="py-3 px-6">Terakhir Diperbarui</th>
                            <th scope="col" class="py-3 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach($recentArticles as $article)
                            <tr class="hover:bg-stone-50/70 transition-colors">
                                <td class="py-3.5 px-6 font-medium text-stone-900">
                                    <div class="flex items-center gap-2 max-w-md">
                                        @if($article->featured_image)
                                            <img src="{{ asset('storage/'.$article->featured_image) }}" alt="{{ $article->image_alt ?? $article->title }}" class="w-10 h-10 object-cover rounded-md border border-stone-200 shrink-0" />
                                        @else
                                            <div class="w-10 h-10 rounded-md bg-stone-100 border border-stone-200 flex items-center justify-center text-stone-400 shrink-0 text-xs">
                                                No img
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.articles.edit', $article) }}" class="font-semibold text-stone-900 hover:text-red-600 transition-colors truncate block">
                                                {{ $article->title }}
                                            </a>
                                            <div class="flex items-center gap-2 text-[11px] text-stone-400 mt-0.5">
                                                @if($article->is_featured)
                                                    <span class="inline-flex items-center text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded text-[10px] font-medium border border-purple-200">Featured</span>
                                                @endif
                                                @if($article->is_trending)
                                                    <span class="inline-flex items-center text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded text-[10px] font-medium border border-rose-200">Trending</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-6 text-xs text-stone-600">
                                    <span class="px-2.5 py-1 rounded-md bg-stone-100 border border-stone-200 text-stone-700 font-medium">
                                        {{ $article->category->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-6">
                                    @if($article->status === \App\Enums\ArticleStatus::PUBLISHED)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Published
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Draft
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-6 text-xs text-stone-500 whitespace-nowrap">
                                    {{ $article->updated_at->diffForHumans() }}
                                </td>
                                <td class="py-3.5 px-6 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.articles.edit', $article) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg text-stone-700 bg-stone-100 hover:bg-stone-200 hover:text-stone-900 transition-colors border border-stone-200">
                                        <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
