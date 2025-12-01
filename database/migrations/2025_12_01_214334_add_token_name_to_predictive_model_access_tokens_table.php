<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('predictive_model_access_tokens', function (Blueprint $table) {
            $table->string('token_name')->nullable()->after('access_token');
        });
    }

    public function down(): void
    {
        Schema::table('predictive_model_access_tokens', function (Blueprint $table) {
            $table->dropColumn('token_name');
        });
    }
};
