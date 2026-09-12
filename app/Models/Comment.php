<?php

namespace App\Models;

use App\Enums\CommentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['article_id', 'parent_id', 'name', 'email', 'body', 'status', 'ip_hash'])]
class Comment extends Model
{
    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
        ];
    }

    /** @param Builder<Comment> $query */
    public function scopeApproved($query): void
    {
        $query->where('status', CommentStatus::Approved->value);
    }

    /** @return BelongsTo<Article, $this> */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /** @return BelongsTo<Comment, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
