<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display the specified category and its published articles.
     */
    public function show(Category $category): View
    {
        $articles = $category->articles()
            ->with(['author', 'category'])
            ->published()
            ->latestPublished()
            ->paginate(12);

        return view('public.categories.show', compact('category', 'articles'));
    }
}
