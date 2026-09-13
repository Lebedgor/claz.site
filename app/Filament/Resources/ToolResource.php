<?php

namespace App\Filament\Resources;

use App\Enums\ToolStatus;
use App\Enums\ToolType;
use App\Filament\Forms\Components\MediaPickerField;
use App\Filament\Resources\ToolResource\Pages;
use App\Filament\Resources\ToolResource\RelationManagers\CriteriaRelationManager;
use App\Models\Tool;
use App\Support\TranslatableValue;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ToolResource extends Resource
{
    protected static ?string $model = Tool::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Catalog';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cube';

    public static function resolveRecordRouteBinding(int|string $key, ?\Closure $modifyQuery = null): ?Model
    {
        $query = static::getRecordRouteBindingEloquentQuery();

        if ($modifyQuery) {
            $query = $modifyQuery($query) ?? $query;
        }

        $field = is_numeric($key) ? null : 'slug->'.app()->getLocale();

        return app(static::getModel())
            ->resolveRouteBindingQuery($query, $key, $field)
            ->first();
    }

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
                        Textarea::make('description.en')->label('Description')->rows(4),
                    ]),
                ]),
            Select::make('type')->options(ToolType::class)->default(ToolType::Plugin->value)->required(),
            Select::make('status')->options(ToolStatus::class)->default(ToolStatus::Draft->value)->required(),
            TextInput::make('vendor')->maxLength(255),
            TextInput::make('rating_avg')
                ->label('Overall rating')
                ->numeric()
                ->minValue(0)
                ->maxValue(10)
                ->step(0.1)
                ->helperText('Editorial rating 0–10. Used for catalog sorting and the tool page badge. Not linked to comparison table scores.'),
            MediaPickerField::make('logo')
                ->label('Logo')
                ->disk('public'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->formatStateUsing(TranslatableValue::string())
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('name->en', 'like', "%{$search}%")),
                TextColumn::make('slug')
                    ->formatStateUsing(TranslatableValue::string())
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('slug->en', 'like', "%{$search}%")),
                TextColumn::make('type')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('rating_avg')->sortable()->placeholder('—'),
                TextColumn::make('published_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')->options(ToolType::class),
                SelectFilter::make('status')->options(ToolStatus::class),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CriteriaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTools::route('/'),
            'create' => Pages\CreateTool::route('/create'),
            'edit' => Pages\EditTool::route('/{record}/edit'),
        ];
    }
}
