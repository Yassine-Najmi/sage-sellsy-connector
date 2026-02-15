<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SyncBatch extends Model
{
    protected $fillable = [
        'type',
        'total_items',
        'processed_items',
        'successful_items',
        'failed_items',
        'skipped_items',
        'status',
        'started_at',
        'completed_at',
        'current_offset',
        'batch_size',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function productSyncs(): HasMany
    {
        return $this->hasMany(ProductSync::class);
    }

    public function pendingProducts(): HasMany
    {
        return $this->hasMany(ProductSync::class)->where('status', 'pending');
    }

    public function failedProducts(): HasMany
    {
        return $this->hasMany(ProductSync::class)->where('status', 'failed');
    }

    public function successfulProducts(): HasMany
    {
        return $this->hasMany(ProductSync::class)->where('status', 'success');
    }

    // Helper methods
    public function markAsRunning(): void
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function incrementProcessed(string $status): void
    {
        $this->increment('processed_items');

        match($status) {
            'success' => $this->increment('successful_items'),
            'failed' => $this->increment('failed_items'),
            'skipped' => $this->increment('skipped_items'),
            default => null,
        };
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_items === 0) {
            return 0;
        }
        return round(($this->processed_items / $this->total_items) * 100, 2);
    }
}
