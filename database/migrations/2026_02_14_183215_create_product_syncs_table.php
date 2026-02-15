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
        Schema::create('product_syncs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sync_batch_id')->constrained()->onDelete('cascade');

            // All Sage fields from F_ARTICLE
            $table->string('ar_ref')->unique(); // Primary product reference
            $table->text('ar_design')->nullable(); // Product name/description
            $table->decimal('ar_prixven', 15, 2)->nullable(); // Sale price
            $table->decimal('ar_prixach', 15, 2)->nullable(); // Purchase price

            // Tax info from F_TAXE
            $table->string('ta_code')->nullable(); // Tax code
            $table->decimal('ta_taux', 5, 2)->nullable(); // Tax rate percentage

            // Store the complete raw data for flexibility
            $table->json('sage_raw_data')->nullable();

            // Sellsy mapping results
            $table->string('sellsy_product_id')->nullable();
            $table->string('sellsy_category_id')->nullable();
            $table->string('sellsy_tax_id')->nullable();
            $table->json('sellsy_response')->nullable(); // Full Sellsy API response

            // Sync status tracking
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'skipped'])->default('pending');
            $table->integer('retry_count')->default(0);
            $table->integer('max_retries')->default(3);
            $table->timestamp('last_attempt_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('error_details')->nullable();
            $table->integer('http_status_code')->nullable(); // Track 429, 500, etc
            $table->timestamps();

            $table->index(['status', 'retry_count']);
            $table->index(['sync_batch_id', 'status']);
            $table->index('ar_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_syncs');
    }
};
