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
        Schema::table('predictive_model_access_tokens', function (Blueprint $table) {
            $table->dropForeign(['model_id']);

            $table->foreign('model_id')
                ->references('id')
                ->on('predictive_models')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('predictive_model_access_tokens', function (Blueprint $table) {
            $table->dropForeign(['model_id']);

            $table->foreign('model_id')
                ->references('id')
                ->on('predictive_models');
        });
    }
};
