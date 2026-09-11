<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use App\Support\TranslatableValue;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Catalog';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-folder';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Content')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('English')->schema([
                        TextInput::make('name.en')->label('Name')->required()->maxLength(255),
                        TextInput::make('slug.en')->label('Slug')->maxLength(255)
                            ->helperText('Leave empty to generate from the name'),
                        Textarea::make('description.en')->label('Description')->rows(3),
                    ]),
                ]),
            Select::make('parent_id')
                ->label('Parent category')
                ->searchable()
                ->options(fn (?Category $record): array => Category::query()
                    ->when($record, fn (Builder $query) => $query->whereKeyNot($record->getKey()))
                    ->orderBy('sort_order')
                    ->get()
                    ->mapWithKeys(fn (Category $category): array => [$category->getKey() => $category->getTranslation('name', 'en')])
                    ->all()),
            TextInput::make('sort_order')->numeric()->default(0)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->formatStateUsing(TranslatableValue::string())
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('name->en', 'like', "%{$search}%")),
                TextColumn::make('slug')->formatStateUsing(TranslatableValue::string()),
                TextColumn::make('parent.name')->formatStateUsing(TranslatableValue::string())->placeholder('—'),
                TextColumn::make('sort_order')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
