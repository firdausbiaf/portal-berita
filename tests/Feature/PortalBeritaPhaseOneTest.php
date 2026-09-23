<?php

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('1. public homepage can be accessed', function () {
    Category::factory()->count(3)->create();

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('JatimNusa');
    $response->assertSee('berita');
});

test('2. admin login page can be accessed by guest', function () {
    $response = $this->get(route('admin.login'));

    $response->assertOk();
    $response->assertSee('Portal Berita CMS');
    $response->assertSee('Masuk ke Dashboard');
});

test('3. guest cannot access admin dashboard and is redirected to login', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('admin.login'));
    $this->assertGuest();
});

test('4. admin with correct credentials can login', function () {
    $admin = User::factory()->create([
        'email' => 'admin@portalberita.test',
        'password' => bcrypt('password123'),
        'role' => 'admin',
    ]);

    $response = $this->post(route('admin.login.store'), [
        'email' => 'admin@portalberita.test',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($admin);
});

test('5. login with invalid credentials fails', function () {
    User::factory()->create([
        'email' => 'admin@portalberita.test',
        'password' => bcrypt('correct-password'),
        'role' => 'admin',
    ]);

    $response = $this->from(route('admin.login'))->post(route('admin.login.store'), [
        'email' => 'admin@portalberita.test',
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect(route('admin.login'));
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('6. admin can logout successfully', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post(route('admin.logout'));

    $response->assertRedirect(route('admin.login'));
    $this->assertGuest();
});

test('7. public registration route is not available', function () {
    $response = $this->get('/register');

    $response->assertNotFound();
});

test('8. non-admin user cannot access admin dashboard', function () {
    $regularUser = User::factory()->nonAdmin()->create();

    $response = $this->actingAs($regularUser)->get(route('admin.dashboard'));

    $response->assertForbidden();
});

test('9. basic model relationships work correctly', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::factory()->create();
    $tags = Tag::factory()->count(2)->create();

    $article = Article::factory()->create([
        'author_id' => $admin->id,
        'category_id' => $category->id,
    ]);
    $article->tags()->attach($tags->pluck('id'));

    // Assert User -> Articles
    expect($admin->articles)->toHaveCount(1)
        ->and($admin->articles->first()->id)->toBe($article->id);

    // Assert Category -> Articles
    expect($category->articles)->toHaveCount(1)
        ->and($category->articles->first()->id)->toBe($article->id);

    // Assert Article -> Author & Category
    expect($article->author->id)->toBe($admin->id)
        ->and($article->category->id)->toBe($category->id);

    // Assert Article <-> Tags
    expect($article->tags)->toHaveCount(2)
        ->and($tags->first()->articles)->toHaveCount(1);
});

test('10. published scope does not return draft articles and query scopes work', function () {
    $draft = Article::factory()->create([
        'status' => ArticleStatus::DRAFT,
        'published_at' => null,
    ]);

    $published1 = Article::factory()->published()->create([
        'view_count' => 100,
        'published_at' => now()->subDay(),
    ]);

    $published2 = Article::factory()->featured()->create([
        'view_count' => 500,
        'published_at' => now(),
    ]);

    $published3 = Article::factory()->trending()->create([
        'view_count' => 300,
        'published_at' => now()->subHours(2),
    ]);

    // Published scope
    $publishedArticles = Article::published()->get();
    expect($publishedArticles->pluck('id'))
        ->toContain($published1->id, $published2->id, $published3->id)
        ->not->toContain($draft->id);

    // Featured scope
    $featuredArticles = Article::featured()->get();
    expect($featuredArticles->pluck('id'))->toContain($published2->id)
        ->not->toContain($published1->id, $draft->id);

    // Trending scope
    $trendingArticles = Article::trending()->get();
    expect($trendingArticles->pluck('id'))->toContain($published3->id)
        ->not->toContain($published1->id, $draft->id);

    // Popular scope (ordered by view_count desc)
    $popularArticles = Article::popular()->get();
    expect($popularArticles->first()->id)->toBe($published2->id);

    // Latest published scope (ordered by published_at desc)
    $latestArticles = Article::latestPublished()->get();
    expect($latestArticles->first()->id)->toBe($published2->id);
});
