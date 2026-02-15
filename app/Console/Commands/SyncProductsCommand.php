<?php

namespace App\Console\Commands;

use App\Jobs\SyncProductToSellsy;
use App\Models\ProductSync;
use App\Models\SyncBatch;
use App\Services\SageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncProductsCommand extends Command
{
    protected $signature = 'sellsy:sync-products {batch_id : The sync batch ID}';

    protected $description = 'Sync products from Sage to Sellsy';

    public function handle(SageService $sageService): int
    {
        $batchId = $this->argument('batch_id');
        $batch = SyncBatch::find($batchId);

        if (!$batch) {
            $this->error("Batch {$batchId} not found");
            return self::FAILURE;
        }

        if ($batch->status !== 'pending' && $batch->status !== 'paused') {
            $this->error("Batch {$batchId} is not pending or paused (current status: {$batch->status})");
            return self::FAILURE;
        }

        $this->info("Starting sync for batch {$batchId}");
        $batch->markAsRunning();

        // Step 1: Load products from Sage in batches
        $offset = $batch->current_offset;
        $batchSize = $batch->batch_size;
        $totalProducts = $batch->total_items;

        $this->info("Total products to sync: {$totalProducts}");
        $this->info("Batch size: {$batchSize}");

        $bar = $this->output->createProgressBar($totalProducts);
        $bar->start();

        while ($offset < $totalProducts) {
            // Check if batch is paused
            $batch->refresh();
            if ($batch->status === 'paused') {
                $this->warn("\nSync paused by user");
                return self::SUCCESS;
            }

            // Fetch products from Sage
            $sageProducts = $sageService->fetchProducts($offset, $batchSize);

            if (empty($sageProducts)) {
                $this->warn("\nNo more products found at offset {$offset}");
                break;
            }

            // Create ProductSync records for this batch
            foreach ($sageProducts as $sageProduct) {
                $productSync = ProductSync::updateOrCreate(
                    [
                        'sync_batch_id' => $batch->id,
                        'ar_ref' => $sageProduct->AR_Ref,
                    ],
                    [
                        'ar_design' => $sageProduct->AR_Design ?? null,
                        'ar_prixven' => $sageProduct->AR_PrixVen ?? null,
                        'ar_prixach' => $sageProduct->AR_PrixAch ?? null,
                        'ta_code' => $sageProduct->TA_Code ?? null,
                        'ta_taux' => $sageProduct->TA_Taux ?? null,
                        'sage_raw_data' => json_decode(json_encode($sageProduct), true),
                        'status' => 'pending',
                    ]
                );

                // Dispatch job to queue
                SyncProductToSellsy::dispatch($productSync);

                $bar->advance();
            }

            $offset += $batchSize;
            $batch->update(['current_offset' => $offset]);

            // Small delay to avoid overwhelming the system
            usleep(100000); // 0.1 second
        }

        $bar->finish();
        $this->newLine();

        $batch->markAsCompleted();
        $this->info("Sync batch {$batchId} completed!");

        return self::SUCCESS;
    }
}
