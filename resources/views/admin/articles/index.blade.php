@extends('layouts.admin')

@section('title', 'Berita')
@section('page_title', 'Manajemen Berita')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-stone-900">Daftar Artikel Berita</h2>
            <p class="text-xs text-stone-500 mt-1">Kelola konten berita, publikasi, status featured, dan trending</p>
        </div>
        <a href="{{ route('admin.articles.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium text-xs transition-colors shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tulis Berita Baru</span>
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-2xl border border-stone-200 p-4 shadow-2xs">
        <form method="GET" action="{{ route('admin.articles.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Search Title -->
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari judul berita..."
                    class="w-full pl-9 pr-3 py-2 rounded-lg border border-stone-300 text-xs text-stone-900 placeholder-stone-400 focus:outline-hidden focus:border-red-600 focus:ring-1 focus:ring-red-600 transition-colors"
                />
            </div>

            <!-- Filter Status -->
            <div>
                <select
                    name="status"
                    class="w-full px-3 py-2 rounded-lg border border-stone-300 text-xs text-stone-900 focus:outline-hidden focus:border-red-600 focus:ring-1 focus:ring-red-600 transition-colors bg-white"
                >
                    <option value="">-- Semua Status --</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published (Tayang)</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft (Konsep)</option>
                </select>
            </div>

            <!-- Filter Category -->
            <div>
                <select
                    name="category_id"
                    class="w-full px-3 py-2 rounded-lg border border-stone-300 text-xs text-stone-900 focus:outline-hidden focus:border-red-600 focus:ring-1 focus:ring-red-600 transition-colors bg-white"
                >
                    <option value="">-- Semua Kategori --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
                <button
                    type="submit"
                    class="flex-1 py-2 px-3 rounded-lg bg-stone-900 hover:bg-stone-800 text-white font-medium text-xs transition-colors shadow-xs"
                >
                    Terapkan Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'category_id']))
                    <a
                        href="{{ route('admin.articles.index') }}"
                        class="py-2 px-3 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium text-xs transition-colors border border-stone-200 text-center"
                    >
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-stone-200 shadow-2xs overflow-hidden">
        @if($articles->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-xl bg-stone-100 border border-stone-200 flex items-center justify-center mx-auto text-stone-400 mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                </div>
                <h4 class="text-sm font-semibold text-stone-800">Tidak ada artikel berita ditemukan</h4>
                <p class="text-xs text-stone-500 mt-1 max-w-sm mx-auto">
                    @if(request()->hasAny(['search', 'status', 'category_id']))
                        Tidak ada berita yang cocok dengan filter pencarian Anda. Coba atur ulang filter.
                    @else
                        Belum ada berita yang ditulis. Mulai tulis berita pertama Anda sekarang.
                    @endif
                </p>
                <div class="mt-4">
                    @if(request()->hasAny(['search', 'status', 'category_id']))
                        <a href="{{ route('admin.articles.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-700 transition-colors border border-stone-200">
                            Reset Filter
                        </a>
                    @else
                        <a href="{{ route('admin.articles.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors">
                            Tulis Berita Baru
                        </a>
                    @endif
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-stone-700">
                    <thead class="bg-stone-50 text-[11px] font-semibold uppercase tracking-wider text-stone-500 border-b border-stone-200">
                        <tr>
                            <th scope="col" class="py-3 px-4">Thumbnail & Judul</th>
                            <th scope="col" class="py-3 px-4">Kategori</th>
                            <th scope="col" class="py-3 px-4">Status</th>
                            <th scope="col" class="py-3 px-4">Featured</th>
                            <th scope="col" class="py-3 px-4">Trending</th>
                            <th scope="col" class="py-3 px-4">Views</th>
                            <th scope="col" class="py-3 px-4">Waktu Terbit / Update</th>
                            <th scope="col" class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach($articles as $article)
                            <tr class="hover:bg-stone-50/70 transition-colors">
                                <!-- Thumbnail & Judul -->
                                <td class="py-3.5 px-4 font-medium text-stone-900 max-w-xs sm:max-w-md">
                                    <div class="flex items-center gap-3">
                                        @if($article->featured_image)
                                            <img src="{{ asset('storage/'.$article->featured_image) }}" alt="{{ $article->image_alt ?? $article->title }}" class="w-12 h-12 object-cover rounded-lg border border-stone-200 shrink-0" />
                                        @else
                                            <div class="w-12 h-12 rounded-lg bg-stone-100 border border-stone-200 flex items-center justify-center text-stone-400 shrink-0 text-[11px] font-medium">
                                                No img
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.articles.edit', $article) }}" class="font-bold text-stone-900 hover:text-red-600 transition-colors line-clamp-1 block text-sm">
                                                {{ $article->title }}
                                            </a>
                                            <p class="text-[11px] text-stone-400 font-mono mt-0.5 truncate">/{{ $article->slug }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Kategori -->
                                <td class="py-3.5 px-4 text-xs whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-md bg-stone-100 border border-stone-200 text-stone-700 font-medium">
                                        {{ $article->category->name ?? '-' }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
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

                                <!-- Featured -->
                                <td class="py-3.5 px-4 text-xs whitespace-nowrap">
                                    @if($article->is_featured)
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                            Ya
                                        </span>
                                    @else
                                        <span class="text-stone-400">Tidak</span>
                                    @endif
                                </td>

                                <!-- Trending -->
                                <td class="py-3.5 px-4 text-xs whitespace-nowrap">
                                    @if($article->is_trending)
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            Ya
                                        </span>
                                    @else
                                        <span class="text-stone-400">Tidak</span>
                                    @endif
                                </td>

                                <!-- Views -->
                                <td class="py-3.5 px-4 text-xs font-mono text-stone-600 whitespace-nowrap">
                                    {{ number_format($article->view_count) }}
                                </td>

                                <!-- Published / Updated -->
                                <td class="py-3.5 px-4 text-xs text-stone-500 whitespace-nowrap">
                                    @if($article->published_at)
                                        <div title="{{ $article->published_at }}">Terbit: {{ $article->published_at->format('d/m/Y H:i') }}</div>
                                    @else
                                        <span class="text-stone-400">Belum terbit</span>
                                    @endif
                                    <div class="text-[10px] text-stone-400 mt-0.5">Update: {{ $article->updated_at->diffForHumans() }}</div>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Quick Publish / Unpublish -->
                                        @if($article->status === \App\Enums\ArticleStatus::PUBLISHED)
                                            <form method="POST" action="{{ route('admin.articles.unpublish', $article) }}" class="inline">
                                                @csrf
                                                <button type="submit" title="Jadikan Draft / Tarik publikasi" class="px-2.5 py-1 text-xs font-medium rounded-lg text-amber-700 bg-amber-50 hover:bg-amber-100 transition-colors border border-amber-200 cursor-pointer">
                                                    Unpublish
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.articles.publish', $article) }}" class="inline">
                                                @csrf
                                                <button type="submit" title="Publikasikan sekarang" class="px-2.5 py-1 text-xs font-medium rounded-lg text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition-colors border border-emerald-200 cursor-pointer">
                                                    Publish
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Edit -->
                                        <a href="{{ route('admin.articles.edit', $article) }}" class="p-1.5 text-xs font-medium rounded-lg text-stone-700 bg-stone-100 hover:bg-stone-200 hover:text-stone-900 transition-colors border border-stone-200" title="Edit Berita">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        <!-- Delete -->
                                        <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus artikel ini? File gambar dan tautan tag terkait akan ikut dibersihkan.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-xs font-medium rounded-lg text-red-600 bg-red-50 hover:bg-red-100 transition-colors border border-red-200 cursor-pointer" title="Hapus Berita">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($articles->hasPages())
                <div class="p-4 border-t border-stone-200">
                    {{ $articles->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
