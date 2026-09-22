@extends('layouts.public')

@section('title', '404 - Halaman Tidak Ditemukan - ' . config('site.name', 'Portal Berita'))
@section('meta_robots', 'noindex, follow')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-20 text-center">
    <div class="bg-white rounded-3xl border border-stone-200 p-8 sm:p-14 shadow-xs">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-red-50 text-red-600 font-mono font-black text-3xl mb-6">
            404
        </div>

        <h1 class="text-2xl sm:text-4xl font-extrabold text-stone-950 tracking-tight">
            Halaman Tidak Ditemukan
        </h1>

        <p class="mt-3 text-sm sm:text-base text-stone-500 max-w-md mx-auto leading-relaxed">
            Maaf, artikel atau tautan yang Anda tuju tidak tersedia, telah dipindahkan, atau belum dipublikasikan oleh redaksi.
        </p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-stone-950 text-white text-xs sm:text-sm font-semibold hover:bg-stone-800 transition-colors shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Kembali ke Beranda
            </a>

            <a href="{{ route('search') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-stone-100 border border-stone-200 text-stone-700 text-xs sm:text-sm font-semibold hover:bg-stone-200 transition-colors">
                <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Cari Berita
            </a>
        </div>

        @isset($navCategories)
            <div class="mt-12 pt-8 border-t border-stone-100">
                <h2 class="text-xs font-bold uppercase tracking-wider text-stone-400 mb-4">
                    Jelajahi Kanal Berita Pilihan
                </h2>
                <div class="flex flex-wrap justify-center gap-2">
                    @foreach($navCategories->take(6) as $cat)
                        <a href="{{ route('categories.show', $cat) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-stone-50 hover:bg-red-50 text-stone-700 hover:text-red-600 border border-stone-200 hover:border-red-200 transition-all">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endisset
    </div>
</div>
@endsection
