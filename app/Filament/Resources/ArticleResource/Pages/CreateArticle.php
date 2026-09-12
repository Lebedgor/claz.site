<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use App\Models\Article;
use App\Support\ReadingTime;
use App\Support\Slugger;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['slug']['en'] ?? null)) {
            $data['slug']['en'] = Slugger::unique(Article::class, 'en', strval($data['title']['en'] ?? ''));
        }

        $data['reading_time'] = ReadingTime::estimate($data['body_html']['en'] ?? null);

        return $data;
    }
}
