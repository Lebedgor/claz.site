<?php

namespace App\Support;

use Illuminate\Support\Str;

class Slugger
{
    public static function generate(string $value, string $locale = 'en'): string
    {
        return Str::slug($value, language: $locale);
    }

    public static function unique(string $model, string $locale, string $base, ?int $ignoreId = null): string
    {
        $slug = static::generate($base, $locale);
        $suffix = 1;

        $query = $model::query();

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        while ((clone $query)->where("slug->{$locale}", $slug)->exists()) {
            $slug = static::generate($base, $locale).'-'.++$suffix;
        }

        return $slug;
    }
}
