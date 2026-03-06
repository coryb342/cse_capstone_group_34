<?php

use App\Models\PredictiveModel;
use App\Models\PredictiveModelRunResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('Can export run results as CSV', function () {
    $user = User::factory()->create();
    $model = PredictiveModel::factory()->create();

    PredictiveModelRunResult::factory()->count(3)->create([
        'model_id' => $model->id,
    ]);

    $response = $this->actingAs($user)->get(route('predictive-models.export.csv', $model));
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

test('Can export run results as Excel', function () {
    $user = User::factory()->create();
    $model = PredictiveModel::factory()->create();

    PredictiveModelRunResult::factory()->count(3)->create([
        'model_id' => $model->id,
    ]);

    $response = $this->actingAs($user)->get(route('predictive-models.export.excel', $model));
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});
