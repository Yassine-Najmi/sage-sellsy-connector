<?php

namespace App\Filament\Resources\ProductSyncs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductSyncsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('syncBatch.id')
                    ->searchable(),
                TextColumn::make('ar_ref')
                    ->searchable(),
                TextColumn::make('ar_prixven')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ar_prixach')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ta_code')
                    ->searchable(),
                TextColumn::make('ta_taux')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sellsy_product_id')
                    ->searchable(),
                TextColumn::make('sellsy_category_id')
                    ->searchable(),
                TextColumn::make('sellsy_tax_id')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('retry_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_retries')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_attempt_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('http_status_code')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
