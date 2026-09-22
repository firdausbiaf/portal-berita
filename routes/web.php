<?php

use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Public\ArticleController as PublicArticleController;
use App\Http\Controllers\Public\CategoryController as PublicCategoryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\RobotsController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\Public\TagController as PublicTagController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/cari', [SearchController::class, 'index'])->name('search');
Route::get('/kategori/{category:slug}', [PublicCategoryController::class, 'show'])->name('categories.show');
Route::get('/tag/{tag:slug}', [PublicTagController::class, 'show'])->name('tags.show');
Route::get('/berita/{article:slug}', [PublicArticleController::class, 'show'])->name('articles.show');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [RobotsController::class, 'index'])->name('robots');

/*
|--------------------------------------------------------------------------
| Admin Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

        // Categories CRUD
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Tags CRUD
        Route::resource('tags', TagController::class)->except(['show']);

        // Articles CRUD & Publishing actions
        Route::post('articles/{article}/publish', [ArticleController::class, 'publish'])->name('articles.publish');
        Route::post('articles/{article}/unpublish', [ArticleController::class, 'unpublish'])->name('articles.unpublish');
        Route::resource('articles', ArticleController::class)->except(['show']);
    });
});
