<?php

namespace App\Filament\Resources\ProductSyncs\Pages;

use App\Filament\Resources\ProductSyncs\ProductSyncResource;
use App\Filament\Resources\SyncBatches\SyncBatchResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProductSync extends ViewRecord
{
    protected static string $resource = ProductSyncResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('retry')
                ->label('Retry Sync')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn () => $this->record->canRetry())
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->markAsPending();

                    \Filament\Notifications\Notification::make()
                        ->title('Product queued for retry')
                        ->success()
                        ->send();

                    $this->redirect(ProductSyncResource::getUrl('index'));
                }),

            Action::make('view_batch')
                ->label('View Batch')
                ->icon('heroicon-o-queue-list')
                ->color('info')
                ->url(fn () => SyncBatchResource::getUrl('view', [
                    'record' => $this->record->sync_batch_id
                ])),
            DeleteAction::make(),
        ];
    }
}
