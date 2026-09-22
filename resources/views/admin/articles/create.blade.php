@extends('layouts.admin')

@section('title', 'Tulis Berita Baru')
@section('page_title', 'Tulis Berita Baru')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-stone-900">Tulis Artikel Berita Baru</h2>
            <p class="text-xs text-stone-500 mt-1">Lengkapi informasi konten, kategori, featured image, dan pengaturan publikasi</p>
        </div>
        <a href="{{ route('admin.articles.index') }}" class="text-xs text-stone-500 hover:text-stone-800 transition-colors inline-flex items-center gap-1 font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar Berita
        </a>
    </div>

    <!-- Article Form -->
    <form method="POST" action="{{ route('admin.articles.store') }}" enctype="multipart/form-data" id="article-form">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- MAIN COLUMN (Content & Media) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Title & Slug Card -->
                <div class="bg-white rounded-2xl border border-stone-200 shadow-2xs p-6 space-y-4">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">
                            Judul Berita <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title') }}"
                            required
                            autofocus
                            placeholder="Ketik judul artikel berita yang menarik dan informatif..."
                            class="w-full px-3.5 py-2.5 rounded-lg border @error('title') border-red-500 @else border-stone-300 @enderror text-base font-semibold text-stone-900 placeholder-stone-400 focus:outline-hidden focus:border-red-600 focus:ring-1 focus:ring-red-600 transition-colors"
                        />
                        @error('title')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Slug -->
                    <div>
                        <label for="slug" class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">
                            Slug URL (Opsional)
                        </label>
                        <div class="flex rounded-lg border @error('slug') border-red-500 @else border-stone-300 @enderror overflow-hidden focus-within:border-red-600 focus-within:ring-1 focus-within:ring-red-600">
                            <span class="px-3 py-2 bg-stone-50 text-stone-400 text-xs border-r border-stone-200 select-none flex items-center">
                                /berita/
                            </span>
                            <input
                                type="text"
                                id="slug"
                                name="slug"
                                value="{{ old('slug') }}"
                                placeholder="otomatis-dibuat-dari-judul-jika-kosong"
                                class="w-full px-3 py-2 text-xs font-mono text-stone-900 placeholder-stone-400 focus:outline-hidden"
                            />
                        </div>
                        <p class="text-[11px] text-stone-400 mt-1">Jika dikosongkan, sistem akan meng-generate slug secara otomatis.</p>
                        @error('slug')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Featured Image Card -->
                <div class="bg-white rounded-2xl border border-stone-200 shadow-2xs p-6 space-y-4">
                    <h3 class="text-sm font-bold text-stone-900 border-b border-stone-100 pb-2">Featured Image (Foto Utama)</h3>

                    <div>
                        <label for="featured_image" class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">
                            Unggah Gambar Berita
                        </label>
                        <input
                            type="file"
                            id="featured_image"
                            name="featured_image"
                            accept="image/png, image/jpeg, image/jpg, image/webp"
                            onchange="previewFeaturedImage(this)"
                            class="block w-full text-xs text-stone-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-stone-100 file:text-stone-700 hover:file:bg-stone-200 cursor-pointer"
                        />
                        <p class="text-[11px] text-stone-400 mt-1">Format didukung: JPG, PNG, WEBP. Maksimal ukuran 5 MB.</p>
                        @error('featured_image')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Image Preview Container -->
                    <div id="image-preview-container" class="hidden">
                        <p class="text-xs text-stone-500 font-medium mb-1">Pratinjau Gambar:</p>
                        <img id="image-preview" src="#" alt="Preview" class="w-full max-h-64 object-cover rounded-lg border border-stone-200" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <!-- Caption -->
                        <div>
                            <label for="image_caption" class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">
                                Keterangan Gambar (Caption)
                            </label>
                            <input
                                type="text"
                                id="image_caption"
                                name="image_caption"
                                value="{{ old('image_caption') }}"
                                placeholder="Contoh: Suasana pelantikan kabinet di Istana Negara"
                                class="w-full px-3 py-2 rounded-lg border border-stone-300 text-xs text-stone-900 placeholder-stone-400 focus:outline-hidden focus:border-red-600 focus:ring-1 focus:ring-red-600"
                            />
                        </div>

                        <!-- Alt Text -->
                        <div>
                            <label for="image_alt" class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">
                                Alt Text (Aksesibilitas & SEO)
                            </label>
                            <input
                                type="text"
                                id="image_alt"
                                name="image_alt"
                                value="{{ old('image_alt') }}"
                                placeholder="Deskripsi gambar untuk pembaca tuna netra"
                                class="w-full px-3 py-2 rounded-lg border border-stone-300 text-xs text-stone-900 placeholder-stone-400 focus:outline-hidden focus:border-red-600 focus:ring-1 focus:ring-red-600"
                            />
                        </div>
                    </div>
                </div>

                <!-- Excerpt Card -->
                <div class="bg-white rounded-2xl border border-stone-200 shadow-2xs p-6 space-y-2">
                    <label for="excerpt" class="block text-xs font-semibold text-stone-700 uppercase tracking-wider">
                        Ringkasan Singkat (Excerpt)
                    </label>
                    <textarea
                        id="excerpt"
                        name="excerpt"
                        rows="3"
                        placeholder="Tuliskan 1-2 kalimat ringkasan berita sebagai teaser pada kartu artikel di portal..."
                        class="w-full px-3.5 py-2.5 rounded-lg border border-stone-300 text-xs text-stone-900 placeholder-stone-400 focus:outline-hidden focus:border-red-600 focus:ring-1 focus:ring-red-600"
                    >{{ old('excerpt') }}</textarea>
                    <p class="text-[11px] text-stone-400">Bersifat opsional. Jika dikosongkan, tampilan portal dapat menggunakan cuplikan isi berita.</p>
                </div>

                <!-- Content Body Editor Card -->
                <div class="bg-white rounded-2xl border border-stone-200 shadow-2xs p-6 space-y-3">
                    <div class="flex items-center justify-between border-b border-stone-100 pb-2">
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider">
                            Isi Berita Lengkap
                        </label>
                        <span class="text-[11px] text-stone-400">Editor Ringan & Terformat</span>
                    </div>

                    <!-- Formatting Toolbar -->
                    <div class="flex flex-wrap items-center gap-1 p-2 rounded-lg bg-stone-50 border border-stone-200">
                        <button type="button" onclick="execCmd('formatBlock', '<p>')" class="px-2 py-1 text-xs rounded hover:bg-stone-200 font-semibold text-stone-700" title="Paragraf">P</button>
                        <button type="button" onclick="execCmd('formatBlock', '<h2>')" class="px-2 py-1 text-xs rounded hover:bg-stone-200 font-bold text-stone-800" title="Heading 2">H2</button>
                        <button type="button" onclick="execCmd('formatBlock', '<h3>')" class="px-2 py-1 text-xs rounded hover:bg-stone-200 font-bold text-stone-800" title="Heading 3">H3</button>
                        <span class="h-4 w-px bg-stone-300 mx-1"></span>
                        <button type="button" onclick="execCmd('bold')" class="px-2.5 py-1 text-xs rounded hover:bg-stone-200 font-bold text-stone-800" title="Tebal (Ctrl+B)">B</button>
                        <button type="button" onclick="execCmd('italic')" class="px-2.5 py-1 text-xs rounded hover:bg-stone-200 italic font-semibold text-stone-800" title="Miring (Ctrl+I)">I</button>
                        <button type="button" onclick="execCmd('underline')" class="px-2.5 py-1 text-xs rounded hover:bg-stone-200 underline font-semibold text-stone-800" title="Garis Bawah (Ctrl+U)">U</button>
                        <span class="h-4 w-px bg-stone-300 mx-1"></span>
                        <button type="button" onclick="execCmd('insertUnorderedList')" class="px-2 py-1 text-xs rounded hover:bg-stone-200 text-stone-700" title="Daftar Poin (Bullets)">• List</button>
                        <button type="button" onclick="execCmd('insertOrderedList')" class="px-2 py-1 text-xs rounded hover:bg-stone-200 text-stone-700" title="Daftar Berurutan (Numbers)">1. List</button>
                        <button type="button" onclick="execCmd('formatBlock', '<blockquote>')" class="px-2 py-1 text-xs rounded hover:bg-stone-200 text-stone-700" title="Kutipan">" Quote</button>
                        <span class="h-4 w-px bg-stone-300 mx-1"></span>
                        <button type="button" onclick="insertLink()" class="px-2 py-1 text-xs rounded hover:bg-stone-200 text-stone-700" title="Sisipkan Link">🔗 Link</button>
                        <button type="button" onclick="execCmd('unlink')" class="px-2 py-1 text-xs rounded hover:bg-stone-200 text-stone-700" title="Hapus Link">Unlink</button>
                    </div>

                    <!-- Editable Body Area -->
                    <div
                        id="editor-content"
                        contenteditable="true"
                        class="min-h-[320px] p-4 rounded-xl border border-stone-300 focus:outline-hidden focus:border-red-600 focus:ring-1 focus:ring-red-600 text-stone-900 text-sm leading-relaxed prose prose-stone max-w-none"
                    >{!! old('content', '<p>Tuliskan naskah berita di sini...</p>') !!}</div>

                    <!-- Hidden input for form submission -->
                    <input type="hidden" name="content" id="hidden-content" />

                    @error('content')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- SIDE PANEL (Publish Settings & Taxonomies) -->
            <div class="space-y-6">
                <!-- Action & Status Card -->
                <div class="bg-white rounded-2xl border border-stone-200 shadow-2xs p-6 space-y-4">
                    <h3 class="text-sm font-bold text-stone-900 border-b border-stone-100 pb-2">Publikasi</h3>

                    <p class="text-xs text-stone-500 leading-relaxed">
                        Pilih untuk menyimpan sebagai draf konsep atau langsung menerbitkan ke pembaca portal.
                    </p>

                    <div class="space-y-2 pt-2">
                        <button
                            type="submit"
                            name="action"
                            value="publish"
                            class="w-full py-2.5 px-4 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold text-xs transition-colors shadow-xs flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Publikasikan Sekarang
                        </button>

                        <button
                            type="submit"
                            name="action"
                            value="draft"
                            class="w-full py-2.5 px-4 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-800 font-semibold text-xs transition-colors border border-stone-200 flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            Simpan sebagai Draft
                        </button>
                    </div>
                </div>

                <!-- Category Selection Card -->
                <div class="bg-white rounded-2xl border border-stone-200 shadow-2xs p-6 space-y-3">
                    <h3 class="text-sm font-bold text-stone-900 border-b border-stone-100 pb-2">
                        Kategori Utama <span class="text-red-500">*</span>
                    </h3>

                    <div>
                        <select
                            name="category_id"
                            id="category_id"
                            required
                            class="w-full px-3 py-2.5 rounded-lg border @error('category_id') border-red-500 @else border-stone-300 @enderror text-xs text-stone-900 focus:outline-hidden focus:border-red-600 focus:ring-1 focus:ring-red-600 bg-white"
                        >
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Featured & Trending Toggles Card -->
                <div class="bg-white rounded-2xl border border-stone-200 shadow-2xs p-6 space-y-4">
                    <h3 class="text-sm font-bold text-stone-900 border-b border-stone-100 pb-2">Status Khusus</h3>

                    <!-- Featured Checkbox -->
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            name="is_featured"
                            value="1"
                            {{ old('is_featured') ? 'checked' : '' }}
                            class="w-4 h-4 mt-0.5 rounded text-purple-600 border-stone-300 focus:ring-purple-500"
                        />
                        <div>
                            <span class="text-xs font-semibold text-stone-900 block">Jadikan Berita Featured</span>
                            <span class="text-[11px] text-stone-500 block">Artikel disorot sebagai headline atau pilihan redaksi di beranda.</span>
                        </div>
                    </label>

                    <!-- Trending Checkbox -->
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            name="is_trending"
                            value="1"
                            {{ old('is_trending') ? 'checked' : '' }}
                            class="w-4 h-4 mt-0.5 rounded text-rose-600 border-stone-300 focus:ring-rose-500"
                        />
                        <div>
                            <span class="text-xs font-semibold text-stone-900 block">Jadikan Berita Trending</span>
                            <span class="text-[11px] text-stone-500 block">Artikel ditandai sedang ramai dibicarakan publik.</span>
                        </div>
                    </label>
                </div>

                <!-- Tag Multi-select Card -->
                <div class="bg-white rounded-2xl border border-stone-200 shadow-2xs p-6 space-y-3">
                    <h3 class="text-sm font-bold text-stone-900 border-b border-stone-100 pb-2">Tag Terkait</h3>
                    <p class="text-[11px] text-stone-500">Pilih satu atau lebih tag yang relevan:</p>

                    <div class="max-h-52 overflow-y-auto space-y-1.5 pr-1 scrollbar-thin">
                        @forelse($tags as $tag)
                            <label class="flex items-center gap-2 p-1.5 rounded-md hover:bg-stone-50 cursor-pointer text-xs">
                                <input
                                    type="checkbox"
                                    name="tags[]"
                                    value="{{ $tag->id }}"
                                    {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}
                                    class="w-4 h-4 rounded text-red-600 border-stone-300 focus:ring-red-500"
                                />
                                <span class="text-stone-800">#{{ $tag->name }}</span>
                            </label>
                        @empty
                            <p class="text-xs text-stone-400 py-2">Belum ada tag tersedia.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function previewFeaturedImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image-preview').src = e.target.result;
                document.getElementById('image-preview-container').classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function execCmd(command, value = null) {
        document.execCommand(command, false, value);
        document.getElementById('editor-content').focus();
    }

    function insertLink() {
        const url = prompt('Masukkan tautan URL: (contoh: https://example.com)');
        if (url) {
            execCmd('createLink', url);
        }
    }

    document.getElementById('article-form').addEventListener('submit', function() {
        const editor = document.getElementById('editor-content');
        document.getElementById('hidden-content').value = editor.innerHTML;
    });
</script>
@endsection
