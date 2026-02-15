<?php

namespace App\Services;

use App\Models\ApiRateLimit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SellsyService
{
    private const AUTH_URL = 'https://login.sellsy.com/oauth2/access-tokens';
    private const API_URL = 'https://apifeed.sellsy.com/0/';

    private const MAX_REQUESTS_PER_SECOND = 5;
    private const MAX_REQUESTS_PER_DAY = 432000;

    private ?string $accessToken = null;

    /**
     * Get OAuth2 access token (cached for 1 hour)
     */
    private function getAccessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        return Cache::remember('sellsy_access_token', 3600, function () {
            $response = Http::asForm()->post(self::AUTH_URL, [
                'grant_type' => 'client_credentials',
                'client_id' => config('sellsy.client_id'),
                'client_secret' => config('sellsy.client_secret'),
            ]);

            if ($response->failed()) {
                Log::error('Sellsy OAuth failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Failed to authenticate with Sellsy: ' . $response->body());
            }

            $this->accessToken = $response->json('access_token');

            Log::info('Sellsy access token obtained');

            return $this->accessToken;
        });
    }

    /**
     * Respect Sellsy rate limits: 5 req/second, 432k req/day
     */
    private function respectRateLimit(): void
    {
        $rateLimit = ApiRateLimit::firstOrCreate(
            ['service' => 'sellsy'],
            [
                'calls_in_current_second' => 0,
                'calls_today' => 0,
                'second_window_start' => now(),
                'day_window_start' => now()->startOfDay(),
            ]
        );

        // Reset second counter if new second
        if (now()->greaterThan($rateLimit->second_window_start->addSecond())) {
            $rateLimit->update([
                'calls_in_current_second' => 0,
                'second_window_start' => now(),
            ]);
        }

        // Reset day counter if new day
        if (now()->greaterThan($rateLimit->day_window_start->addDay())) {
            $rateLimit->update([
                'calls_today' => 0,
                'day_window_start' => now()->startOfDay(),
            ]);
        }

        // Check if we hit the second limit
        if ($rateLimit->calls_in_current_second >= self::MAX_REQUESTS_PER_SECOND) {
            $sleepTime = 1 - now()->diffInMilliseconds($rateLimit->second_window_start) / 1000;
            if ($sleepTime > 0) {
                usleep((int)($sleepTime * 1000000));
            }

            // Reset after sleeping
            $rateLimit->update([
                'calls_in_current_second' => 0,
                'second_window_start' => now(),
            ]);
        }

        // Check daily limit
        if ($rateLimit->calls_today >= self::MAX_REQUESTS_PER_DAY) {
            throw new \Exception('Daily Sellsy API limit reached (432,000 requests)');
        }

        // Increment counters
        $rateLimit->increment('calls_in_current_second');
        $rateLimit->increment('calls_today');
    }

    /**
     * Make API call to Sellsy (Old API format)
     */
    public function call(string $method, array $params = []): array
    {
        $this->respectRateLimit();

        $token = $this->getAccessToken();

        $response = Http::asForm()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])
            ->post(self::API_URL, [
                'io_mode' => 'json',
                'do_in' => json_encode([
                    'method' => $method,
                    'params' => $params,
                ]),
            ]);

        if ($response->status() === 429) {
            Log::warning('Sellsy rate limit hit (429)', ['method' => $method]);
            throw new \Exception('Rate limit exceeded', 429);
        }

        if ($response->failed()) {
            Log::error('Sellsy API call failed', [
                'method' => $method,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception(
                'Sellsy API error: ' . $response->body(),
                $response->status()
            );
        }

        $data = $response->json();

        // Check for API-level errors
        if (isset($data['status']) && $data['status'] === 'error') {
            throw new \Exception(
                'Sellsy API error: ' . ($data['error']['message'] ?? 'Unknown error')
            );
        }

        return $data;
    }

    /**
     * Get all tax rates from Sellsy
     */
    public function getTaxes(): array
    {
        $response = $this->call('Accountdatas.getTaxes', ['enabled' => 'all']);
        return $response['response'] ?? [];
    }

    /**
     * Create a product in Sellsy
     */
    public function createProduct(array $productData): array
    {
        $response = $this->call('Catalogue.create', $productData);
        return $response['response'] ?? [];
    }

    /**
     * Update a product in Sellsy
     */
    public function updateProduct(string $productId, array $productData): array
    {
        $productData['id'] = $productId;
        $response = $this->call('Catalogue.update', $productData);
        return $response['response'] ?? [];
    }

    /**
     * Get product by reference
     */
    public function getProductByRef(string $reference): ?array
    {
        try {
            $response = $this->call('Catalogue.getList', [
                'search' => ['contains' => $reference],
            ]);

            $products = $response['response']['result'] ?? [];

            foreach ($products as $product) {
                if ($product['ref'] === $reference) {
                    return $product;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get product by ref', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get rate limit statistics
     */
    public function getRateLimitStats(): array
    {
        $rateLimit = ApiRateLimit::where('service', 'sellsy')->first();

        if (!$rateLimit) {
            return [
                'calls_in_current_second' => 0,
                'calls_today' => 0,
                'remaining_today' => self::MAX_REQUESTS_PER_DAY,
            ];
        }

        return [
            'calls_in_current_second' => $rateLimit->calls_in_current_second,
            'calls_today' => $rateLimit->calls_today,
            'remaining_today' => self::MAX_REQUESTS_PER_DAY - $rateLimit->calls_today,
            'percentage_used' => round(($rateLimit->calls_today / self::MAX_REQUESTS_PER_DAY) * 100, 2),
        ];
    }
}
