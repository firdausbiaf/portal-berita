<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use App\Support\ArticleContentSanitizer;
use Carbon\Carbon;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

#[Fillable([
    'author_id',
    'category_id',
    'title',
    'slug',
    'excerpt',
    'content',
    'featured_image',
    'image_caption',
    'image_alt',
    'status',
    'is_featured',
    'is_trending',
    'view_count',
    'published_at',
])]
class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
            'is_featured' => 'boolean',
            'is_trending' => 'boolean',
            'view_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Get the author of the article.
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the category that owns the article.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The tags that belong to the article.
     *
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_tag');
    }

    /**
     * Scope a query to only include published articles.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ArticleStatus::PUBLISHED);
    }

    /**
     * Scope a query to only include published and featured articles.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->published()->where('is_featured', true);
    }

    /**
     * Scope a query to only include published and trending articles.
     */
    public function scopeTrending(Builder $query): Builder
    {
        return $query->published()->where('is_trending', true);
    }

    /**
     * Scope a query to order published articles with the latest publication date first.
     */
    public function scopeLatestPublished(Builder $query): Builder
    {
        return $query->published()->orderByDesc('published_at');
    }

    /**
     * Scope a query to order published articles by highest view count.
     */
    public function scopePopular(Builder $query): Builder
    {
        return $query->published()->orderByDesc('view_count');
    }

    /**
     * Check if the article is currently published.
     */
    public function isPublished(): bool
    {
        return $this->status === ArticleStatus::PUBLISHED;
    }

    /**
     * Get the published_at Carbon instance or formatted string converted to display timezone.
     */
    public function publishedAtDisplay(?string $format = null, bool $includeTz = true): Carbon|string|null
    {
        if (! $this->published_at) {
            return null;
        }

        $dt = $this->published_at->timezone(config('site.display_timezone', 'Asia/Jakarta'));

        if ($format === null) {
            return $dt;
        }

        $formatted = $dt->translatedFormat($format);

        if ($includeTz) {
            $formatted .= ' WIB';
        }

        return $formatted;
    }

    /**
     * Get dynamic SEO meta description derived from excerpt or sanitized plain text content.
     */
    public function getMetaDescriptionAttribute(): string
    {
        if ($this->excerpt && trim($this->excerpt) !== '') {
            return Str::limit(trim(preg_replace('/\s+/', ' ', $this->excerpt)), 160);
        }

        if ($this->content && trim($this->content) !== '') {
            $plain = strip_tags(ArticleContentSanitizer::sanitize($this->content) ?? '');
            $clean = trim(preg_replace('/\s+/', ' ', $plain));
            if ($clean !== '') {
                return Str::limit($clean, 160);
            }
        }

        return config('site.description', 'Berita terbaru dari '.config('site.name', 'Portal Berita'));
    }
}
