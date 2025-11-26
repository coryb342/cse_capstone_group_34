<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('predictive_model_run_results')->insert([
//            [
//                'model_id' => 1,
//                'inputs' => json_encode([
//                    'temp' => 70,
//                    'flow_rate' => 210,
//                    'prcp' => 0.12
//                ]),
//                'result' => json_encode([
//                    'predicted' => 205.4,
//                    'mae' => 12.5,
//                    'rmse' => 18.1
//                ]),
//                'actual' => json_encode([
//                    'value' => 198.0
//                ]),
//                'created_at' => now(),
//                'updated_at' => now(),
//            ],
//            [
//                'model_id' => 1,
//                'inputs' => json_encode([
//                    'temp' => 65,
//                    'flow_rate' => 190,
//                    'prcp' => 0.4
//                ]),
//                'result' => json_encode([
//                    'predicted' => 185.9,
//                    'mae' => 9.3,
//                    'rmse' => 15.0
//                ]),
//                'actual' => json_encode([
//                    'value' => 178.4
//                ]),
//                'created_at' => now()->subDays(1),
//                'updated_at' => now()->subDays(1),
//            ],
//            [
//                'model_id' => 2,
//                'inputs' => json_encode([
//                    'temp' => 80,
//                    'flow_rate' => 240,
//                    'prcp' => 0.0
//                ]),
//                'result' => json_encode([
//                    'predicted' => 250.3,
//                    'mae' => 11.1,
//                    'rmse' => 16.9
//                ]),
//                'actual' => json_encode([
//                    'value' => 242.6
//                ]),
//                'created_at' => now()->subDays(2),
//                'updated_at' => now()->subDays(2),
//            ],
                [
                    'model_id' => 3,
                    'inputs' => json_encode([
                        'vista_gauge_level' => 3.2,
                        'vista_gauge_pred' => 3.5,
                        'flow1' => 185,
                        'flow2' => 190,
                        'prcp' => 0.00,
                        'snow' => 0.0
                    ]),
                    'result' => json_encode([
                        'predicted' => 188.7,
                        'mae' => 3.3,
                        'rmse' => 3.3,
                        'mse' => 10.89
                    ]),
                    'actual' => json_encode([
                        'value' => 192.0,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            [
                'model_id' => 3,
                'inputs' => json_encode([
                    'vista_gauge_level' => 3.2,
                    'vista_gauge_pred' => 3.5,
                    'flow1' => 185,
                    'flow2' => 190,
                    'prcp' => 0.00,
                    'snow' => 0.0
                ]),
                'result' => json_encode([
                    'predicted' => 188.7,
                    'mae' => 3.3,
                    'rmse' => 3.3,
                    'mse' => 10.89
                ]),
                'actual' => json_encode([
                    'value' => 192.0,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'model_id' => 3,
                'inputs' => json_encode([
                    'vista_gauge_level' => 4.8,
                    'vista_gauge_pred' => 5.0,
                    'flow1' => 245,
                    'flow2' => 240,
                    'prcp' => 0.35,
                    'snow' => 1.8
                ]),
                'result' => json_encode([
                    'predicted' => 252.9,
                    'mae' => 7.5,
                    'rmse' => 7.5,
                    'mse' => 56.25
                ]),
                'actual' => json_encode([
                    'value' => 260.4,
                ]),
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
        ]);
    }
}
