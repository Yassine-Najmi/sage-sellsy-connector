<?php

namespace App\Filament\Resources\ProductSyncs\Tables;

use App\Models\ProductSync;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductSyncsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('syncBatch.id')
                    ->label('Batch')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('ar_ref')
                    ->label('Product Ref')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('ar_design')
                    ->label('Description')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn($record) => $record->ar_design),

                TextColumn::make('ar_prixven')
                    ->label('Price')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'info',
                        'success' => 'success',
                        'failed' => 'danger',
                        'skipped' => 'warning',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'processing' => 'heroicon-o-arrow-path',
                        'success' => 'heroicon-o-check-circle',
                        'failed' => 'heroicon-o-x-circle',
                        'skipped' => 'heroicon-o-minus-circle',
                    })
                    ->sortable(),

                TextColumn::make('retry_count')
                    ->label('Retries')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'warning' : 'gray')
                    ->sortable(),

                TextColumn::make('http_status_code')
                    ->label('HTTP Code')
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state === null => 'gray',
                        $state >= 200 && $state < 300 => 'success',
                        $state === 429 => 'warning',
                        $state >= 400 => 'danger',
                        default => 'info',
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('sellsy_product_id')
                    ->label('Sellsy ID')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('last_attempt_at')
                    ->label('Last Attempt')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('sync_batch_id')
                    ->label('Batch')
                    ->relationship('syncBatch', 'id')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'skipped' => 'Skipped',
                    ])
                    ->multiple(),

                Filter::make('has_errors')
                    ->label('Has Errors')
                    ->query(fn($query) => $query->whereNotNull('error_message')),

                Filter::make('can_retry')
                    ->label('Can Retry')
                    ->query(fn($query) => $query->where('status', 'failed')
                        ->whereColumn('retry_count', '<', 'max_retries')),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('retry')
                    ->label('Retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn(ProductSync $record) => $record->canRetry())
                    ->requiresConfirmation()
                    ->action(function (ProductSync $record) {
                        $record->markAsPending();

                        \Filament\Notifications\Notification::make()
                            ->title('Product queued for retry')
                            ->success()
                            ->send();
                    }),

                Action::make('view_in_sellsy')
                    ->label('View in Sellsy')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('info')
                    ->visible(fn(ProductSync $record) => $record->sellsy_product_id)
                    ->url(
                        fn(ProductSync $record) =>
                        "https://www.sellsy.com/?_f=catalogueitem&id={$record->sellsy_product_id}"
                    )
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('retry_selected')
                        ->label('Retry Selected')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->canRetry()) {
                                    $record->markAsPending();
                                    $count++;
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->title("{$count} products queued for retry")
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('10s');
    }
}
