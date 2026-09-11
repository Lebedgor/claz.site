<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TagResource\Pages;
use App\Models\Tag;
use App\Support\TranslatableValue;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Catalog';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-tag';

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
                    ]),
                ]),
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
            ])
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
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }
}
