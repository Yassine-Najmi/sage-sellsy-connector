<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSync extends Model
{
    protected $fillable = [
        'sync_batch_id',
        'ar_ref',
        'ar_design',
        'ar_prixven',
        'ar_prixach',
        'ta_code',
        'ta_taux',
        'sage_raw_data',
        'sellsy_product_id',
        'sellsy_category_id',
        'sellsy_tax_id',
        'sellsy_response',
        'status',
        'retry_count',
        'max_retries',
        'last_attempt_at',
        'error_message',
        'error_details',
        'http_status_code',
    ];

    protected $casts = [
        'sage_raw_data' => 'array',
        'sellsy_response' => 'array',
        'error_details' => 'array',
        'last_attempt_at' => 'datetime',
        'ar_prixven' => 'decimal:2',
        'ar_prixach' => 'decimal:2',
        'ta_taux' => 'decimal:2',
    ];

    public function syncBatch(): BelongsTo
    {
        return $this->belongsTo(SyncBatch::class);
    }

    public function canRetry(): bool
    {
        return $this->status === 'failed' && $this->retry_count < $this->max_retries;
    }

    public function markAsPending(): void
    {
        $this->update(['status' => 'pending']);
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'last_attempt_at' => now(),
        ]);
    }

    public function markAsSuccess(array $sellsyResponse): void
    {
        $this->update([
            'status' => 'success',
            'sellsy_response' => $sellsyResponse,
            'sellsy_product_id' => $sellsyResponse['id'] ?? null,
            'error_message' => null,
            'error_details' => null,
        ]);
    }

    public function markAsFailed(string $message, array $details = [], ?int $httpCode = null): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $message,
            'error_details' => $details,
            'http_status_code' => $httpCode,
        ]);

        $this->increment('retry_count');
    }

    public function markAsSkipped(string $reason): void
    {
        $this->update([
            'status' => 'skipped',
            'error_message' => $reason,
        ]);
    }
}
