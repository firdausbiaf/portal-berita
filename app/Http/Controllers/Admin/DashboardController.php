<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with summary metrics and recently managed articles.
     */
    public function index(): View
    {
        $stats = [
            'total_articles' => Article::count(),
            'published_articles' => Article::where('status', ArticleStatus::PUBLISHED)->count(),
            'draft_articles' => Article::where('status', ArticleStatus::DRAFT)->count(),
            'featured_articles' => Article::where('is_featured', true)->count(),
            'trending_articles' => Article::where('is_trending', true)->count(),
            'total_categories' => Category::count(),
            'total_tags' => Tag::count(),
        ];

        $recentArticles = Article::query()
            ->with(['category'])
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentArticles'));
    }
}
