<?php

namespace App\Models;

use App\Enums\ToolStatus;
use App\Enums\ToolType;
use App\Models\Concerns\HasPublicSlugRouting;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

#[Fillable(['type', 'status', 'name', 'slug', 'vendor', 'description', 'logo', 'rating_avg', 'published_at'])]
class Tool extends Model
{
    use HasPublicSlugRouting;
    use HasTranslations;
    use SoftDeletes;

    /** @var list<string> */
    public array $translatable = ['name', 'slug', 'description'];

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
