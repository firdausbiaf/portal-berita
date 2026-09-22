<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\View\View;

class TagController extends Controller
{
    /**
     * Display the specified tag archive and its published articles.
     */
    public function show(Tag $tag): View
    {
        $articles = $tag->articles()
            ->with(['author', 'category'])
            ->published()
            ->latestPublished()
            ->paginate(12);

        return view('public.tags.show', compact('tag', 'articles'));
    }
}
