<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

#[Fillable(['comparison_item_id', 'criteria_id', 'value', 'note', 'sort_order'])]
class ComparisonScore extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['note'];

    protected function casts(): array
    {
        return ['sort_order' => 'int'];
    }

    /** @return BelongsTo<ComparisonItem, $this> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(ComparisonItem::class, 'comparison_item_id');
    }

    /** @return BelongsTo<Criterion, $this> */
    public function criterion(): BelongsTo
    {
        return $this->belongsTo(Criterion::class, 'criteria_id');
    }
}
