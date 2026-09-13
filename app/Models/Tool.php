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
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[Fillable(['type', 'status', 'name', 'slug', 'vendor', 'description', 'logo', 'rating_avg', 'published_at'])]
class Tool extends Model implements HasMedia
{
    use HasPublicSlugRouting;
    use HasTranslations;
    use InteractsWithMedia;
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

    public function getLogoUrlAttribute(): ?string
    {
        $logo = trim((string) $this->getRawOriginal('logo'));

        if ($logo === '') {
            return null;
        }

        return str_starts_with($logo, 'http') || str_starts_with($logo, '/storage') ? $logo : asset('storage/'.$logo);
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
