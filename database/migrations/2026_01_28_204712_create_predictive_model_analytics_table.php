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
        Schema::create('predictive_model_analytics', function (Blueprint $table) {
            $table->id();

            $table->foreignId('model_id')
                ->constrained('predictive_models')
                ->cascadeOnDelete();

            $table->foreignId('organization_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // counters
            $table->unsignedBigInteger('total_predictions')->default(0);
            $table->unsignedBigInteger('total_failed_predictions')->default(0);
            $table->unsignedBigInteger('evaluated_predictions')->default(0);

            // regression metrics snapshot (derived from evaluated runs)
            $table->double('mse')->nullable();
            $table->double('mae')->nullable();
            $table->double('rmse')->nullable();
            $table->double('r2')->nullable();
            $table->double('mape')->nullable();
            $table->double('accuracy')->nullable(); // 100 - MAPE

            $table->timestamps();

            // one analytics row per model
            $table->unique('model_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictive_model_analytics');
    }
};
