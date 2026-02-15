<?php

namespace App\Filament\Resources\SyncBatches\Tables;

use App\Filament\Resources\ProductSyncs\ProductSyncResource;
use App\Models\SyncBatch;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SyncBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color('info'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'running' => 'warning',
                        'paused' => 'info',
                        'completed' => 'success',
                        'failed' => 'danger',
                    }),
                TextColumn::make('progress')
                    ->label('Progress')
                    ->getStateUsing(function (SyncBatch $record): string {
                        if ($record->total_items === 0) {
                            return '0%';
                        }
                        return "{$record->processed_items}/{$record->total_items} ({$record->progress_percentage}%)";
                    }),
                TextColumn::make('successful_items')
                    ->label('✓ Success')
                    ->badge()
                    ->color('success'),

                TextColumn::make('failed_items')
                    ->label('✗ Failed')
                    ->badge()
                    ->color('danger'),

                TextColumn::make('skipped_items')
                    ->label('⊘ Skipped')
                    ->badge()
                    ->color('warning'),
                TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'running' => 'Running',
                        'paused' => 'Paused',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('view_products')
                    ->label('Products')
                    ->icon('heroicon-o-list-bullet')
                    ->color('info')
                    ->url(
                        fn(SyncBatch $record): string =>
                        ProductSyncResource::getUrl('index', [
                            'tableFilters' => [
                                'sync_batch_id' => ['value' => $record->id],
                            ],
                        ])
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->requiresConfirmation(),
                ]),
            ])->defaultSort('created_at', 'desc')
            ->poll('10s');
    }
}
