<?php

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->category = Category::factory()->create(['name' => 'Teknologi', 'slug' => 'teknologi']);
});

/*
|--------------------------------------------------------------------------
| 1. Article Detail & Content Rendering Tests
|--------------------------------------------------------------------------
*/

test('published article detail page returns 200 and displays all components', function () {
    $article = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Inovasi Superkomputer Nasional',
        'slug' => 'inovasi-superkomputer-nasional',
        'excerpt' => 'Ringkasan singkat tentang superkomputer.',
        'content' => '<p>Paragraf pertama berita.</p><h2>Spesifikasi</h2><p>Paragraf kedua berita.</p>',
        'image_caption' => 'Ruang server data center',
        'image_alt' => 'Server berjejer rapi',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    $response = $this->get(route('articles.show', $article));

    $response->assertOk();
    $response->assertSee('Inovasi Superkomputer Nasional');
    $response->assertSee('Teknologi');
    $response->assertSee($this->admin->name);
    $response->assertSee('Ringkasan singkat tentang superkomputer.');
    $response->assertSee('Paragraf pertama berita.');
    $response->assertSee('Spesifikasi');
    $response->assertSee('Ruang server data center');
});

test('draft article returns 404 on article detail route', function () {
    $draft = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Draft Tersembunyi',
        'slug' => 'draft-tersembunyi',
        'status' => ArticleStatus::DRAFT,
        'published_at' => null,
    ]);

    $response = $this->get(route('articles.show', $draft));

    $response->assertNotFound();
});

test('non-existent article slug returns 404', function () {
    $response = $this->get('/berita/slug-tidak-ada-di-database');

    $response->assertNotFound();
});

test('article with null or empty content handles gracefully', function () {
    $article = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Berita Tanpa Konten',
        'slug' => 'berita-tanpa-konten',
        'content' => null,
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    $response = $this->get(route('articles.show', $article));

    $response->assertOk();
    $response->assertSee('Konten berita belum tersedia.');
});

/*
|--------------------------------------------------------------------------
| 2. Defense-in-Depth / Historical Content Safety Tests
|--------------------------------------------------------------------------
*/

test('historical unsafe article content in database is neutralized before rendering', function () {
    // Save directly to database without passing through admin store/update
    $article = Article::create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Berita Legacy Rentan',
        'slug' => 'berita-legacy-rentan',
        'excerpt' => 'Excerpt aman',
        'content' => '<p>Paragraf Berita Sah</p>'
            .'<script>alert("XSS Legacy Attack!"); window.location="https://attacker.com";</script>'
            .'<a href="javascript:stealCookie()" onclick="dangerousAction()" style="color:red">Tautan Bahaya</a>'
            .'<iframe src="https://attacker.com/embed"></iframe>',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    $response = $this->get(route('articles.show', $article));

    $response->assertOk();
    // Safe content must be rendered
    $response->assertSee('Paragraf Berita Sah');
    // Dangerous script and its text must be purged from safeContent and output
    expect($response->viewData('safeContent'))->not->toContain('<script>')
        ->and($response->viewData('safeContent'))->not->toContain('<iframe')
        ->and($response->viewData('safeContent'))->not->toContain('onclick')
        ->and($response->viewData('safeContent'))->not->toContain('javascript:');

    $response->assertDontSee('XSS Legacy Attack!');
    $response->assertDontSee('attacker.com');
    $response->assertDontSee('javascript:stealCookie');
    $response->assertDontSee('dangerousAction()');
    $response->assertDontSee('<iframe', false);
});

/*
|--------------------------------------------------------------------------
| 3. View Counter MVP Tests (Session-Based)
|--------------------------------------------------------------------------
*/

test('view counter increments on first view in a session', function () {
    $article = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'status' => ArticleStatus::PUBLISHED,
        'view_count' => 10,
        'published_at' => now(),
    ]);

    $response = $this->get(route('articles.show', $article));

    $response->assertOk();
    expect($article->fresh()->view_count)->toBe(11);
});

test('view counter does not increment on refresh within the same session', function () {
    $article = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'status' => ArticleStatus::PUBLISHED,
        'view_count' => 10,
        'published_at' => now(),
    ]);

    // First visit
    $this->get(route('articles.show', $article));
    expect($article->fresh()->view_count)->toBe(11);

    // Refresh 1
    $this->get(route('articles.show', $article));
    expect($article->fresh()->view_count)->toBe(11);

    // Refresh 2
    $this->get(route('articles.show', $article));
    expect($article->fresh()->view_count)->toBe(11);
});

test('view counter increments separately for different articles in the same session', function () {
    $articleA = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'status' => ArticleStatus::PUBLISHED,
        'view_count' => 5,
        'published_at' => now(),
    ]);

    $articleB = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'status' => ArticleStatus::PUBLISHED,
        'view_count' => 20,
        'published_at' => now(),
    ]);

    // Visit A
    $this->get(route('articles.show', $articleA));
    expect($articleA->fresh()->view_count)->toBe(6);

    // Visit B
    $this->get(route('articles.show', $articleB));
    expect($articleB->fresh()->view_count)->toBe(21);

    // Re-visit A (same session: no increment)
    $this->get(route('articles.show', $articleA));
    expect($articleA->fresh()->view_count)->toBe(6);
});

test('new session can increment view count again', function () {
    $article = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'status' => ArticleStatus::PUBLISHED,
        'view_count' => 50,
        'published_at' => now(),
    ]);

    // Session 1
    $this->get(route('articles.show', $article));
    expect($article->fresh()->view_count)->toBe(51);

    // Flush session to simulate a new visitor / new session
    $this->flushSession();

    // Session 2
    $this->get(route('articles.show', $article));
    expect($article->fresh()->view_count)->toBe(52);
});

test('draft article does not increment view count', function () {
    $draft = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'status' => ArticleStatus::DRAFT,
        'view_count' => 0,
        'published_at' => null,
    ]);

    $this->get(route('articles.show', $draft));

    expect($draft->fresh()->view_count)->toBe(0);
});

/*
|--------------------------------------------------------------------------
| 4. Tags Display Tests
|--------------------------------------------------------------------------
*/

test('article tags are displayed as pills linking to tag archive', function () {
    $article = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    $tag1 = Tag::factory()->create(['name' => 'Artificial Intelligence']);
    $tag2 = Tag::factory()->create(['name' => 'Data Center']);
    $article->tags()->attach([$tag1->id, $tag2->id]);

    $response = $this->get(route('articles.show', $article));

    $response->assertOk();
    $response->assertSee('Topik Terkait');
    $response->assertSee('#Artificial Intelligence');
    $response->assertSee('#Data Center');
    $response->assertSee(route('tags.show', $tag1));
    $response->assertSee(route('tags.show', $tag2));
});

test('article without tags renders cleanly without error', function () {
    $article = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    $response = $this->get(route('articles.show', $article));

    $response->assertOk();
    $response->assertDontSee('Topik Terkait');
});

/*
|--------------------------------------------------------------------------
| 5. Related News Tests
|--------------------------------------------------------------------------
*/

test('related news prioritizes articles from the same category', function () {
    $mainArticle = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Artikel Utama',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    $sameCategoryArticle = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Artikel Kategori Sama',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now()->subHour(),
    ]);

    $otherCategory = Category::factory()->create(['name' => 'Olahraga']);
    $otherCategoryArticle = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $otherCategory->id,
        'title' => 'Artikel Kategori Berbeda',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now()->subHours(2),
    ]);

    $response = $this->get(route('articles.show', $mainArticle));

    $response->assertOk();
    $response->assertSee('Berita Terkait');
    $response->assertSee('Artikel Kategori Sama');
    $related = $response->viewData('related');
    expect($related->pluck('id'))->toContain($sameCategoryArticle->id)
        ->and($related->pluck('id'))->not->toContain($mainArticle->id);
});

test('related news uses shared tags fallback when same category has fewer than 4 articles', function () {
    $tag = Tag::factory()->create(['name' => 'Gadget']);

    $mainArticle = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Review Smartphone Baru',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);
    $mainArticle->tags()->attach($tag->id);

    // Only 1 article in same category
    $sameCatArticle = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Tips Memilih Laptop',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now()->subHours(1),
    ]);

    // Another category, but shares the same tag
    $bisnisCat = Category::factory()->create(['name' => 'Bisnis']);
    $sharedTagArticle = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $bisnisCat->id,
        'title' => 'Pasar Smartphone Q3 Tumbuh Pesat',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now()->subHours(2),
    ]);
    $sharedTagArticle->tags()->attach($tag->id);

    $response = $this->get(route('articles.show', $mainArticle));

    $response->assertOk();
    $related = $response->viewData('related');
    expect($related->pluck('id'))->toContain($sameCatArticle->id)
        ->and($related->pluck('id'))->toContain($sharedTagArticle->id)
        ->and($related->pluck('id'))->not->toContain($mainArticle->id);
});

test('related news excludes draft articles and prevents duplicates', function () {
    $mainArticle = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Artikel Utama Bebas Draft',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    $draftArticle = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Draft Terkait Tidak Boleh Tampil',
        'status' => ArticleStatus::DRAFT,
        'published_at' => null,
    ]);

    $response = $this->get(route('articles.show', $mainArticle));

    $response->assertOk();
    $related = $response->viewData('related');
    expect($related->pluck('id'))->not->toContain($draftArticle->id)
        ->and($related->pluck('id'))->not->toContain($mainArticle->id);
});

/*
|--------------------------------------------------------------------------
| 6. Sidebar Tests
|--------------------------------------------------------------------------
*/

test('sidebar displays popular, latest, and trending published articles excluding current article', function () {
    $currentArticle = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Berita Yang Sedang Dibaca',
        'view_count' => 9999,
        'status' => ArticleStatus::PUBLISHED,
        'is_trending' => true,
        'published_at' => now(),
    ]);

    $otherPopular = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Berita Populer Lain',
        'view_count' => 500,
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now()->subDay(),
    ]);

    $otherLatest = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Berita Paling Baru',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now()->subHour(),
    ]);

    $otherTrending = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Berita Trending Pilihan',
        'status' => ArticleStatus::PUBLISHED,
        'is_trending' => true,
        'published_at' => now()->subHours(2),
    ]);

    $response = $this->get(route('articles.show', $currentArticle));

    $response->assertOk();
    $popular = $response->viewData('popular');
    $latest = $response->viewData('latest');
    $trending = $response->viewData('trending');

    // Current article should not appear in sidebar datasets
    expect($popular->pluck('id'))->not->toContain($currentArticle->id)
        ->and($popular->pluck('id'))->toContain($otherPopular->id)
        ->and($latest->pluck('id'))->not->toContain($currentArticle->id)
        ->and($latest->pluck('id'))->toContain($otherLatest->id)
        ->and($trending->pluck('id'))->not->toContain($currentArticle->id)
        ->and($trending->pluck('id'))->toContain($otherTrending->id);
});

/*
|--------------------------------------------------------------------------
| 7. Share Actions Tests
|--------------------------------------------------------------------------
*/

test('article detail contains properly formatted share links', function () {
    $article = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Judul Berita Khusus',
        'slug' => 'judul-berita-khusus',
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now(),
    ]);

    $response = $this->get(route('articles.show', $article));

    $response->assertOk();
    $response->assertSee('api.whatsapp.com/send?text=', false);
    $response->assertSee('facebook.com/sharer/sharer.php?u=', false);
    $response->assertSee('twitter.com/intent/tweet?url=', false);
    $response->assertSee('id="copy-link-btn"', false);
});
