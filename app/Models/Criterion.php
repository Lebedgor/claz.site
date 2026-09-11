<?php

namespace App\Models;

use App\Enums\CriterionKind;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'description', 'kind', 'weight', 'sort_order', 'is_active'])]
class Criterion extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['name', 'description'];

    protected function casts(): array
    {
        return [
            'kind' => CriterionKind::class,
            'weight' => 'int',
            'sort_order' => 'int',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsToMany<Tool, $this, ToolCriterion, 'pivot'> */
    public function tools(): BelongsToMany
    {
        return $this->belongsToMany(Tool::class, 'tool_criteria', 'criteria_id', 'tool_id')
            ->withPivot('value')
            ->using(ToolCriterion::class);
    }
}
