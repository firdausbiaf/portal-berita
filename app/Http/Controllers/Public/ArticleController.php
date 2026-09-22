<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Support\ArticleContentSanitizer;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * Display the full article detail page.
     */
    public function show(Article $article): View
    {
        // 1. Draft protection: Only published articles may be viewed publicly
        if (! $article->isPublished()) {
            abort(404);
        }

        // 2. View counter MVP: increment once per browser session per article
        $viewedArticles = session()->get('viewed_articles', []);
        if (! in_array($article->id, $viewedArticles, true)) {
            $article->increment('view_count');
            session()->push('viewed_articles', $article->id);
            $article->view_count++;
        }

        // 3. Eager load relations for reading view
        $article->load(['category', 'author', 'tags']);

        // 4. Defense-in-depth: sanitize article content before render (without mutating DB on GET)
        $safeContent = ArticleContentSanitizer::sanitize($article->content);

        // 5. Related News: Priority 1 is same category, Fallback is shared tags, up to 4 articles
        $related = Article::query()
            ->with('category')
            ->published()
            ->where('id', '!=', $article->id)
            ->where('category_id', $article->category_id)
            ->latestPublished()
            ->take(4)
            ->get();

        $needed = 4 - $related->count();
        $tagIds = $article->tags->pluck('id')->all();

        if ($needed > 0 && ! empty($tagIds)) {
            $tagRelated = Article::query()
                ->with('category')
                ->published()
                ->where('id', '!=', $article->id)
                ->whereNotIn('id', $related->pluck('id'))
                ->whereHas('tags', fn ($query) => $query->whereIn('tags.id', $tagIds))
                ->latestPublished()
                ->take($needed)
                ->get();

            $related = $related->concat($tagRelated);
        }

        // 6. Sidebar datasets (excluding current article)
        $popular = Article::query()
            ->with('category')
            ->published()
            ->where('id', '!=', $article->id)
            ->popular()
            ->take(5)
            ->get();

        $latest = Article::query()
            ->with('category')
            ->published()
            ->where('id', '!=', $article->id)
            ->latestPublished()
            ->take(5)
            ->get();

        $trending = Article::query()
            ->with('category')
            ->published()
            ->where('id', '!=', $article->id)
            ->trending()
            ->latestPublished()
            ->take(5)
            ->get();

        return view('public.articles.show', compact(
            'article',
            'safeContent',
            'related',
            'popular',
            'latest',
            'trending'
        ));
    }
}
