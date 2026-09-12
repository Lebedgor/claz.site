<?php

namespace App\Models;

use App\Enums\ToolStatus;
use App\Enums\ToolType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

#[Fillable(['type', 'status', 'name', 'slug', 'vendor', 'description', 'logo', 'rating_avg', 'published_at'])]
class Tool extends Model
{
    use HasTranslations;
    use SoftDeletes;

    /** @var list<string> */
    public array $translatable = ['name', 'slug', 'description'];

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

    protected function casts(): array
    {
        return [
            'type' => ToolType::class,
            'status' => ToolStatus::class,
            'rating_avg' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    /** @return BelongsToMany<Criterion, $this, ToolCriterion, 'pivot'> */
    public function criteria(): BelongsToMany
    {
        return $this->belongsToMany(Criterion::class, 'tool_criteria', 'tool_id', 'criteria_id')
            ->withPivot('value')
            ->using(ToolCriterion::class);
    }

    /** @return HasMany<ToolLink, $this> */
    public function links(): HasMany
    {
        return $this->hasMany(ToolLink::class);
    }
}
