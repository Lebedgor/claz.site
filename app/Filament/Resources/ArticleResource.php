<?php

namespace App\Filament\Resources;

use App\Enums\ArticleStatus;
use App\Enums\EditorMode;
use App\Filament\Forms\Components\MediaPickerField;
use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Tool;
use App\Support\TranslatableValue;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Content';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Content')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('English')->schema([
                        TextInput::make('title.en')->label('Title')->required()->maxLength(255),
                        TextInput::make('slug.en')->label('Slug')->maxLength(255)
                            ->helperText('Leave empty to generate from the title'),
                        Textarea::make('excerpt.en')->label('Excerpt')->rows(3),
                        Radio::make('editor_mode')
                            ->label('Body editor')
                            ->options([
                                EditorMode::Tiptap->value => 'TipTap (visual)',
                                EditorMode::Html->value => 'Custom HTML (source)',
                            ])
                            ->default(EditorMode::Tiptap->value)
                            ->live()
                            ->afterStateUpdated(function ($state, $old, Set $set, Get $get, Component $component): void {
                                if ($old === EditorMode::Html->value) {
                                    if (filled($get('body_html_src'))) {
                                        $set('body_tiptap', strval($get('body_html_src')));
                                    }

                                    return;
                                }

                                $richEditor = $component->getContainer()->getComponentByStatePath('body_tiptap', true);

                                if ($richEditor instanceof RichEditor && is_array($body = $get('body_tiptap'))) {
                                    $editor = $richEditor->getTipTapEditor();
                                    $editor->setContent($body);
                                    $set('body_html_src', (string) $editor->getHtml());

                                    return;
                                }

                                if (filled($body = $get('body_tiptap'))) {
                                    $set('body_html_src', strval($body));
                                }
                            })
                            ->columnSpanFull(),
                        RichEditor::make('body_tiptap')
                            ->label('Body')
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => ($get('editor_mode') ?? EditorMode::Tiptap->value) === EditorMode::Tiptap->value)
                            ->afterStateHydrated(function (RichEditor $component, ?Article $record): void {
                                if ($record === null) {
                                    return;
                                }

                                $component->state($record->getTranslation('body_html', app()->getLocale()));
                            })
                            ->toolbarButtons([
                                'attachFiles',
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'h2',
                                'h3',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'table',
                                'undo',
                                'underline',
                            ]),
                        CodeEditor::make('body_html_src')
                            ->label('Body (HTML)')
                            ->columnSpanFull()
                            ->language(Language::Html)
                            ->visible(fn (Get $get): bool => ($get('editor_mode') ?? EditorMode::Tiptap->value) === EditorMode::Html->value)
                            ->afterStateHydrated(function (CodeEditor $component, ?Article $record): void {
                                if ($record === null) {
                                    return;
                                }

                                $component->state($record->getTranslation('body_html', app()->getLocale()));
                            }),
                    ]),
                    Tab::make('SEO')->schema([
                        TextInput::make('meta_title.en')->label('Meta title')->maxLength(255),
                        Textarea::make('meta_description.en')->label('Meta description')->rows(2),
                    ]),
                ]),
            Select::make('category_id')
                ->label('Category')
                ->searchable()
                ->options(fn (): array => Category::query()
                    ->orderBy('sort_order')
                    ->get()
                    ->mapWithKeys(fn (Category $category): array => [$category->getKey() => $category->getTranslation('name', 'en')])
                    ->all()),
            Select::make('tags')
                ->label('Tags')
                ->multiple()
                ->preload()
                ->relationship('tags', 'name')
                ->getOptionLabelFromRecordUsing(fn (Tag $record): string => $record->getTranslation('name', 'en')),
            MediaPickerField::make('cover')
                ->label('Cover image')
                ->disk('public'),
            Select::make('status')->options(ArticleStatus::class)->default(ArticleStatus::Draft->value)->required(),
            DateTimePicker::make('published_at'),
            Section::make('Comparison blocks')
                ->columnSpanFull()
                ->description('Published tables use a snapshot of tool values. Use "Sync scores" on the article page after changing tool cards.')
                ->visible(fn (string $operation): bool => $operation === 'edit')
                ->schema([
                    Repeater::make('comparisons')
                        ->relationship()
                        ->label('Blocks')
                        ->itemLabel(fn (array $state): string => strval($state['title']['en'] ?? 'New block'))
                        ->collapsible()
                        ->collapsed()
                        ->reorderable()
                        ->orderColumn('sort_order')
                        ->schema([
                            TextInput::make('title.en')->label('Title')->maxLength(255),
                            Textarea::make('intro.en')->label('Intro')->rows(2),
                            Textarea::make('verdict.en')->label('Verdict')->rows(2),
                            Repeater::make('items')
                                ->relationship()
                                ->label('Tools')
                                ->itemLabel(fn (array $state): string => strval(Tool::find($state['tool_id'] ?? null)?->getTranslation('name', 'en') ?? 'New item'))
                                ->collapsible()
                                ->collapsed()
                                ->reorderable()
                                ->orderColumn('position')
                                ->schema([
                                    Select::make('tool_id')
                                        ->label('Tool')
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->required()
                                        ->options(fn (): array => Tool::query()
                                            ->orderBy('name->en')
                                            ->get()
                                            ->mapWithKeys(fn (Tool $tool): array => [$tool->getKey() => $tool->getTranslation('name', 'en')])
                                            ->all()),
                                    TextInput::make('score')->label('Overall score (0-10)')->numeric()->minValue(0)->maxValue(10),
                                    TextInput::make('verdict.en')->label('Verdict')->maxLength(255),
                                ]),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->formatStateUsing(TranslatableValue::string())
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('title->en', 'like', "%{$search}%")),
                TextColumn::make('category.name')->formatStateUsing(TranslatableValue::string())->placeholder('—'),
                TextColumn::make('status')->badge(),
                TextColumn::make('reading_time')->label('Min')->suffix(' min'),
                TextColumn::make('published_at')->dateTime()->sortable()->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                EditAction::make(),
                self::publishAction(),
                self::unpublishAction(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function publishAction(): Action
    {
        return Action::make('publish')
            ->label('Publish')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (?Article $record): bool => $record?->status === ArticleStatus::Draft)
            ->action(function (Article $record): void {
                $record->update([
                    'status' => ArticleStatus::Published,
                    'published_at' => $record->published_at ?? now(),
                ]);
            });
    }

    public static function unpublishAction(): Action
    {
        return Action::make('unpublish')
            ->label('Unpublish')
            ->icon('heroicon-o-arrow-path')
            ->color('gray')
            ->visible(fn (?Article $record): bool => $record?->status === ArticleStatus::Published)
            ->action(function (Article $record): void {
                $record->update(['status' => ArticleStatus::Draft]);
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
