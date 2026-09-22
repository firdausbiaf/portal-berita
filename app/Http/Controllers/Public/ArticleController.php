<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * Display the minimal article detail bridge page.
     */
    public function show(Article $article): View
    {
        if (! $article->isPublished()) {
            abort(404);
        }

        $article->load(['category', 'author']);

        return view('public.articles.show', compact('article'));
    }
}
