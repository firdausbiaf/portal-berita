<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the public homepage.
     */
    public function index(): View
    {
        // 1. Main Headline: Priority 1 is featured, fallback is latest published
        $headline = Article::query()
            ->with(['category', 'author'])
            ->featured()
            ->latestPublished()
            ->first();

        if (! $headline) {
            $headline = Article::query()
                ->with(['category', 'author'])
                ->latestPublished()
                ->first();
        }

        // 2. Secondary Featured: up to 4 articles, featured only, exclude headline
        $secondaryFeatured = Article::query()
            ->with(['category', 'author'])
            ->featured()
            ->when($headline, fn ($query) => $query->where('id', '!=', $headline->id))
            ->latestPublished()
            ->take(4)
            ->get();

        // 3. Popular News: up to 5 articles, sorted by view_count DESC, published only
        $popular = Article::query()
            ->with(['category'])
            ->popular()
            ->take(5)
            ->get();

        // 4. Trending News: up to 5 articles, editorial (is_trending = true), published only
        $trending = Article::query()
            ->with(['category'])
            ->trending()
            ->latestPublished()
            ->take(5)
            ->get();

        // 5. Latest News: up to 10 articles, published_at DESC, published only
        $latest = Article::query()
            ->with(['category', 'author'])
            ->latestPublished()
            ->take(10)
            ->get();

        // 6. Category Sections: categories with published articles, eager-loading top 4 articles per category
        $categorySections = Category::query()
            ->whereHas('articles', fn ($query) => $query->published())
            ->with(['articles' => fn ($query) => $query->published()->latestPublished()->limit(4)->with(['category', 'author'])])
            ->take(4)
            ->get();

        return view('public.home', compact(
            'headline',
            'secondaryFeatured',
            'popular',
            'trending',
            'latest',
            'categorySections'
        ));
    }
}
