<?php

namespace App\Console\Commands;

use App\Services\SageService;
use App\Services\SellsyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestSellsyConnection extends Command
{
    protected $signature = 'sellsy:test';
    protected $description = 'Test Sellsy and Sage connections';

    public function handle(SellsyService $sellsy, SageService $sage): void
    {
        $this->info('Testing connections...');

        // Test Sage
        $this->info('Testing Sage database...');
        try {
            $result = DB::connection('sage')->select('SELECT @@VERSION as version');
            $this->info('✓ Sage connection successful');
            $this->info('SQL Server Version: ' . $result[0]->version);

            $count = $sage->getTotalProductCount();
            $this->info("✓ Found {$count} products in Sage");
        } catch (\Exception $e) {
            $this->error('✗ Sage connection failed');
            $this->error('Error: ' . $e->getMessage());
            $this->error('Class: ' . get_class($e));
            return;
        }
        if ($sage->testConnection()) {
            $this->info('✓ Sage connection successful');
            $count = $sage->getTotalProductCount();
            $this->info("✓ Found {$count} products in Sage");
        } else {
            $this->error('✗ Sage connection failed');
        }

        // Test Sellsy
        $this->info('Testing Sellsy API...');
        try {
            $taxes = $sellsy->getTaxes();
            $this->info('✓ Sellsy connection successful');
            $this->info('✓ Found ' . count($taxes) . ' tax rates');

            $stats = $sellsy->getRateLimitStats();
            $this->info("✓ API usage: {$stats['calls_today']}/{$stats['remaining_today']} calls today");
        } catch (\Exception $e) {
            $this->error('✗ Sellsy connection failed: ' . $e->getMessage());
        }
    }
}
