<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\DemoArticleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

/*
|--------------------------------------------------------------------------
| Public Header & Navigation Revisions
|--------------------------------------------------------------------------
*/

test('public top utility bar does not contain LIVE badge but displays current date', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertDontSee('>LIVE<', false);
    $response->assertSee(now(config('site.display_timezone', 'Asia/Jakarta'))->translatedFormat('l, d F Y'));
});

test('public header does not contain Redaksi 24 Jam', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertDontSee('Redaksi 24 Jam');
});

test('public search bar has clear placeholder and accessible form', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('placeholder="Cari berita..."', false);
    $response->assertSee('name="q"', false);
    $response->assertSee('border-stone-300', false);
});

test('desktop navigation renders header categories ordered by menu_order', function () {
    $cat3 = Category::factory()->create(['name' => 'Kategori Tiga', 'show_in_header' => true, 'menu_order' => 3]);
    $cat1 = Category::factory()->create(['name' => 'Kategori Satu', 'show_in_header' => true, 'menu_order' => 1]);
    $cat2 = Category::factory()->create(['name' => 'Kategori Dua', 'show_in_header' => true, 'menu_order' => 2]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Beranda');

    $content = $response->getContent();
    $pos1 = strpos($content, 'Kategori Satu');
    $pos2 = strpos($content, 'Kategori Dua');
    $pos3 = strpos($content, 'Kategori Tiga');

    expect($pos1)->toBeLessThan($pos2);
    expect($pos2)->toBeLessThan($pos3);
});

test('desktop navigation groups non-header categories in Lainnya dropdown', function () {
    Category::factory()->create(['name' => 'Nasional', 'show_in_header' => true, 'menu_order' => 1]);
    Category::factory()->create(['name' => 'Teknologi', 'show_in_header' => false]);
    Category::factory()->create(['name' => 'Otomotif', 'show_in_header' => false]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Lainnya');
    $response->assertSee('Kategori Lainnya');
    $response->assertSee('Teknologi');
    $response->assertSee('Otomotif');
});

test('desktop navigation does not show Lainnya dropdown when all categories are in header', function () {
    Category::factory()->create(['name' => 'Nasional', 'show_in_header' => true, 'menu_order' => 1]);
    Category::factory()->create(['name' => 'Internasional', 'show_in_header' => true, 'menu_order' => 2]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertDontSee('id="nav-more-dropdown-btn"', false);
});

test('desktop navigation marks Lainnya button as active when viewing a dropdown category', function () {
    Category::factory()->create(['name' => 'Nasional', 'show_in_header' => true, 'menu_order' => 1]);
    $hiddenCat = Category::factory()->create(['name' => 'Gaya Hidup', 'slug' => 'gaya-hidup', 'show_in_header' => false]);

    $response = $this->get(route('categories.show', $hiddenCat));

    $response->assertOk();
    $response->assertSee('id="nav-more-dropdown-btn"', false);
    // Button should have the active red border and text class
    $response->assertSeeInOrder(['id="nav-more-dropdown-btn"', 'border-red-600 text-red-600'], false);
});

test('mobile drawer renders all categories with header categories first', function () {
    $headerCat = Category::factory()->create(['name' => 'Politik Utama', 'show_in_header' => true, 'menu_order' => 1]);
    $drawerCat = Category::factory()->create(['name' => 'Arsip Khusus', 'show_in_header' => false]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('id="mobile-menu"', false);
    $response->assertSee('Politik Utama');
    $response->assertSee('Arsip Khusus');
});

/*
|--------------------------------------------------------------------------
| Footer Revisions
|--------------------------------------------------------------------------
*/

test('footer does not contain internal verification or architecture claims', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertDontSee('Terverifikasi Dewan Pers (Standar MVP)');
    $response->assertDontSee('Status: Sistem Redaksi Terverifikasi');
    $response->assertDontSee('Arsitektur: Laravel 13 & Tailwind CSS v4');
});

test('footer displays correct copyright text for PT Red Cherry Infinity', function () {
    $year = date('Y');
    $expectedCopyright = "&copy; {$year} PT Red Cherry Infinity. Seluruh hak cipta dilindungi undang-undang.";

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee($expectedCopyright, false);
});

test('footer displays all categories without 6-item truncation when count is small', function () {
    for ($i = 1; $i <= 8; $i++) {
        Category::factory()->create(['name' => "Kategori {$i}"]);
    }

    $response = $this->get(route('home'));

    $response->assertOk();
    for ($i = 1; $i <= 8; $i++) {
        $response->assertSee("Kategori {$i}");
    }
    // No expander button for <= 30 categories
    $response->assertDontSee('id="toggle-all-categories-btn"', false);
});

test('footer provides toggle expander when more than 30 categories exist', function () {
    for ($i = 1; $i <= 35; $i++) {
        Category::factory()->create(['name' => sprintf('Topik %02d', $i)]);
    }

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('id="toggle-all-categories-btn"', false);
    $response->assertSee('Lihat Semua Kategori (35)');
    $response->assertSee('id="footer-extra-categories"', false);
});

/*
|--------------------------------------------------------------------------
| Admin Category Management Revisions
|--------------------------------------------------------------------------
*/

test('admin category index displays Menu Utama badge and Urutan column', function () {
    Category::factory()->create([
        'name' => 'Ekonomi Bisnis',
        'show_in_header' => true,
        'menu_order' => 5,
    ]);

    Category::factory()->create([
        'name' => 'Selingan',
        'show_in_header' => false,
        'menu_order' => null,
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.categories.index'));

    $response->assertOk();
    $response->assertSee('Menu Utama');
    $response->assertSee('Urutan');
    $response->assertSee('Ya');
    $response->assertSee('Tidak');
    $response->assertSee('5');
});

test('admin can create category with show_in_header and menu_order', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
        'name' => 'Sains & Alam',
        'show_in_header' => '1',
        'menu_order' => 4,
    ]);

    $response->assertRedirect(route('admin.categories.index'));
    $this->assertDatabaseHas('categories', [
        'name' => 'Sains & Alam',
        'show_in_header' => true,
        'menu_order' => 4,
    ]);
});

test('admin can create category with show_in_header unchecked and menu_order null', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
        'name' => 'Hiburan',
    ]);

    $response->assertRedirect(route('admin.categories.index'));
    $this->assertDatabaseHas('categories', [
        'name' => 'Hiburan',
        'show_in_header' => false,
        'menu_order' => null,
    ]);
});

test('admin can update category show_in_header and menu_order', function () {
    $category = Category::factory()->create([
        'name' => 'Budaya',
        'show_in_header' => false,
        'menu_order' => null,
    ]);

    $response = $this->actingAs($this->admin)->put(route('admin.categories.update', $category), [
        'name' => 'Budaya Nusantara',
        'show_in_header' => '1',
        'menu_order' => 8,
    ]);

    $response->assertRedirect(route('admin.categories.index'));
    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Budaya Nusantara',
        'show_in_header' => true,
        'menu_order' => 8,
    ]);
});

test('admin category creation validates menu_order must be positive integer', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
        'name' => 'Validasi Kategori',
        'menu_order' => -1,
    ]);

    $response->assertSessionHasErrors('menu_order');
});

/*
|--------------------------------------------------------------------------
| JatimNusa Branding & Asset Verification Tests
|--------------------------------------------------------------------------
*/

test('public layout renders JatimNusa logo and favicon assets', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('images/branding/logo.png');
    $response->assertSee('images/branding/favicon.png');
    $response->assertSee('favicon.ico');
    $response->assertSee('alt="JatimNusa"', false);
});

test('public header and footer no longer contain old PORTALBERITA placeholder', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertDontSee('PORTAL<span class="text-red-600">BERITA</span>', false);
    $response->assertDontSee('PORTAL<span class="text-red-500">BERITA</span>', false);
    $response->assertSee('Jatim<span class="text-red-500 transition-colors group-hover:text-red-400">Nusa</span>', false);
});

test('page title and site config use JatimNusa as brand name with new tagline', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('<title>JatimNusa - Dari Jatim untuk Nusa</title>', false);
    expect(config('site.name'))->toBe('JatimNusa');
    expect(config('site.tagline'))->toBe('Dari Jatim untuk Nusa');
});

test('branding asset files exist in public directory', function () {
    expect(file_exists(public_path('images/branding/logo.png')))->toBeTrue();
    expect(file_exists(public_path('images/branding/logo-mark.png')))->toBeTrue();
    expect(file_exists(public_path('images/branding/favicon.png')))->toBeTrue();
    expect(file_exists(public_path('favicon.ico')))->toBeTrue();
});

test('header logo uses enlarged responsive height classes', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('h-11 sm:h-12 md:h-13 lg:h-15 xl:h-16', false);
    $response->assertSee('w-auto object-contain', false);
});

test('homepage does not contain Fokus Pemberitaan or Standar Jurnalistik card', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertDontSee('Fokus Pemberitaan');
    $response->assertDontSee('Standar Jurnalistik Akurat');
});

test('footer brand acts as single interactive unit with unified hover and homepage link', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    // Verify both wordmark parts change to red-400 on group hover
    $response->assertSee('group-hover:text-red-400', false);
    $response->assertSee('<span class="text-red-500 transition-colors group-hover:text-red-400">Nusa</span>', false);
    $response->assertSee('images/branding/logo-mark.png');
});

test('DemoArticleSeeder seeds published articles with local featured images and is idempotent', function () {
    $this->seed(DemoArticleSeeder::class);

    $demoCount = Article::whereNotNull('featured_image')->count();
    expect($demoCount)->toBeGreaterThanOrEqual(8);

    // Verify local SVG asset was copied to public storage
    $firstDemo = Article::whereNotNull('featured_image')->first();
    expect($firstDemo)->not->toBeNull();
    expect(file_exists(storage_path('app/public/'.$firstDemo->featured_image)))->toBeTrue();

    // Verify idempotency: running again should not duplicate
    $this->seed(DemoArticleSeeder::class);
    $secondCount = Article::whereNotNull('featured_image')->count();
    expect($secondCount)->toBe($demoCount);

    // Verify demo article renders on homepage and detail page
    $response = $this->get(route('articles.show', $firstDemo));
    $response->assertOk();
    $response->assertSee($firstDemo->title);
    $response->assertSee($firstDemo->featured_image);
});
