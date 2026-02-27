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
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source_ip');
            $table->string('method');
            $table->integer('response_code');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->foreignId('predictive_model_access_token_id')
                ->nullable()
                ->constrained('predictive_model_access_tokens')
                ->onDelete('set null');
            $table->foreignId('predictive_model_run_result_id')
                ->nullable()
                ->constrained('predictive_model_run_results')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
