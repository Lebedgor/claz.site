<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'slug'])]
class Tag extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['name', 'slug'];
}
