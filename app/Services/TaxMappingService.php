<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TaxMappingService
{
    private const DEFAULT_TAX_RATE = '20.00';

    // Tax mapping from your N8N config
    private const TAX_MAPPING = [
        'ENV' => '20.00',
        'CLA' => '20.00',
        'FOU' => '20.00',
        'EQU' => '20.00',
    ];

    public function __construct(
        private SellsyService $sellsyService
    ) {}

    /**
     * Get Sellsy tax ID from Sage tax code/rate
     */
    public function getSellsyTaxId(?string $taxCode, ?float $taxRate): ?string
    {
        // Get expected tax rate
        $expectedRate = self::TAX_MAPPING[$taxCode] ?? self::DEFAULT_TAX_RATE;

        // Get all Sellsy taxes (cached)
        $sellsyTaxes = $this->getSellsyTaxes();

        // Find matching tax by rate
        foreach ($sellsyTaxes as $taxId => $tax) {
            if (abs(floatval($tax['rate']) - floatval($expectedRate)) < 0.01) {
                return $taxId;
            }
        }

        Log::warning('No matching Sellsy tax found', [
            'tax_code' => $taxCode,
            'tax_rate' => $taxRate,
            'expected_rate' => $expectedRate,
        ]);

        return null;
    }

    /**
     * Get all Sellsy taxes (cached for 1 hour)
     */
    private function getSellsyTaxes(): array
    {
        return Cache::remember('sellsy_taxes', 3600, function () {
            return $this->sellsyService->getTaxes();
        });
    }
}
