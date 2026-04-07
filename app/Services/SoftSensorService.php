<?php
namespace App\Services;

use App\Models\PredictiveModel;
use App\Models\PredictiveModelRunResult;
use App\Models\SoftSensor;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\MqttClient;

Class SoftSensorService {

    public function __construct(
        protected DockerExecutionService $execution_service,
        protected PredictiveModelAnalyticsService $analytics_service,
    )
    {
    }

    private function getLatestDataStreamPayload(SoftSensor $soft_sensor): array
    {
        $broker = $soft_sensor->mqtt_broker;
        $topic = $soft_sensor->mqtt_topic;
        $broker_parts = parse_url($broker);

        $client = new MqttClient($broker_parts['host'], $broker_parts['port']);

        $client->connect();

        $data_stream_payload = [];
        $client->subscribe($topic, function ($topic, $message) use (&$data_stream_payload, &$client) {
            $data_stream_payload = json_decode($message, true);
            $client->interrupt();
        }, 0);

        $client->loop();
        $client->disconnect();

        return $data_stream_payload;
    }

    private function getPredictionRunResult(SoftSensor $soft_sensor): ?PredictiveModelRunResult
    {
        $latest_payload = $this->getLatestDataStreamPayload($soft_sensor);

        $model_id = $soft_sensor->model_id;
        $model = PredictiveModel::find($model_id);

        $model_required_parameters = json_decode($model->required_parameters);
        $parameters_from_datastream = [];

        foreach ($latest_payload as $key => $value) {
            if ($key !== "sensor_actual" && !str_contains($key, "Date")){
                $parameters_from_datastream[] = $value;
            }
        }

        $sensor_actual = $latest_payload["sensor_actual"];

        if (count($parameters_from_datastream) !== count($model_required_parameters)) {
            return null;
        }

        $mapped_parameters = [];
        $index = 0;

        foreach ($model_required_parameters as $parameter) {
            $mapped_parameters[$parameter] = $parameters_from_datastream[$index];
            $index++;
        }

        $prediction = $this->execution_service->runPrediction($model, $parameters_from_datastream);

        return new PredictiveModelRunResult([
            'model_id' => $model_id,
            'inputs' => json_encode($mapped_parameters),
            'result' => $prediction,
            'actual' => $sensor_actual,
        ]);
    }

    public function updatePredictedValue(SoftSensor $soft_sensor): void
    {
        $run_result = $this->getPredictionRunResult($soft_sensor);

        if (!$run_result) {
            Log::error("SoftSensorService updatePredictedValue getPredictionRunResult failed.");
            return;
        }

        $run_result->save();

        $this->analytics_service->recomputeForModel($run_result->model_id);

        $soft_sensor->runResults()->attach($run_result->id);

        $soft_sensor->update(['last_prediction_time' => now()]);
    }

}


