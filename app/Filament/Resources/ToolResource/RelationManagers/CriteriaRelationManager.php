<?php

namespace App\Filament\Resources\ToolResource\RelationManagers;

use App\Support\TranslatableValue;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CriteriaRelationManager extends RelationManager
{
    protected static string $relationship = 'criteria';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('value')
                ->label('Value')
                ->helperText('Score criteria: number 0–10. Boolean criteria: true / false. Text criteria: free text.')
                ->maxLength(255),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->formatStateUsing(TranslatableValue::string()),
                TextColumn::make('value')
                    ->formatStateUsing(function (mixed $state): string {
                        if ($state === null) {
                            return '—';
                        }

                        if (is_string($state) && in_array(strtolower($state), ['true', 'false'], true)) {
                            return strtolower($state) === 'true' ? 'Yes' : 'No';
                        }

                        $decoded = is_string($state) ? json_decode($state) : $state;

                        return strval(is_scalar($decoded) || $decoded === null ? $decoded : $state);
                    }),
            ])
            ->headerActions([
                AttachAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DetachAction::make(),
            ])
            ->bulkActions([
                DetachBulkAction::make(),
            ]);
    }
}
