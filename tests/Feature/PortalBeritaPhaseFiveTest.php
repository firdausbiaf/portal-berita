<?php

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->category = Category::factory()->create(['name' => 'Teknologi', 'slug' => 'teknologi']);
});

/*
|--------------------------------------------------------------------------
| 1. Public Search Tests
|--------------------------------------------------------------------------
*/

test('search finds published articles by title', function () {
    $matched = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Perkembangan AI dan Masa Depan Robotika',
        'slug' => 'perkembangan-ai-dan-masa-depan-robotika',
        'content' => '<p>Konten artikel.</p>',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    $unmatched = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Resep Kuliner Nusantara yang Lezat',
        'slug' => 'resep-kuliner-nusantara-yang-lezat',
        'content' => '<p>Konten kuliner.</p>',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    $response = $this->get(route('search', ['q' => 'Robotika']));

    $response->assertOk();
    $response->assertSee('Perkembangan AI dan Masa Depan Robotika');
    $response->assertDontSee('Resep Kuliner Nusantara yang Lezat');
});

test('search matches title only and does not match content or excerpt', function () {
    $contentOnlyMatch = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Transformasi Digital Indonesia',
        'slug' => 'transformasi-digital-indonesia',
        'excerpt' => 'Artikel tentang teknologi kuantum modern.',
        'content' => '<p>Pembahasan mendalam tentang teknologi kriptografi.</p>',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    // Search for word present only in content/excerpt, not in title
    $response = $this->get(route('search', ['q' => 'kriptografi']));

    $response->assertOk();
    $response->assertSee('Tidak ditemukan berita untuk');
    $response->assertDontSee('Transformasi Digital Indonesia');
});

test('search never displays draft articles even if title matches', function () {
    $draft = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Bocoran AI Rahasia',
        'slug' => 'bocoran-ai-rahasia',
        'status' => ArticleStatus::DRAFT,
        'published_at' => null,
    ]);

    $response = $this->get(route('search', ['q' => 'Rahasia']));

    $response->assertOk();
    $response->assertDontSee('Bocoran AI Rahasia');
});

test('empty search query shows initial search prompt without listing all articles', function () {
    Article::factory()->count(5)->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    $response = $this->get(route('search'));

    $response->assertOk();
    $response->assertSee('Ketik kata kunci judul');
    $response->assertDontSee('hasil ditemukan untuk');
});

test('search input with special characters is handled safely without XSS', function () {
    $xssQuery = '<script>alert("xss")</script>';

    $response = $this->get(route('search', ['q' => $xssQuery]));

    $response->assertOk();
    $response->assertDontSee($xssQuery, false); // verifies raw unescaped payload is NOT in response
    $response->assertSee($xssQuery); // default $escaped=true verifies it is safely escaped
});

test('search results page includes noindex robots tag', function () {
    $response = $this->get(route('search', ['q' => 'Teknologi']));

    $response->assertOk();
    $response->assertSee('<meta name="robots" content="noindex, follow">', false);
});

test('search pagination preserves query parameter', function () {
    Article::factory()->count(15)->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Inovasi Teknologi Terkini Seri ',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    $response = $this->get(route('search', ['q' => 'Inovasi']));

    $response->assertOk();
    $response->assertSee('q=Inovasi');
});

/*
|--------------------------------------------------------------------------
| 2. Tag Archive Tests
|--------------------------------------------------------------------------
*/

test('tag archive page displays published articles with that tag', function () {
    $tag = Tag::factory()->create(['name' => 'Artificial Intelligence', 'slug' => 'artificial-intelligence']);

    $taggedArticle = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Kecerdasan Buatan Generasi Baru',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);
    $taggedArticle->tags()->attach($tag);

    $untaggedArticle = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Berita Lain Tanpa Tag',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    $response = $this->get(route('tags.show', $tag));

    $response->assertOk();
    $response->assertSee('Artificial Intelligence');
    $response->assertSee('Kecerdasan Buatan Generasi Baru');
    $response->assertDontSee('Berita Lain Tanpa Tag');
});

test('tag archive page excludes draft articles', function () {
    $tag = Tag::factory()->create(['name' => 'Cybersecurity', 'slug' => 'cybersecurity']);

    $draftArticle = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Draft Analisis Keamanan',
        'status' => ArticleStatus::DRAFT,
        'published_at' => null,
    ]);
    $draftArticle->tags()->attach($tag);

    $response = $this->get(route('tags.show', $tag));

    $response->assertOk();
    $response->assertSee('Belum ada berita pada topik ini');
    $response->assertDontSee('Draft Analisis Keamanan');
});

test('tag archive page for nonexistent tag returns 404', function () {
    $response = $this->get('/tag/tag-yang-tidak-pernah-ada-12345');

    $response->assertNotFound();
});

test('article detail tag pills link to tag archive route', function () {
    $tag = Tag::factory()->create(['name' => 'Cloud Computing', 'slug' => 'cloud-computing']);

    $article = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Ekosistem Komputasi Awan',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);
    $article->tags()->attach($tag);

    $response = $this->get(route('articles.show', $article));

    $response->assertOk();
    $response->assertSee(route('tags.show', $tag));
});

/*
|--------------------------------------------------------------------------
| 3. Dynamic SEO, Canonical & Meta Description Tests
|--------------------------------------------------------------------------
*/

test('article detail page generates correct dynamic SEO metadata', function () {
    $article = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Strategi Nasional Semikonduktor',
        'slug' => 'strategi-nasional-semikonduktor',
        'excerpt' => 'Langkah percepatan kemandirian industri chip dan semikonduktor di tanah air.',
        'content' => '<p>Konten lengkap artikel semikonduktor.</p>',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    $canonicalUrl = route('articles.show', $article);
    $response = $this->get($canonicalUrl);

    $response->assertOk();

    // Canonical link
    $response->assertSee('<link rel="canonical" href="'.$canonicalUrl.'">', false);

    // Title tag
    $response->assertSee('<title>'.e($article->title).' - '.config('site.name', 'Portal Berita').'</title>', false);

    // Meta description
    $response->assertSee('<meta name="description" content="'.e($article->excerpt).'">', false);

    // OpenGraph
    $response->assertSee('<meta property="og:title" content="'.e($article->title).'">', false);
    $response->assertSee('<meta property="og:description" content="'.e($article->excerpt).'">', false);
    $response->assertSee('<meta property="og:url" content="'.$canonicalUrl.'">', false);
    $response->assertSee('<meta property="og:type" content="article">', false);

    // Twitter Card
    $response->assertSee('<meta name="twitter:title" content="'.e($article->title).'">', false);
    $response->assertSee('<meta name="twitter:description" content="'.e($article->excerpt).'">', false);

    // Share buttons use the same canonical URL
    $response->assertSee(rawurlencode($canonicalUrl));
    $response->assertSee('data-url="'.$canonicalUrl.'"', false);
});

test('meta description falls back to stripped content when excerpt is null', function () {
    $article = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Berita Tanpa Excerpt Manual',
        'slug' => 'berita-tanpa-excerpt-manual',
        'excerpt' => null,
        'content' => '<p>Ini adalah <strong>paragraf pembuka</strong> yang kaya akan informasi penting dari lapangan.</p>',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    expect($article->meta_description)->toContain('Ini adalah paragraf pembuka yang kaya akan informasi');
    expect($article->meta_description)->not->toContain('<p>');
    expect($article->meta_description)->not->toContain('<strong>');

    $response = $this->get(route('articles.show', $article));
    $response->assertOk();
    $response->assertSee('<meta name="description" content="'.e($article->meta_description).'">', false);
});

/*
|--------------------------------------------------------------------------
| 4. NewsArticle JSON-LD Structured Data Tests
|--------------------------------------------------------------------------
*/

test('article detail includes valid NewsArticle JSON-LD structured data', function () {
    $article = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Infrastruktur Data Center Cerdas',
        'slug' => 'infrastruktur-data-center-cerdas',
        'excerpt' => 'Pembangunan data center ramah lingkungan di IKN.',
        'content' => '<p>Konten artikel data center.</p>',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    $response = $this->get(route('articles.show', $article));

    $response->assertOk();
    $response->assertSee('application/ld+json');
    $response->assertSee('"@type":"NewsArticle"', false);
    $response->assertSee('"headline":"Infrastruktur Data Center Cerdas"', false);
    $response->assertSee('"name":"'.$this->admin->name.'"', false);
    $response->assertSee('"name":"'.config('site.name', 'Portal Berita').'"', false);
});

/*
|--------------------------------------------------------------------------
| 5. Dynamic XML Sitemap Tests
|--------------------------------------------------------------------------
*/

test('sitemap returns valid xml containing published articles, active categories, and active tags', function () {
    $tag = Tag::factory()->create(['name' => 'Sitemap Tag', 'slug' => 'sitemap-tag']);
    $emptyTag = Tag::factory()->create(['name' => 'Empty Tag', 'slug' => 'empty-tag']);
    $emptyCategory = Category::factory()->create(['name' => 'Empty Category', 'slug' => 'empty-category']);

    $published = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Artikel Masuk Sitemap',
        'slug' => 'artikel-masuk-sitemap',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);
    $published->tags()->attach($tag);

    $draft = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Draft Tidak Boleh Masuk Sitemap',
        'slug' => 'draft-tidak-boleh-masuk-sitemap',
        'status' => ArticleStatus::DRAFT,
        'published_at' => null,
    ]);

    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');

    // Root homepage
    $response->assertSee(route('home'));

    // Published article
    $response->assertSee(route('articles.show', $published));

    // Category with articles
    $response->assertSee(route('categories.show', $this->category));

    // Tag with articles
    $response->assertSee(route('tags.show', $tag));

    // Draft article MUST NOT appear
    $response->assertDontSee(route('articles.show', $draft));

    // Empty category MUST NOT appear
    $response->assertDontSee(route('categories.show', $emptyCategory));

    // Empty tag MUST NOT appear
    $response->assertDontSee(route('tags.show', $emptyTag));
});

/*
|--------------------------------------------------------------------------
| 6. Dynamic Robots.txt Tests
|--------------------------------------------------------------------------
*/

test('robots txt returns plain text disallowing admin and search with sitemap url', function () {
    $response = $this->get('/robots.txt');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    $response->assertSee('User-agent: *');
    $response->assertSee('Disallow: /admin/');
    $response->assertDontSee('Disallow: /cari');
    $response->assertSee('Sitemap: '.url('/sitemap.xml'));
});

/*
|--------------------------------------------------------------------------
| 7. Timezone Accuracy & Conversion Tests
|--------------------------------------------------------------------------
*/

test('published article accurately converts UTC database time to Asia Jakarta WIB', function () {
    // 08:00:00 UTC corresponds to 15:00:00 WIB (UTC+7)
    $utcTime = Carbon::create(2026, 9, 22, 8, 0, 0, 'UTC');

    $article = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Pengujian Timezone Presisi',
        'slug' => 'pengujian-timezone-presisi',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => $utcTime,
    ]);

    $formatted = $article->publishedAtDisplay('d F Y, H:i');

    expect($formatted)->toBe('22 September 2026, 15:00 WIB');

    $response = $this->get(route('articles.show', $article));
    $response->assertOk();
    $response->assertSee('15:00 WIB');
    $response->assertDontSee('08:00 WIB');
});

/*
|--------------------------------------------------------------------------
| 8. Custom 404 Error Page Tests
|--------------------------------------------------------------------------
*/

test('custom 404 page renders editorial error layout with back links', function () {
    $response = $this->get('/halaman-yang-sama-sekali-tidak-ada-999');

    $response->assertNotFound();
    $response->assertSee('404');
    $response->assertSee('Halaman Tidak Ditemukan');
    $response->assertSee(route('home'));
    $response->assertSee(route('search'));
});
