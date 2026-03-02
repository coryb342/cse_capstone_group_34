<?php

use App\Models\PredictiveModel;
use App\Models\PredictiveModelAccessToken;
Use App\Models\User;

test('Request with valid model access token can access describe model endpoint', function () {
    $user = User::factory()->create();
    $predictive_model = PredictiveModel::factory()->create();
    $plain_text_access_token = bin2hex(random_bytes(32));
    $predictive_model_access_token = PredictiveModelAccessToken::create([
        'model_id' => $predictive_model->id,
        'user_id' => $user->id,
        'access_token' => hash('sha256', $plain_text_access_token),
        'token_name' => fake()->word(),
        'status' => 'active',
    ]);

    $response = $this->withHeaders([
        'X-Access-Token' => $plain_text_access_token,
    ])->getJson('api/v1/describe_model');

    $response->assertStatus(200);
});

test('Request with invalid model access token gets rejected', function () {
    $invalid_access_token = bin2hex(random_bytes(32));

    $response = $this->withHeaders([
        'X-Access-Token' => $invalid_access_token,
    ])->getJson('api/v1/describe_model');

    $response->assertStatus(401);

});
