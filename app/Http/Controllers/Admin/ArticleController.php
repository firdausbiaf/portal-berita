<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreArticleRequest;
use App\Http\Requests\Admin\UpdateArticleRequest;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Support\ArticleContentSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * Display a listing of articles.
     */
    public function index(Request $request): View
    {
        $query = Article::query()->with(['category', 'author']);

        // Search by title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $articles = $query->orderByDesc('updated_at')->paginate(15)->withQueryString();
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.articles.index', compact('articles', 'categories'));
    }

    /**
     * Show the form for creating a new article.
     */
    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $tags = Tag::query()->orderBy('name')->get();

        return view('admin.articles.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created article in storage.
     */
    public function store(StoreArticleRequest $request): RedirectResponse
    {
        // 1. Slug generation
        $slug = $request->filled('slug')
            ? Str::slug($request->slug)
            : Str::slug($request->title);

        $originalSlug = $slug;
        $counter = 2;
        while (Article::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        // 2. Featured Image Upload
        $featuredImagePath = null;
        if ($request->hasFile('featured_image')) {
            $featuredImagePath = $request->file('featured_image')->store('articles', 'public');
        }

        // 3. Publishing Workflow
        $action = $request->input('action', 'draft');
        if ($action === 'publish' || $request->input('status') === 'published') {
            $status = ArticleStatus::PUBLISHED;
            $publishedAt = now();
            $message = 'Berita berhasil dipublish.';
        } else {
            $status = ArticleStatus::DRAFT;
            $publishedAt = null;
            $message = 'Berita berhasil disimpan sebagai draft.';
        }

        // 4. Sanitize content
        $content = $request->filled('content')
            ? ArticleContentSanitizer::sanitize($request->content)
            : null;

        // 5. Create Article
        $article = Article::create([
            'author_id' => Auth::id(),
            'category_id' => $request->category_id,
            'title' => $request->title,
            'slug' => $slug,
            'excerpt' => $request->excerpt,
            'content' => $content,
            'featured_image' => $featuredImagePath,
            'image_caption' => $request->image_caption,
            'image_alt' => $request->image_alt,
            'status' => $status,
            'is_featured' => $request->boolean('is_featured'),
            'is_trending' => $request->boolean('is_trending'),
            'view_count' => 0,
            'published_at' => $publishedAt,
        ]);

        // 6. Sync Tags
        if ($request->has('tags')) {
            $article->tags()->sync($request->tags);
        }

        return redirect()->route('admin.articles.index')->with('success', $message);
    }

    /**
     * Show the form for editing the specified article.
     */
    public function edit(Article $article): View
    {
        $article->load('tags');
        $categories = Category::query()->orderBy('name')->get();
        $tags = Tag::query()->orderBy('name')->get();

        return view('admin.articles.edit', compact('article', 'categories', 'tags'));
    }

    /**
     * Update the specified article in storage.
     */
    public function update(UpdateArticleRequest $request, Article $article): RedirectResponse
    {
        // 1. Slug handling: If slug not manually specified, PRESERVE existing slug!
        if ($request->filled('slug')) {
            $slug = Str::slug($request->slug);
            $originalSlug = $slug;
            $counter = 2;
            while (Article::where('slug', $slug)->where('id', '!=', $article->id)->exists()) {
                $slug = "{$originalSlug}-{$counter}";
                $counter++;
            }
        } else {
            $slug = $article->slug;
        }

        // 2. Featured Image Replacement
        $featuredImagePath = $article->featured_image;
        if ($request->hasFile('featured_image')) {
            // Delete old image if exists
            if ($featuredImagePath && Storage::disk('public')->exists($featuredImagePath)) {
                Storage::disk('public')->delete($featuredImagePath);
            }
            $featuredImagePath = $request->file('featured_image')->store('articles', 'public');
        }

        // 3. Publishing Workflow
        $action = $request->input('action');
        if ($action === 'unpublish') {
            $status = ArticleStatus::DRAFT;
            $publishedAt = null;
            $message = 'Berita berhasil di-unpublish.';
        } elseif ($action === 'publish') {
            $status = ArticleStatus::PUBLISHED;
            $publishedAt = $article->published_at ?? now();
            $message = 'Berita berhasil dipublish.';
        } elseif ($action === 'draft') {
            $status = ArticleStatus::DRAFT;
            $publishedAt = null;
            $message = 'Berita berhasil disimpan sebagai draft.';
        } else {
            // Default update (preserve status and publication time)
            $status = $article->status;
            $publishedAt = $article->published_at;
            $message = 'Berita berhasil diperbarui.';
        }

        // 4. Sanitize content
        $content = $request->filled('content')
            ? ArticleContentSanitizer::sanitize($request->content)
            : $article->content;

        // 5. Update Article
        $article->update([
            'category_id' => $request->category_id,
            'title' => $request->title,
            'slug' => $slug,
            'excerpt' => $request->excerpt,
            'content' => $content,
            'featured_image' => $featuredImagePath,
            'image_caption' => $request->image_caption,
            'image_alt' => $request->image_alt,
            'status' => $status,
            'is_featured' => $request->boolean('is_featured'),
            'is_trending' => $request->boolean('is_trending'),
            'published_at' => $publishedAt,
        ]);

        // 6. Sync Tags
        $article->tags()->sync($request->tags ?? []);

        return redirect()->route('admin.articles.index')->with('success', $message);
    }

    /**
     * Remove the specified article from storage.
     */
    public function destroy(Article $article): RedirectResponse
    {
        // 1. Delete featured image from storage
        if ($article->featured_image && Storage::disk('public')->exists($article->featured_image)) {
            Storage::disk('public')->delete($article->featured_image);
        }

        // 2. Detach tags
        $article->tags()->detach();

        // 3. Delete article
        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', 'Berita berhasil dihapus.');
    }

    /**
     * Quick publish action.
     */
    public function publish(Article $article): RedirectResponse
    {
        $article->update([
            'status' => ArticleStatus::PUBLISHED,
            'published_at' => $article->published_at ?? now(),
        ]);

        return back()->with('success', 'Berita berhasil dipublish.');
    }

    /**
     * Quick unpublish action.
     */
    public function unpublish(Article $article): RedirectResponse
    {
        $article->update([
            'status' => ArticleStatus::DRAFT,
            'published_at' => null,
        ]);

        return back()->with('success', 'Berita berhasil di-unpublish.');
    }
}
