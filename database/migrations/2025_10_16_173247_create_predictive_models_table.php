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
        Schema::create('predictive_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained();
            $table->string('path');
            $table->string('name');
            $table->json('required_parameters')->nullable();
            $table->string('run_access_code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictive_models');
    }
};
