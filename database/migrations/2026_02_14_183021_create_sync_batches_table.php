<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sync_batches', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('product_sync'); // for future: customers, invoices
            $table->integer('total_items')->default(0);
            $table->integer('processed_items')->default(0);
            $table->integer('successful_items')->default(0);
            $table->integer('failed_items')->default(0);
            $table->integer('skipped_items')->default(0); // already synced
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('current_offset')->default(0); // for pagination
            $table->integer('batch_size')->default(100); // how many to fetch at once
            $table->json('config')->nullable(); // store batch-specific settings
            $table->timestamps();

            $table->index(['status', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_batches');
    }
};
