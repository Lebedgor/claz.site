<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VsPageResource\Pages;
use App\Models\Tool;
use App\Models\VsPage;
use App\Support\TranslatableValue;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class VsPageResource extends Resource
{
    protected static ?string $model = VsPage::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Content';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-arrows-right-left';

    public static function form(Schema $schema): Schema
    {
        $toolOptions = fn (): array => Tool::query()
            ->where('status', 'published')
            ->orderBy('name->en')
            ->get()
            ->mapWithKeys(fn (Tool $tool): array => [$tool->getKey() => $tool->getTranslation('name', 'en')])
            ->all();

        return $schema->components([
            Hidden::make('id')->dehydrated(false),
            Select::make('tool_a_id')->label('Tool A')->searchable()->preload()->options($toolOptions)->required(),
            Select::make('tool_b_id')
                ->label('Tool B')
                ->searchable()
                ->preload()
                ->options($toolOptions)
                ->required()
                ->rules([
                    fn (string $operation, ?VsPage $record, Get $get): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($get, $operation, $record): void {
                        if (! is_numeric($value) || ! is_numeric($get('tool_a_id'))) {
                            return;
                        }

                        if ((int) $value === (int) $get('tool_a_id')) {
                            $fail('Pick two different tools.');

                            return;
                        }

                        $exists = VsPage::query()
                            ->where(function ($query) use ($value, $get): void {
                                $query
                                    ->where(fn ($q) => $q->where('tool_a_id', $get('tool_a_id'))->where('tool_b_id', $value))
                                    ->orWhere(fn ($q) => $q->where('tool_a_id', $value)->where('tool_b_id', $get('tool_a_id')));
                            })
                            ->when($operation === 'edit' && $record !== null, fn ($query) => $query->whereKeyNot($record->getKey()))
                            ->exists();

                        if ($exists) {
                            $fail('A page for this pair already exists.');
                        }
                    },
                ]),
            Tabs::make('Editorial')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('English')->schema([
                        Textarea::make('intro.en')->label('Intro (optional)')->rows(3),
                        Textarea::make('conclusion.en')->label('Conclusion (optional)')->rows(3),
                    ]),
                ]),
            Toggle::make('is_published')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('toolA.name')->formatStateUsing(TranslatableValue::string()),
                TextColumn::make('toolB.name')->formatStateUsing(TranslatableValue::string()),
                ToggleColumn::make('is_published'),
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
            'index' => Pages\ListVsPages::route('/'),
            'create' => Pages\CreateVsPage::route('/create'),
            'edit' => Pages\EditVsPage::route('/{record}/edit'),
        ];
    }
}
