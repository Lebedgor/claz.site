<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

#[Fillable(['tool_a_id', 'tool_b_id', 'intro', 'conclusion', 'is_published'])]
class VsPage extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['intro', 'conclusion'];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'tool_a_id' => 'int',
            'tool_b_id' => 'int',
        ];
    }

    /** @return BelongsTo<Tool, $this> */
    public function toolA(): BelongsTo
    {
        return $this->belongsTo(Tool::class, 'tool_a_id');
    }

    /** @return BelongsTo<Tool, $this> */
    public function toolB(): BelongsTo
    {
        return $this->belongsTo(Tool::class, 'tool_b_id');
    }
}
