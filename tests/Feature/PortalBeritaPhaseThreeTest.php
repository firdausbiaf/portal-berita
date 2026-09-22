<?php

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use App\Support\ArticleContentSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->regularUser = User::factory()->nonAdmin()->create();
});

/*
|--------------------------------------------------------------------------
| 1. Sanitizer & Security Tests
|--------------------------------------------------------------------------
*/

test('article content sanitizer preserves safe tags and formatting', function () {
    $input = '<p>Paragraf <strong>tebal</strong>, <em>miring</em>, <u>garis bawah</u>.</p>'
        .'<h2>Judul H2</h2><h3>Judul H3</h3>'
        .'<ul><li>Item 1</li><li>Item 2</li></ul>'
        .'<blockquote>Kutipan penting</blockquote>'
        .'<a href="https://example.com" title="Contoh">Tautan Aman</a>';

    $cleaned = ArticleContentSanitizer::sanitize($input);

    expect($cleaned)->toContain('<strong>tebal</strong>')
        ->and($cleaned)->toContain('<em>miring</em>')
        ->and($cleaned)->toContain('<u>garis bawah</u>')
        ->and($cleaned)->toContain('<h2>Judul H2</h2>')
        ->and($cleaned)->toContain('<h3>Judul H3</h3>')
        ->and($cleaned)->toContain('<ul><li>Item 1</li><li>Item 2</li></ul>')
        ->and($cleaned)->toContain('<blockquote>Kutipan penting</blockquote>')
        ->and($cleaned)->toContain('href="https://example.com"');
});

test('article content sanitizer strips script tags and their content', function () {
    $input = '<p>Berita normal <script>alert("XSS Attack!"); document.location="https://evil.com";</script>berlanjut di sini.</p>';

    $cleaned = ArticleContentSanitizer::sanitize($input);

    expect($cleaned)->not->toContain('<script>')
        ->and($cleaned)->not->toContain('alert(')
        ->and($cleaned)->not->toContain('evil.com')
        ->and($cleaned)->toContain('Berita normal')
        ->and($cleaned)->toContain('berlanjut di sini.');
});

test('article content sanitizer strips dangerous tags like iframe, embed, and form', function () {
    $input = '<div>Teks awal <iframe src="https://evil.com"></iframe><form action="/steal"><input type="text"></form></div>';

    $cleaned = ArticleContentSanitizer::sanitize($input);

    expect($cleaned)->not->toContain('<iframe')
        ->and($cleaned)->not->toContain('<form')
        ->and($cleaned)->not->toContain('<input')
        ->and($cleaned)->toContain('Teks awal');
});

test('article content sanitizer strips on* event handlers and arbitrary style/class', function () {
    $input = '<p onclick="alert(1)" onmouseover="steal()" style="color:red" class="dangerous-class" id="p1">Konten</p>';

    $cleaned = ArticleContentSanitizer::sanitize($input);

    expect($cleaned)->toBe('<p>Konten</p>');
});

test('article content sanitizer neutralizes javascript and data protocol links', function () {
    $input = '<p><a href="javascript:alert(\'hack\')">Klik Disini</a> dan <a href="data:text/html;base64,PHNjcmlwdD4=">Payload</a></p>';

    $cleaned = ArticleContentSanitizer::sanitize($input);

    expect($cleaned)->not->toContain('javascript:')
        ->and($cleaned)->not->toContain('data:')
        ->and($cleaned)->toContain('<a>Klik Disini</a>')
        ->and($cleaned)->toContain('<a>Payload</a>');
});

test('article content sanitizer enforces rel noopener noreferrer when target blank is used', function () {
    $input = '<p><a href="https://portalberita.test/doc" target="_blank">Dokumen</a></p>';

    $cleaned = ArticleContentSanitizer::sanitize($input);

    expect($cleaned)->toContain('target="_blank"')
        ->and($cleaned)->toContain('rel="noopener noreferrer"');
});

test('admin article creation sanitizes content before saving to database', function () {
    $category = Category::factory()->create();

    $this->actingAs($this->admin)->post(route('admin.articles.store'), [
        'category_id' => $category->id,
        'title' => 'Artikel Berita Security',
        'content' => '<p>Paragraf aman <script>stealCookies();</script><a href="javascript:bad()" onclick="hack()">Link</a></p>',
        'action' => 'draft',
    ]);

    $article = Article::where('title', 'Artikel Berita Security')->first();
    expect($article)->not->toBeNull();
    expect($article->content)->not->toContain('<script>')
        ->and($article->content)->not->toContain('stealCookies')
        ->and($article->content)->not->toContain('onclick')
        ->and($article->content)->not->toContain('javascript:');
});

/*
|--------------------------------------------------------------------------
| 2. Homepage Tests
|--------------------------------------------------------------------------
*/

test('homepage returns 200 and renders successfully', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertViewIs('public.home');
});

test('homepage shows friendly empty state when no published articles exist', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Belum ada berita yang dipublikasikan.');
});

test('draft articles do not appear anywhere on homepage', function () {
    $category = Category::factory()->create(['name' => 'Politik']);
    $draft = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
        'title' => 'Rahasia Draft Tidak Boleh Muncul',
        'status' => ArticleStatus::DRAFT,
        'is_featured' => true,
        'is_trending' => true,
        'view_count' => 99999,
        'published_at' => null,
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertDontSee('Rahasia Draft Tidak Boleh Muncul');
});

test('latest published featured article is chosen as main headline', function () {
    $category = Category::factory()->create();

    $olderFeatured = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
        'title' => 'Headline Lama',
        'status' => ArticleStatus::PUBLISHED,
        'is_featured' => true,
        'published_at' => now()->subDay(),
    ]);

    $newerFeatured = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
        'title' => 'Headline Paling Baru',
        'status' => ArticleStatus::PUBLISHED,
        'is_featured' => true,
        'published_at' => now(),
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Headline Paling Baru');
    expect($response->viewData('headline')->id)->toBe($newerFeatured->id);
});

test('headline falls back to latest published article if no featured article exists', function () {
    $category = Category::factory()->create();

    $publishedArticle = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
        'title' => 'Berita Biasa Terkini Fallback Headline',
        'status' => ArticleStatus::PUBLISHED,
        'is_featured' => false,
        'published_at' => now(),
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Berita Biasa Terkini Fallback Headline');
    expect($response->viewData('headline')->id)->toBe($publishedArticle->id);
});

test('main headline is not duplicated inside secondary featured list', function () {
    $category = Category::factory()->create();

    $headline = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
        'title' => 'Featured 1 Utama',
        'status' => ArticleStatus::PUBLISHED,
        'is_featured' => true,
        'published_at' => now(),
    ]);

    $second = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
        'title' => 'Featured 2 Sekunder',
        'status' => ArticleStatus::PUBLISHED,
        'is_featured' => true,
        'published_at' => now()->subHours(1),
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $secondary = $response->viewData('secondaryFeatured');
    expect($secondary->pluck('id'))->not->toContain($headline->id)
        ->and($secondary->pluck('id'))->toContain($second->id);
});

test('popular news is ordered by view count descending and ignores drafts', function () {
    $category = Category::factory()->create();

    $artLow = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
        'title' => 'Berita Pembaca Sedikit',
        'status' => ArticleStatus::PUBLISHED,
        'view_count' => 10,
        'published_at' => now(),
    ]);

    $artHigh = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
        'title' => 'Berita Viral Banyak Pembaca',
        'status' => ArticleStatus::PUBLISHED,
        'view_count' => 500,
        'published_at' => now(),
    ]);

    $draftViral = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
        'title' => 'Draft Viral Tidak Boleh Muncul',
        'status' => ArticleStatus::DRAFT,
        'view_count' => 9999,
        'published_at' => null,
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $popular = $response->viewData('popular');
    expect($popular->first()->id)->toBe($artHigh->id)
        ->and($popular->pluck('id'))->not->toContain($draftViral->id);
});

test('trending news displays published editorial trending articles only', function () {
    $category = Category::factory()->create();

    $trendingPublished = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
        'title' => 'Berita Trending Redaksi',
        'status' => ArticleStatus::PUBLISHED,
        'is_trending' => true,
        'published_at' => now(),
    ]);

    $trendingDraft = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
        'title' => 'Trending Draft Tersembunyi',
        'status' => ArticleStatus::DRAFT,
        'is_trending' => true,
        'published_at' => null,
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $trending = $response->viewData('trending');
    expect($trending->pluck('id'))->toContain($trendingPublished->id)
        ->and($trending->pluck('id'))->not->toContain($trendingDraft->id);
});

test('category sections on homepage show categories with published articles', function () {
    $techCategory = Category::factory()->create(['name' => 'Teknologi']);
    $emptyCategory = Category::factory()->create(['name' => 'Kosong']);

    Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $techCategory->id,
        'title' => 'Inovasi AI Indonesia',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Teknologi');
    $response->assertSee('Inovasi AI Indonesia');
    $categorySections = $response->viewData('categorySections');
    expect($categorySections->pluck('id'))->toContain($techCategory->id)
        ->and($categorySections->pluck('id'))->not->toContain($emptyCategory->id);
});

/*
|--------------------------------------------------------------------------
| 3. Category Archive Page Tests
|--------------------------------------------------------------------------
*/

test('category page displays category information and published articles', function () {
    $category = Category::factory()->create([
        'name' => 'Olahraga',
        'slug' => 'olahraga',
        'description' => 'Kanal seputar pertandingan olahraga terkini.',
    ]);

    $otherCategory = Category::factory()->create(['name' => 'Hiburan']);

    $article = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
        'title' => 'Timnas Juara Piala Asia',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    $draftArticle = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
        'title' => 'Draft Rencana Pertandingan',
        'status' => ArticleStatus::DRAFT,
        'published_at' => null,
    ]);

    $otherArticle = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $otherCategory->id,
        'title' => 'Konser Musik Megah',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    $response = $this->get(route('categories.show', $category));

    $response->assertOk();
    $response->assertSee('Olahraga');
    $response->assertSee('Kanal seputar pertandingan olahraga terkini.');
    $response->assertSee('Timnas Juara Piala Asia');
    $response->assertDontSee('Draft Rencana Pertandingan');
    $response->assertDontSee('Konser Musik Megah');
});

test('empty category returns 200 with empty state notice', function () {
    $category = Category::factory()->create([
        'name' => 'Sains',
        'slug' => 'sains',
    ]);

    $response = $this->get(route('categories.show', $category));

    $response->assertOk();
    $response->assertSee('Belum ada berita pada kategori ini.');
});

test('invalid category slug returns 404', function () {
    $response = $this->get('/kategori/slug-yang-tidak-pernah-ada');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| 4. Minimal Article Bridge Tests
|--------------------------------------------------------------------------
*/

test('published article can be viewed via minimal detail bridge route', function () {
    $category = Category::factory()->create(['name' => 'Nasional']);
    $article = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
        'title' => 'Pembangunan Infrastruktur Berkelanjutan',
        'slug' => 'pembangunan-infrastruktur-berkelanjutan',
        'excerpt' => 'Ringkasan penting tentang proyek infrastruktur.',
        'content' => '<p>Konten rahasia lengkap fase 4 yang belum boleh dirender.</p>',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    $response = $this->get(route('articles.show', $article));

    $response->assertOk();
    $response->assertSee('Pembangunan Infrastruktur Berkelanjutan');
    $response->assertSee('Nasional');
    $response->assertSee('Ringkasan penting tentang proyek infrastruktur.');
    // Must NOT render full raw body content in Phase 3 minimal bridge
    $response->assertDontSee('Konten rahasia lengkap fase 4 yang belum boleh dirender.');
});

test('draft article returns 404 on article detail route', function () {
    $category = Category::factory()->create();
    $draft = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
        'title' => 'Draft Tertutup',
        'slug' => 'draft-tertutup',
        'status' => ArticleStatus::DRAFT,
        'published_at' => null,
    ]);

    $response = $this->get(route('articles.show', $draft));

    $response->assertNotFound();
});

test('invalid article slug returns 404', function () {
    $response = $this->get('/berita/slug-artikel-tidak-ditemukan');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| 5. Regression & Protection Tests
|--------------------------------------------------------------------------
*/

test('admin routes remain protected against unauthenticated visitors', function () {
    $response = $this->get(route('admin.dashboard'));
    $response->assertRedirect(route('admin.login'));

    $response = $this->get(route('admin.articles.index'));
    $response->assertRedirect(route('admin.login'));
});
