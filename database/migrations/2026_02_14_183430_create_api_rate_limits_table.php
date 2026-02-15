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
        Schema::create('api_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->string('service')->default('sellsy');
            $table->string('endpoint')->nullable();
            $table->integer('calls_in_current_second')->default(0);
            $table->integer('calls_in_current_minute')->default(0);
            $table->integer('calls_today')->default(0);
            $table->timestamp('second_window_start')->useCurrent();
            $table->timestamp('minute_window_start')->useCurrent();
            $table->timestamp('day_window_start')->useCurrent();
            $table->timestamps();

            $table->index(['service', 'second_window_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_rate_limits');
    }
};
