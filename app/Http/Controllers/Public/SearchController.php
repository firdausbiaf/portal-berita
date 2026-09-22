<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Display search results matching article titles.
     */
    public function index(Request $request): View
    {
        $query = trim((string) $request->input('q', ''));
        if (mb_strlen($query) > 100) {
            $query = mb_substr($query, 0, 100);
        }

        $articles = null;

        if ($query !== '') {
            $articles = Article::query()
                ->with(['category', 'author'])
                ->published()
                ->where('title', 'like', '%'.$query.'%')
                ->latestPublished()
                ->paginate(12)
                ->withQueryString();
        }

        return view('public.search.index', [
            'query' => $query,
            'articles' => $articles,
        ]);
    }
}
