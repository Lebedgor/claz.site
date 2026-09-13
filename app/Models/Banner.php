<?php

namespace App\Models;

use App\Enums\BannerPlacement;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable(['placement', 'title', 'image', 'html', 'url', 'starts_at', 'ends_at', 'is_active', 'sort_order'])]
class Banner extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected function casts(): array
    {
        return [
            'placement' => BannerPlacement::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'sort_order' => 'int',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        $image = trim((string) $this->getRawOriginal('image'));

        if ($image === '') {
            return null;
        }

        return str_starts_with($image, 'http') || str_starts_with($image, '/storage') ? $image : asset('storage/'.$image);
    }

    /** @param Builder<Banner> $query */
    public function scopeActiveFor($query, string $placement): void
    {
        $query
            ->where('placement', $placement)
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('sort_order');
    }
}
