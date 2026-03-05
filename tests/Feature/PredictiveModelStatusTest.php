<?php

use App\Models\PredictiveModel;
Use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('Model can be activated', function () {
    $model = PredictiveModel::factory()->create([
        'status' => 'inactive',
    ]);

    $response = $this->patch(route('predictive-models.status', $model), [
        'status' => 'active',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('predictive_models', [
        'id'     => $model->id,
        'status' => 'active',
    ]);
});

test('Model can be deactivated', function () {
    $model = PredictiveModel::factory()->create([
        'status' => 'active',
    ]);

    $response = $this->patch(route('predictive-models.status', $model), [
        'status' => 'inactive',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('predictive_models', [
        'id'     => $model->id,
        'status' => 'inactive',
    ]);
});

test('Model can be deleted', function () {
    $model = PredictiveModel::factory()->create();

    $response = $this->delete(route('predictive-models.destroy', $model));
    $response->assertRedirect();

    $this->assertDatabaseMissing('predictive_models', [
        'id' => $model->id,
    ]);
});

test('Deleted model no longer exists', function () {
    $user = User::factory()->create();
    $model = PredictiveModel::factory()->create();
    $modelId = $model->id;

    $model->delete();

    $response = $this->actingAs($user)->get(route('predictive-models.show', $modelId));
    $response->assertStatus(404);
});
