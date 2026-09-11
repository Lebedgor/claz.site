<?php

namespace App\Filament\Resources;

use App\Enums\CriterionKind;
use App\Filament\Resources\CriterionResource\Pages;
use App\Models\Criterion;
use App\Support\TranslatableValue;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CriterionResource extends Resource
{
    protected static ?string $model = Criterion::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Catalog';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-scale';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Content')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('English')->schema([
                        TextInput::make('name.en')->label('Name')->required()->maxLength(255),
                        Textarea::make('description.en')->label('Description')->rows(3),
                    ]),
                ]),
            Select::make('kind')->options(CriterionKind::class)->default(CriterionKind::Score->value)->required(),
            TextInput::make('weight')->numeric()->minValue(0)->default(1)->required(),
            TextInput::make('sort_order')->numeric()->default(0)->required(),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->formatStateUsing(TranslatableValue::string())
                    ->searchable(query: fn ($query, string $search) => $query->where('name->en', 'like', "%{$search}%")),
                TextColumn::make('kind')->badge(),
                TextColumn::make('weight')->sortable(),
                TextColumn::make('sort_order')->sortable(),
                ToggleColumn::make('is_active'),
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
            'index' => Pages\ListCriteria::route('/'),
            'create' => Pages\CreateCriterion::route('/create'),
            'edit' => Pages\EditCriterion::route('/{record}/edit'),
        ];
    }
}
