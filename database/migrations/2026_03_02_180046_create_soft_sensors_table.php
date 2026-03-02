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
        Schema::create('soft_sensors', function (Blueprint $table) {
            $table->id();
            $table->string('mqtt_broker');
            $table->string('mqtt_topic');
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('organization_id');
            $table->string('model_id');
            $table->integer('time_interval'); // min 60 seconds
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soft_sensors');
    }
};
