<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[Fillable(['comparison_id', 'tool_id', 'position', 'score', 'verdict'])]
class ComparisonItem extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['verdict'];

    protected function casts(): array
    {
        return [
            'position' => 'int',
            'score' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Comparison, $this> */
    public function comparison(): BelongsTo
    {
        return $this->belongsTo(Comparison::class);
    }

    /** @return BelongsTo<Tool, $this> */
    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    /** @return HasMany<ComparisonScore, $this> */
    public function scores(): HasMany
    {
        return $this->hasMany(ComparisonScore::class)->orderBy('sort_order');
    }
}
