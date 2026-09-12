<?php

namespace App\Filament\Resources;

use App\Enums\ArticleStatus;
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
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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
                        RichEditor::make('body_html.en')->label('Body')->columnSpanFull()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('uploads/editor')
                            ->fileAttachmentsVisibility('public')
                            ->fileAttachmentsAcceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->fileAttachmentsMaxSize(4096)
                            ->toolbarButtons([
                                'attachFiles', 'bold', 'italic', 'underline', 'strike', 'subscript', 'superscript',
                                'h2', 'h3', 'alignStart', 'alignCenter', 'alignEnd', 'blockquote', 'codeBlock',
                                'bulletList', 'orderedList', 'link', 'horizontalRule', 'table', 'clearFormatting',
                                'image-width-25', 'image-width-50', 'image-width-75', 'image-width-100',
                                'undo', 'redo',
                            ])
                            ->tools([
                                RichEditorTool::make('image-width-25')
                                    ->label('Image width 25%')
                                    ->jsHandler('(() => { const editor = $getEditor(); if (!editor) return; const sel = editor.state.selection; let pos = null; if (sel.node && sel.node.type.name === "image") { pos = sel.from; } else { editor.state.doc.nodesBetween(Math.max(0, sel.from - 2), sel.from + 2, (node, p) => { if (pos === null && node.type.name === "image") pos = p; }); } if (pos === null) return; editor.chain().focus().setNodeSelection(pos).updateAttributes("image", { width: "25%" }).run(); })()')
                                    ->icon(Heroicon::ArrowsPointingIn),
                                RichEditorTool::make('image-width-50')
                                    ->label('Image width 50%')
                                    ->jsHandler('(() => { const editor = $getEditor(); if (!editor) return; const sel = editor.state.selection; let pos = null; if (sel.node && sel.node.type.name === "image") { pos = sel.from; } else { editor.state.doc.nodesBetween(Math.max(0, sel.from - 2), sel.from + 2, (node, p) => { if (pos === null && node.type.name === "image") pos = p; }); } if (pos === null) return; editor.chain().focus().setNodeSelection(pos).updateAttributes("image", { width: "50%" }).run(); })()')
                                    ->icon(Heroicon::ArrowsPointingOut),
                                RichEditorTool::make('image-width-75')
                                    ->label('Image width 75%')
                                    ->jsHandler('(() => { const editor = $getEditor(); if (!editor) return; const sel = editor.state.selection; let pos = null; if (sel.node && sel.node.type.name === "image") { pos = sel.from; } else { editor.state.doc.nodesBetween(Math.max(0, sel.from - 2), sel.from + 2, (node, p) => { if (pos === null && node.type.name === "image") pos = p; }); } if (pos === null) return; editor.chain().focus().setNodeSelection(pos).updateAttributes("image", { width: "75%" }).run(); })()')
                                    ->icon(Heroicon::Bars3),
                                RichEditorTool::make('image-width-100')
                                    ->label('Image width 100%')
                                    ->jsHandler('(() => { const editor = $getEditor(); if (!editor) return; const sel = editor.state.selection; let pos = null; if (sel.node && sel.node.type.name === "image") { pos = sel.from; } else { editor.state.doc.nodesBetween(Math.max(0, sel.from - 2), sel.from + 2, (node, p) => { if (pos === null && node.type.name === "image") pos = p; }); } if (pos === null) return; editor.chain().focus().setNodeSelection(pos).updateAttributes("image", { width: "100%" }).run(); })()')
                                    ->icon(Heroicon::ArrowsRightLeft),
                            ]),
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
            FileUpload::make('cover')
                ->image()
                ->disk('public')
                ->directory('covers')
                ->maxSize(4096),
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
