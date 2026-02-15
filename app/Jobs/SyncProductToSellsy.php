<?php

namespace App\Jobs;

use App\Models\ProductSync;
use App\Services\SyncProductService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncProductToSellsy implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $backoff = [30, 60, 120]; // Backoff in seconds

    public function __construct(
        public ProductSync $productSync
    ) {}

    public function handle(SyncProductService $syncService): void
    {
        // Check if batch is paused
        if ($this->productSync->syncBatch->status === 'paused') {
            Log::info('Sync paused, releasing job back to queue', [
                'product_id' => $this->productSync->id,
            ]);
            $this->release(60); // Release for 1 minute
            return;
        }

        // Sync the product
        try {
            $syncService->syncProduct($this->productSync);
        } catch (\Exception $e) {
            // If rate limit hit, release job back to queue
            if ($e->getCode() === 429) {
                Log::warning('Rate limit hit, releasing job', [
                    'product_id' => $this->productSync->id,
                ]);
                $this->release(60); // Wait 1 minute before retry
                return;
            }

            // For other errors, let the job fail and retry
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Product sync job failed permanently', [
            'product_id' => $this->productSync->id,
            'ar_ref' => $this->productSync->ar_ref,
            'error' => $exception->getMessage(),
        ]);
    }
}
