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
        Schema::create('soft_sensor_run_results', function (Blueprint $table) {
            $table->foreignId('soft_sensor_id')->constrained('soft_sensors')->cascadeOnDelete();
            $table->foreignId('predictive_model_run_result_id')->constrained('predictive_model_run_results')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soft_sensor_run_results');
    }
};
