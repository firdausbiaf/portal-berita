@extends('layouts.admin')

@section('title', 'Edit Kategori: ' . $category->name)
@section('page_title', 'Edit Kategori')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-stone-900">Edit Kategori</h2>
            <p class="text-xs text-stone-500 mt-1">Perbarui rincian kategori "{{ $category->name }}"</p>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="text-xs text-stone-500 hover:text-stone-800 transition-colors inline-flex items-center gap-1 font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-stone-200 shadow-2xs p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <label for="name" class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">
                    Nama Kategori <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $category->name) }}"
                    required
                    autofocus
                    class="w-full px-3.5 py-2.5 rounded-lg border @error('name') border-red-500 @else border-stone-300 @enderror text-sm text-stone-900 placeholder-stone-400 focus:outline-hidden focus:border-red-600 focus:ring-1 focus:ring-red-600 transition-colors"
                />
                @error('name')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Slug -->
            <div>
                <label for="slug" class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">
                    Slug URL
                </label>
                <input
                    type="text"
                    id="slug"
                    name="slug"
                    value="{{ old('slug', $category->slug) }}"
                    class="w-full px-3.5 py-2.5 rounded-lg border @error('slug') border-red-500 @else border-stone-300 @enderror text-sm text-stone-900 placeholder-stone-400 focus:outline-hidden focus:border-red-600 focus:ring-1 focus:ring-red-600 transition-colors"
                />
                <p class="text-[11px] text-stone-400 mt-1">Slug digunakan sebagai pengenal URL unik kategori.</p>
                @error('slug')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">
                    Deskripsi
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    class="w-full px-3.5 py-2.5 rounded-lg border @error('description') border-red-500 @else border-stone-300 @enderror text-sm text-stone-900 placeholder-stone-400 focus:outline-hidden focus:border-red-600 focus:ring-1 focus:ring-red-600 transition-colors"
                >{{ old('description', $category->description) }}</textarea>
                @error('description')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Buttons -->
            <div class="pt-3 border-t border-stone-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.categories.index') }}" class="px-4 py-2.5 text-xs font-semibold rounded-lg text-stone-600 hover:text-stone-800 transition-colors">
                    Batal
                </a>
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold text-xs transition-colors shadow-xs cursor-pointer inline-flex items-center gap-1.5"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
