<?php

namespace App\Filament\Resources\SyncBatches\Pages;

use App\Filament\Resources\SyncBatches\SyncBatchResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewSyncBatch extends ViewRecord
{
    protected static string $resource = SyncBatchResource::class;

    // Auto-refresh the page every 10 seconds when sync is running
    protected function getRefreshInterval(): ?int
    {
        return $this->record->status === 'running' ? 10 : null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('start_sync')
                ->label('Start Sync')
                ->icon('heroicon-o-play')
                ->visible(fn() => $this->record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Start Product Sync')
                ->modalDescription(fn() => "This will sync {$this->record->total_items} products from Sage to Sellsy.")
                ->action(function () {
                    // Dispatch the sync command in background
                    \Illuminate\Support\Facades\Artisan::call('sellsy:sync-products', [
                        'batch_id' => $this->record->id,
                    ]);

                    Notification::make()
                        ->title('Sync started!')
                        ->success()
                        ->body('Products are being synced in the background. This page will auto-refresh.')
                        ->send();
                }),

            Action::make('pause_sync')
                ->label('Pause Sync')
                ->icon('heroicon-o-pause')
                ->color('warning')
                ->visible(fn() => $this->record->status === 'running')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'paused']);

                    Notification::make()
                        ->title('Sync paused')
                        ->warning()
                        ->send();
                }),

            Action::make('resume_sync')
                ->label('Resume Sync')
                ->icon('heroicon-o-play')
                ->color('success')
                ->visible(fn() => $this->record->status === 'paused')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'running']);

                    Notification::make()
                        ->title('Sync resumed')
                        ->success()
                        ->send();
                }),

            Action::make('retry_failed')
                ->label('Retry Failed Products')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn() => $this->record->failed_items > 0)
                ->requiresConfirmation()
                ->modalHeading('Retry Failed Products')
                ->modalDescription(fn() => "This will retry {$this->record->failed_items} failed products.")
                ->action(function () {
                    $syncService = app(\App\Services\SyncProductService::class);
                    $retriedCount = $syncService->retryFailedProducts($this->record);

                    Notification::make()
                        ->title('Products queued for retry')
                        ->success()
                        ->body("{$retriedCount} products marked for retry")
                        ->send();
                }),

            DeleteAction::make()
                ->visible(fn() => in_array($this->record->status, ['completed', 'failed', 'pending']))
                ->requiresConfirmation()
                ->modalHeading('Delete Sync Batch')
                ->modalDescription('This will delete the batch and all associated product sync records.'),

        ];
    }
}
