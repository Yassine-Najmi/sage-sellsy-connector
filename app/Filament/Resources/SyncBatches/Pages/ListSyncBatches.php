<?php

namespace App\Filament\Resources\SyncBatches\Pages;

use App\Filament\Resources\SyncBatches\SyncBatchResource;
use App\Models\SyncBatch;
use App\Services\SageService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSyncBatches extends ListRecords
{
    protected static string $resource = SyncBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('start_new_sync')
                ->label('Start New Sync')
                ->icon('heroicon-o-play')
                ->requiresConfirmation()
                ->modalHeading('Start New Product Sync')
                ->modalDescription('This will create a new sync batch and start syncing all products from Sage to Sellsy.')
                ->modalSubmitActionLabel('Start Sync')
                ->action(function () {
                    $sageService = app(SageService::class);
                    $totalProducts = $sageService->getTotalProductCount();

                    // Create new sync batch
                    $batch = SyncBatch::create([
                        'type' => 'product_sync',
                        'total_items' => $totalProducts,
                        'status' => 'pending',
                        'batch_size' => config('sellsy.sync.batch_size', 100),
                    ]);

                    Notification::make()
                        ->title('Sync batch created')
                        ->body("Ready to sync {$totalProducts} products. Click 'Start Sync' to begin.")
                        ->send();

                    // Redirect to view page
                    $this->redirect(SyncBatchResource::getUrl('view', ['record' => $batch]));
                }),

            Action::make('api_stats')
                ->label('API Stats')
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->modalHeading('Sellsy API Usage Statistics')
                ->modalContent(function () {
                    $sellsy = app(\App\Services\SellsyService::class);
                    $stats = $sellsy->getRateLimitStats();

                    return view('filament.modals.api-stats', ['stats' => $stats]);
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),

        ];
    }
}
