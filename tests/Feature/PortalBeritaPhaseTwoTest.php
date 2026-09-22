<?php

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->regularUser = User::factory()->nonAdmin()->create();
});

/*
|--------------------------------------------------------------------------
| Category Management Tests
|--------------------------------------------------------------------------
*/

test('admin can view category index with article count', function () {
    $category = Category::factory()->create(['name' => 'Politik']);
    Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.categories.index'));

    $response->assertOk();
    $response->assertSee('Politik');
    $response->assertSee('1 berita');
});

test('admin can create category with auto-generated slug', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
        'name' => 'Kesehatan Masyarakat',
        'description' => 'Berita kesehatan',
    ]);

    $response->assertRedirect(route('admin.categories.index'));
    $this->assertDatabaseHas('categories', [
        'name' => 'Kesehatan Masyarakat',
        'slug' => 'kesehatan-masyarakat',
        'description' => 'Berita kesehatan',
    ]);
});

test('admin can update category and uniqueness ignores current model', function () {
    $category = Category::factory()->create([
        'name' => 'Ekonomi Lama',
        'slug' => 'ekonomi-lama',
    ]);

    $response = $this->actingAs($this->admin)->put(route('admin.categories.update', $category), [
        'name' => 'Ekonomi Baru',
        'slug' => 'ekonomi-lama', // keeping same slug
    ]);

    $response->assertRedirect(route('admin.categories.index'));
    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Ekonomi Baru',
        'slug' => 'ekonomi-lama',
    ]);
});

test('admin can delete category that has no articles', function () {
    $category = Category::factory()->create();

    $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));

    $response->assertRedirect(route('admin.categories.index'));
    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('admin cannot delete category that still has articles', function () {
    $category = Category::factory()->create(['name' => 'Edukasi']);
    Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
    ]);

    $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));

    $response->assertSessionHas('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh berita.');
    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('category slug must be unique', function () {
    Category::factory()->create(['slug' => 'teknologi']);

    $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
        'name' => 'Teknologi Lain',
        'slug' => 'teknologi',
    ]);

    $response->assertSessionHasErrors('slug');
});

/*
|--------------------------------------------------------------------------
| Tag Management Tests
|--------------------------------------------------------------------------
*/

test('admin can view tag index and create tag', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.tags.store'), [
        'name' => 'Machine Learning',
    ]);

    $response->assertRedirect(route('admin.tags.index'));
    $this->assertDatabaseHas('tags', [
        'name' => 'Machine Learning',
        'slug' => 'machine-learning',
    ]);

    $indexResponse = $this->actingAs($this->admin)->get(route('admin.tags.index'));
    $indexResponse->assertOk();
    $indexResponse->assertSee('Machine Learning');
});

test('admin can update tag', function () {
    $tag = Tag::factory()->create(['name' => 'Startups', 'slug' => 'startups']);

    $response = $this->actingAs($this->admin)->put(route('admin.tags.update', $tag), [
        'name' => 'Startup Digital',
        'slug' => 'startup-digital',
    ]);

    $response->assertRedirect(route('admin.tags.index'));
    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'name' => 'Startup Digital',
        'slug' => 'startup-digital',
    ]);
});

test('deleting tag detaches pivot without deleting article', function () {
    $category = Category::factory()->create();
    $tag = Tag::factory()->create(['name' => 'Investasi']);
    $article = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
    ]);
    $article->tags()->attach($tag->id);

    $response = $this->actingAs($this->admin)->delete(route('admin.tags.destroy', $tag));

    $response->assertRedirect(route('admin.tags.index'));
    $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    $this->assertDatabaseMissing('article_tag', ['tag_id' => $tag->id]);
    $this->assertDatabaseHas('articles', ['id' => $article->id]);
});

/*
|--------------------------------------------------------------------------
| Article Management & Publishing Workflow Tests
|--------------------------------------------------------------------------
*/

test('admin can create article as draft with published_at null', function () {
    $category = Category::factory()->create();

    $response = $this->actingAs($this->admin)->post(route('admin.articles.store'), [
        'title' => 'Rancangan Anggaran Baru Diumumkan',
        'category_id' => $category->id,
        'action' => 'draft',
    ]);

    $response->assertRedirect(route('admin.articles.index'));
    $this->assertDatabaseHas('articles', [
        'title' => 'Rancangan Anggaran Baru Diumumkan',
        'slug' => 'rancangan-anggaran-baru-diumumkan',
        'status' => ArticleStatus::DRAFT->value,
        'published_at' => null,
    ]);
});

test('admin can create and immediately publish article with published_at timestamp', function () {
    $category = Category::factory()->create();

    $response = $this->actingAs($this->admin)->post(route('admin.articles.store'), [
        'title' => 'Peluncuran Satelit Nusantara Sukses',
        'category_id' => $category->id,
        'action' => 'publish',
        'is_featured' => 1,
        'is_trending' => 1,
    ]);

    $response->assertRedirect(route('admin.articles.index'));
    $article = Article::where('title', 'Peluncuran Satelit Nusantara Sukses')->first();

    expect($article)->not->toBeNull()
        ->and($article->status)->toBe(ArticleStatus::PUBLISHED)
        ->and($article->published_at)->not->toBeNull()
        ->and($article->is_featured)->toBeTrue()
        ->and($article->is_trending)->toBeTrue();
});

test('admin can update article title without automatically changing existing slug', function () {
    $category = Category::factory()->create();
    $article = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
        'title' => 'Judul Awal Berita',
        'slug' => 'judul-awal-berita',
    ]);

    $response = $this->actingAs($this->admin)->put(route('admin.articles.update', $article), [
        'title' => 'Judul Baru yang Telah Direvisi',
        'category_id' => $category->id,
        // slug left empty so existing slug is preserved
    ]);

    $response->assertRedirect(route('admin.articles.index'));
    $article->refresh();

    expect($article->title)->toBe('Judul Baru yang Telah Direvisi')
        ->and($article->slug)->toBe('judul-awal-berita');
});

test('admin can unpublish article and republish it', function () {
    $category = Category::factory()->create();
    $article = Article::factory()->published()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
        'published_at' => now()->subDays(5),
    ]);

    // 1. Unpublish
    $unpublishResponse = $this->actingAs($this->admin)->post(route('admin.articles.unpublish', $article));
    $unpublishResponse->assertSessionHas('success');
    $article->refresh();

    expect($article->status)->toBe(ArticleStatus::DRAFT)
        ->and($article->published_at)->toBeNull();

    // 2. Republish
    $republishResponse = $this->actingAs($this->admin)->post(route('admin.articles.publish', $article));
    $republishResponse->assertSessionHas('success');
    $article->refresh();

    expect($article->status)->toBe(ArticleStatus::PUBLISHED)
        ->and($article->published_at)->not->toBeNull();
});

test('admin can attach multiple tags and sync them on update', function () {
    $category = Category::factory()->create();
    $tag1 = Tag::factory()->create();
    $tag2 = Tag::factory()->create();
    $tag3 = Tag::factory()->create();

    // Create article with tag1 and tag2
    $this->actingAs($this->admin)->post(route('admin.articles.store'), [
        'title' => 'Berita dengan Multi Tag',
        'category_id' => $category->id,
        'tags' => [$tag1->id, $tag2->id],
    ]);

    $article = Article::where('title', 'Berita dengan Multi Tag')->first();
    expect($article->tags)->toHaveCount(2);

    // Update with tag2 and tag3 (tag1 should be detached)
    $this->actingAs($this->admin)->put(route('admin.articles.update', $article), [
        'title' => 'Berita dengan Multi Tag',
        'category_id' => $category->id,
        'tags' => [$tag2->id, $tag3->id],
    ]);

    $article->refresh();
    expect($article->tags)->toHaveCount(2)
        ->and($article->tags->pluck('id'))->toContain($tag2->id, $tag3->id)
        ->not->toContain($tag1->id);
});

test('featured image upload, replacement, and deletion work cleanly', function () {
    Storage::fake('public');
    $category = Category::factory()->create();

    $file1 = UploadedFile::fake()->image('hero.jpg');

    // 1. Upload
    $this->actingAs($this->admin)->post(route('admin.articles.store'), [
        'title' => 'Berita dengan Foto',
        'category_id' => $category->id,
        'featured_image' => $file1,
    ]);

    $article = Article::where('title', 'Berita dengan Foto')->first();
    expect($article->featured_image)->not->toBeNull();
    Storage::disk('public')->assertExists($article->featured_image);

    $oldImagePath = $article->featured_image;

    // 2. Replace image
    $file2 = UploadedFile::fake()->image('hero_new.webp');
    $this->actingAs($this->admin)->put(route('admin.articles.update', $article), [
        'title' => 'Berita dengan Foto',
        'category_id' => $category->id,
        'featured_image' => $file2,
    ]);

    $article->refresh();
    Storage::disk('public')->assertMissing($oldImagePath);
    Storage::disk('public')->assertExists($article->featured_image);

    // 3. Delete article cleans up image
    $currentImagePath = $article->featured_image;
    $this->actingAs($this->admin)->delete(route('admin.articles.destroy', $article));

    Storage::disk('public')->assertMissing($currentImagePath);
    $this->assertDatabaseMissing('articles', ['id' => $article->id]);
});

test('admin article index search and filtering work with pagination', function () {
    $catA = Category::factory()->create(['name' => 'Olahraga']);
    $catB = Category::factory()->create(['name' => 'Bisnis']);

    Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $catA->id,
        'title' => 'Kemenangan Timnas Indonesia',
        'status' => ArticleStatus::PUBLISHED,
    ]);

    Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $catB->id,
        'title' => 'IHSG Menguat Hari Ini',
        'status' => ArticleStatus::DRAFT,
    ]);

    // Search by title
    $response = $this->actingAs($this->admin)->get(route('admin.articles.index', ['search' => 'Timnas']));
    $response->assertSee('Kemenangan Timnas Indonesia');
    $response->assertDontSee('IHSG Menguat Hari Ini');

    // Filter by status
    $responseStatus = $this->actingAs($this->admin)->get(route('admin.articles.index', ['status' => 'draft']));
    $responseStatus->assertSee('IHSG Menguat Hari Ini');
    $responseStatus->assertDontSee('Kemenangan Timnas Indonesia');

    // Filter by category
    $responseCat = $this->actingAs($this->admin)->get(route('admin.articles.index', ['category_id' => $catA->id]));
    $responseCat->assertSee('Kemenangan Timnas Indonesia');
    $responseCat->assertDontSee('IHSG Menguat Hari Ini');
});

/*
|--------------------------------------------------------------------------
| Dashboard & Authorization Tests
|--------------------------------------------------------------------------
*/

test('admin can access dashboard and view statistics', function () {
    $category = Category::factory()->create();
    Article::factory()->published()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
        'title' => 'Headline Terkini',
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Ringkasan Konten');
    $response->assertSee('Headline Terkini');
});

test('guest cannot access any admin CMS routes', function () {
    $category = Category::factory()->create();
    $article = Article::factory()->create([
        'author_id' => $this->admin->id,
        'category_id' => $category->id,
    ]);

    $this->get(route('admin.categories.index'))->assertRedirect(route('admin.login'));
    $this->get(route('admin.tags.index'))->assertRedirect(route('admin.login'));
    $this->get(route('admin.articles.index'))->assertRedirect(route('admin.login'));
    $this->post(route('admin.articles.store'), [])->assertRedirect(route('admin.login'));
    $this->delete(route('admin.articles.destroy', $article))->assertRedirect(route('admin.login'));
});

test('non-admin user is forbidden from accessing admin CMS routes', function () {
    $this->actingAs($this->regularUser)->get(route('admin.categories.index'))->assertForbidden();
    $this->actingAs($this->regularUser)->get(route('admin.tags.index'))->assertForbidden();
    $this->actingAs($this->regularUser)->get(route('admin.articles.index'))->assertForbidden();
    $this->actingAs($this->regularUser)->post(route('admin.categories.store'), ['name' => 'Test'])->assertForbidden();
});
