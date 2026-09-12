<?php

namespace App\Models;

use App\Models\Concerns\HasPublicSlugRouting;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[Fillable(['parent_id', 'name', 'slug', 'description', 'sort_order'])]
class Category extends Model
{
    use HasPublicSlugRouting;
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['name', 'slug', 'description'];

    protected function casts(): array
    {
        return [
            'parent_id' => 'int',
            'sort_order' => 'int',
        ];
    }

    /** @return BelongsTo<Category, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<Category, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
