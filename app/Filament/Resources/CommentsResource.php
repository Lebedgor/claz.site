<?php

namespace App\Filament\Resources;

use App\Enums\CommentStatus;
use App\Filament\Resources\CommentsResource\Pages;
use App\Models\Comment;
use App\Support\TranslatableValue;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CommentsResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Content';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('article.title')
                    ->formatStateUsing(TranslatableValue::string())
                    ->limit(40),
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('body')->limit(60),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(CommentStatus::class),
            ])
            ->actions([
                self::approveAction(),
                self::rejectAction(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('approve')
                    ->label('Approve selected')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function (Collection $records): void {
                        foreach ($records as $record) {
                            if ($record instanceof Comment) {
                                $record->update(['status' => CommentStatus::Approved]);
                            }
                        }
                    }),
                BulkAction::make('reject')
                    ->label('Reject selected')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->action(function (Collection $records): void {
                        foreach ($records as $record) {
                            if ($record instanceof Comment) {
                                $record->update(['status' => CommentStatus::Rejected]);
                            }
                        }
                    }),
                DeleteBulkAction::make(),
            ]);
    }

    public static function approveAction(): Action
    {
        return Action::make('approve')
            ->label('Approve')
            ->icon('heroicon-o-check')
            ->color('success')
            ->visible(fn (Comment $record): bool => $record->status === CommentStatus::Pending)
            ->action(fn (Comment $record) => $record->update(['status' => CommentStatus::Approved]));
    }

    public static function rejectAction(): Action
    {
        return Action::make('reject')
            ->label('Reject')
            ->icon('heroicon-o-x-mark')
            ->color('danger')
            ->visible(fn (Comment $record): bool => $record->status === CommentStatus::Pending)
            ->action(fn (Comment $record) => $record->update(['status' => CommentStatus::Rejected]));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComments::route('/'),
        ];
    }
}
