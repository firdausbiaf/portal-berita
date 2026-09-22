@extends('layouts.admin')

@section('title', 'Tag')
@section('page_title', 'Manajemen Tag')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-stone-900">Daftar Tag Berita</h2>
            <p class="text-xs text-stone-500 mt-1">Kelola kata kunci dan label penanda topik berita</p>
        </div>
        <a href="{{ route('admin.tags.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium text-xs transition-colors shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Tag</span>
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-stone-200 shadow-2xs overflow-hidden">
        @if($tags->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-xl bg-stone-100 border border-stone-200 flex items-center justify-center mx-auto text-stone-400 mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                    </svg>
                </div>
                <h4 class="text-sm font-semibold text-stone-800">Belum ada tag</h4>
                <p class="text-xs text-stone-500 mt-1 max-w-sm mx-auto">Tambahkan tag baru untuk memberikan label kata kunci pada artikel berita.</p>
                <div class="mt-4">
                    <a href="{{ route('admin.tags.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors">
                        Tambah Tag
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-stone-700">
                    <thead class="bg-stone-50 text-[11px] font-semibold uppercase tracking-wider text-stone-500 border-b border-stone-200">
                        <tr>
                            <th scope="col" class="py-3 px-6">Nama Tag</th>
                            <th scope="col" class="py-3 px-6">Slug</th>
                            <th scope="col" class="py-3 px-6">Jumlah Berita Terkait</th>
                            <th scope="col" class="py-3 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach($tags as $tag)
                            <tr class="hover:bg-stone-50/70 transition-colors">
                                <td class="py-4 px-6 font-semibold text-stone-900">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="text-stone-400">#</span>
                                        {{ $tag->name }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-mono text-xs text-stone-500">
                                    {{ $tag->slug }}
                                </td>
                                <td class="py-4 px-6 text-xs">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tag->articles_count > 0 ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-stone-100 text-stone-600' }}">
                                        {{ $tag->articles_count }} berita
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.tags.edit', $tag) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg text-stone-700 bg-stone-100 hover:bg-stone-200 hover:text-stone-900 transition-colors border border-stone-200">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.tags.destroy', $tag) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tag #{{ $tag->name }}? Hubungan tag dengan artikel akan dilepas tanpa menghapus artikel.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg text-red-600 bg-red-50 hover:bg-red-100 transition-colors border border-red-200 cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($tags->hasPages())
                <div class="p-4 border-t border-stone-200">
                    {{ $tags->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
