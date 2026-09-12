<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasPublicSlugRouting
{
    public function resolveRouteBinding($value, $field = null)
    {
        $column = $field ?? (is_numeric($value) ? $this->getKeyName() : 'slug->'.app()->getLocale());

        return $this->where($column, $value)->first();
    }

    public function getRouteKey(): mixed
    {
        $route = app()->bound('request') ? request()->route() : null;

        if ($route === null || Str::startsWith(strval($route->getName()), 'filament.')) {
            return $this->getKey();
        }

        $locale = app()->getLocale();
        $slug = $this->getTranslation('slug', $locale);

        return is_array($slug) ? strval($slug[$locale] ?? '') : strval($slug);
    }
}
