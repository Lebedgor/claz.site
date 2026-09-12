<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[Fillable(['article_id', 'title', 'intro', 'verdict', 'sort_order'])]
class Comparison extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['title', 'intro', 'verdict'];

    protected function casts(): array
    {
        return ['sort_order' => 'int'];
    }

    /** @return BelongsTo<Article, $this> */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /** @return HasMany<ComparisonItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(ComparisonItem::class)->orderBy('position');
    }
}
