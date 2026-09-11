<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

#[Fillable(['tool_id', 'code', 'url', 'anchor', 'is_affiliate', 'sort_order'])]
class ToolLink extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['anchor'];

    protected function casts(): array
    {
        return [
            'is_affiliate' => 'boolean',
            'sort_order' => 'int',
        ];
    }

    /** @return BelongsTo<Tool, $this> */
    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }
}
