<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable(['tool_id', 'criteria_id', 'value'])]
class ToolCriterion extends Pivot
{
    protected $table = 'tool_criteria';

    public $timestamps = true;

    protected function casts(): array
    {
        return [];
    }
}
