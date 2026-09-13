<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use App\Enums\EditorMode;
use App\Models\Concerns\HasPublicSlugRouting;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'category_id', 'title', 'slug', 'excerpt', 'body_html', 'cover', 'status',
    'published_at', 'reading_time', 'meta_title', 'meta_description',
])]
class Article extends Model implements HasMedia
{
    use HasPublicSlugRouting;
    use HasTranslations;
    use InteractsWithMedia;
    use SoftDeletes;

    /** @var list<string> */
    public array $translatable = ['title', 'slug', 'excerpt', 'body_html', 'meta_title', 'meta_description'];

    /** @param Builder<Article> $query */
    public function scopePublished($query): void
    {
        $query
            ->where('status', ArticleStatus::Published->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
            'editor_mode' => EditorMode::class,
            'published_at' => 'datetime',
            'reading_time' => 'int',
        ];
    }

    public function getCoverUrlAttribute(): ?string
    {
        $cover = trim((string) $this->getRawOriginal('cover'));

        if ($cover === '') {
            return null;
        }

        return str_starts_with($cover, 'http') || str_starts_with($cover, '/storage') ? $cover : '/storage/'.ltrim($cover, '/');
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /** @return HasMany<Comparison, $this> */
    public function comparisons(): HasMany
    {
        return $this->hasMany(Comparison::class);
    }

    /** @return HasMany<Comment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
