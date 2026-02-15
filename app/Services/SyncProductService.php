<?php

namespace App\Services;

use App\Models\ProductSync;
use App\Models\SyncBatch;
use Illuminate\Support\Facades\Log;

class SyncProductService
{
    public function __construct(
        private SellsyService $sellsyService,
        private CategoryMappingService $categoryMappingService,
        private TaxMappingService $taxMappingService
    ) {}

    /**
     * Sync a single product to Sellsy
     */
    public function syncProduct(ProductSync $productSync): void
    {
        $productSync->markAsProcessing();

        try {
            // Build Sellsy product data
            $sellsyData = $this->buildSellsyProductData($productSync);

            // Check if product already exists in Sellsy
            $existingProduct = $this->sellsyService->getProductByRef($productSync->ar_ref);

            if ($existingProduct) {
                // Update existing product
                $response = $this->sellsyService->updateProduct(
                    $existingProduct['id'],
                    $sellsyData
                );

                Log::info('Product updated in Sellsy', [
                    'ar_ref' => $productSync->ar_ref,
                    'sellsy_id' => $existingProduct['id'],
                ]);
            } else {
                // Create new product
                $response = $this->sellsyService->createProduct($sellsyData);

                Log::info('Product created in Sellsy', [
                    'ar_ref' => $productSync->ar_ref,
                    'sellsy_id' => $response['id'] ?? null,
                ]);
            }

            $productSync->markAsSuccess($response);
            $productSync->syncBatch->incrementProcessed('success');

        } catch (\Exception $e) {
            Log::error('Product sync failed', [
                'ar_ref' => $productSync->ar_ref,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            $productSync->markAsFailed(
                $e->getMessage(),
                [
                    'exception' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ],
                $e->getCode()
            );

            $productSync->syncBatch->incrementProcessed('failed');

            // Re-throw rate limit errors to pause the batch
            if ($e->getCode() === 429) {
                throw $e;
            }
        }
    }

    /**
     * Build Sellsy product data from Sage product
     */
    private function buildSellsyProductData(ProductSync $productSync): array
    {
        $sageData = $productSync->sage_raw_data;

        // Map category
        $categoryId = $this->categoryMappingService->getSellsyCategoryId($sageData);

        // Map tax
        $taxId = $this->taxMappingService->getSellsyTaxId(
            $productSync->ta_code,
            $productSync->ta_taux
        );

        return [
            'ref' => $productSync->ar_ref,
            'name' => $productSync->ar_design ?? $productSync->ar_ref,
            'unit_amount' => $productSync->ar_prixven ?? 0,
            'purchase_amount' => $productSync->ar_prixach ?? 0,
            'category_id' => $categoryId,
            'tax_id' => $taxId,
            // Add more fields as needed based on Sellsy API
        ];
    }

    /**
     * Retry failed products in a batch
     */
    public function retryFailedProducts(SyncBatch $batch): int
    {
        $failedProducts = $batch->failedProducts()
            ->where('retry_count', '<', 'max_retries')
            ->get();

        Log::info('Retrying failed products', [
            'batch_id' => $batch->id,
            'count' => $failedProducts->count(),
        ]);

        $retriedCount = 0;

        foreach ($failedProducts as $product) {
            if ($product->canRetry()) {
                $product->markAsPending();
                $retriedCount++;
            }
        }

        return $retriedCount;
    }
}
