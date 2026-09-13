<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Enums\EditorMode;
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

        $body = ($data['editor_mode'] ?? EditorMode::Tiptap->value) === EditorMode::Html->value
            ? ($data['body_html_src'] ?? null)
            : ($data['body_tiptap'] ?? null);

        if (is_array($body)) {
            $body = null;
        }

        $data['body_html'] = ['en' => $body];
        $data['reading_time'] = ReadingTime::estimate($body);
        unset($data['body_tiptap'], $data['body_html_src']);

        return $data;
    }
}
