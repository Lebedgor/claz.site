<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Actions\SyncComparisonScores;
use App\Filament\Resources\ArticleResource;
use App\Models\Article;
use App\Support\ReadingTime;
use App\Support\Slugger;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        $article = Article::query()->findOrFail((int) $this->record->getKey());

        return [
            ArticleResource::publishAction()->record($article),
            ArticleResource::unpublishAction()->record($article),
            Action::make('syncScores')
                ->label('Sync scores')
                ->icon('heroicon-o-arrow-path')
                ->record($article)
                ->visible(fn (?Article $record): bool => $record !== null && $record->comparisons()->exists())
                ->action(function (Article $record): void {
                    app(SyncComparisonScores::class)->execute($record->refresh());

                    Notification::make()
                        ->title('Comparison scores synced with live tool data')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (blank($data['slug']['en'] ?? null)) {
            $article = Article::query()->findOrFail((int) $this->record->getKey());
            $title = strval($data['title']['en'] ?? $article->getTranslation('title', 'en'));
            $data['slug']['en'] = Slugger::unique(Article::class, 'en', $title, (int) $article->getKey());
        }

        $data['reading_time'] = ReadingTime::estimate($data['body_html']['en'] ?? null);

        return $data;
    }
}
