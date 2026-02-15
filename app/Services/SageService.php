<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SageService
{
    private const BATCH_SIZE = 100;

    /**
     * Get total count of products in Sage
     */
    public function getTotalProductCount(): int
    {
        return DB::connection('sage')
            ->table('F_ARTICLE')
            ->count();
    }

    /**
     * Fetch products from Sage with pagination
     *
     * @param int $offset Starting offset
     * @param int $limit Number of products to fetch
     * @return array
     */
    public function fetchProducts(int $offset = 0, int $limit = self::BATCH_SIZE): array
    {
        Log::info('Fetching products from Sage', [
            'offset' => $offset,
            'limit' => $limit,
        ]);

        $products = DB::connection('sage')
            ->select("
                SELECT
                    'sodico_products' as ClientName,
                    F_ARTICLE.*,
                    F_TAXE.TA_Code,
                    F_TAXE.TA_Taux
                FROM F_ARTICLE
                LEFT JOIN (
                    SELECT
                        AR_Ref,
                        ACP_ComptaCPT_Taxe1,
                        ROW_NUMBER() OVER (PARTITION BY AR_Ref ORDER BY ACP_ComptaCPT_Taxe1) AS rn
                    FROM F_ARTCOMPTA
                ) AS ARTCOMPTA_FILTERED
                    ON F_ARTICLE.AR_REF = ARTCOMPTA_FILTERED.AR_Ref
                    AND ARTCOMPTA_FILTERED.rn = 1
                LEFT JOIN F_TAXE
                    ON ARTCOMPTA_FILTERED.ACP_ComptaCPT_Taxe1 = F_TAXE.TA_Code
                WHERE 1=1
                ORDER BY F_ARTICLE.AR_Ref
                OFFSET ? ROWS
                FETCH NEXT ? ROWS ONLY
            ", [$offset, $limit]);

        Log::info('Fetched products from Sage', [
            'count' => count($products),
            'offset' => $offset,
        ]);

        return $products;
    }

    /**
     * Fetch a single product by reference
     */
    public function fetchProductByRef(string $reference): ?object
    {
        $products = DB::connection('sage')
            ->select("
                SELECT
                    'sodico_products' as ClientName,
                    F_ARTICLE.*,
                    F_TAXE.TA_Code,
                    F_TAXE.TA_Taux
                FROM F_ARTICLE
                LEFT JOIN (
                    SELECT
                        AR_Ref,
                        ACP_ComptaCPT_Taxe1,
                        ROW_NUMBER() OVER (PARTITION BY AR_Ref ORDER BY ACP_ComptaCPT_Taxe1) AS rn
                    FROM F_ARTCOMPTA
                ) AS ARTCOMPTA_FILTERED
                    ON F_ARTICLE.AR_REF = ARTCOMPTA_FILTERED.AR_Ref
                    AND ARTCOMPTA_FILTERED.rn = 1
                LEFT JOIN F_TAXE
                    ON ARTCOMPTA_FILTERED.ACP_ComptaCPT_Taxe1 = F_TAXE.TA_Code
                WHERE F_ARTICLE.AR_Ref = ?
            ", [$reference]);

        return $products[0] ?? null;
    }

    /**
     * Test Sage database connection
     */
    public function testConnection(): bool
    {
        try {
            DB::connection('sage')->select('SELECT 1');
            Log::info('Sage database connection successful');
            return true;
        } catch (\Exception $e) {
            Log::error('Sage database connection failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
