<?php

namespace App\Support;

use Closure;

class TranslatableValue
{
    public static function string(): Closure
    {
        return static fn (mixed $state): string => is_array($state)
            ? strval($state['en'] ?? '')
            : strval($state ?? '');
    }
}
