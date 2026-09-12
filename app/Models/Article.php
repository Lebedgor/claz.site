<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'category_id', 'title', 'slug', 'excerpt', 'body_html', 'cover', 'status',
    'published_at', 'reading_time', 'meta_title', 'meta_description',
])]
class Article extends Model
{
    use HasTranslations;
    use SoftDeletes;

    /** @var list<string> */
    public array $translatable = ['title', 'slug', 'excerpt', 'body_html', 'meta_title', 'meta_description'];

    public function resolveRouteBinding($value, $field = null)
    {
        $column = $field ?? (is_numeric($value) ? $this->getKeyName() : 'slug->'.app()->getLocale());

        return $this->where($column, $value)->first();
    }

    public function getRouteKey(): mixed
    {
        $locale = app()->getLocale();
        $slug = $this->getTranslation('slug', $locale);

        return is_array($slug) ? strval($slug[$locale] ?? '') : strval($slug);
    }

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
            'published_at' => 'datetime',
            'reading_time' => 'int',
        ];
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
