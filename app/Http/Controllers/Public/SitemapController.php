<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate dynamic XML sitemap for public published content.
     */
    public function index(): Response
    {
        $articles = Article::query()
            ->published()
            ->select(['id', 'slug', 'updated_at', 'published_at'])
            ->latestPublished()
            ->get();

        $categories = Category::query()
            ->whereHas('articles', fn ($query) => $query->published())
            ->select(['id', 'slug', 'updated_at'])
            ->get();

        $tags = Tag::query()
            ->whereHas('articles', fn ($query) => $query->published())
            ->select(['id', 'slug', 'updated_at'])
            ->get();

        $content = view('public.sitemap', [
            'articles' => $articles,
            'categories' => $categories,
            'tags' => $tags,
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'text/xml; charset=utf-8',
        ]);
    }
}
