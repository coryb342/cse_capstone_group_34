<?php

namespace App\Exports;

use App\Models\PredictiveModel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RunResultsExport implements FromCollection, WithHeadings, WithMapping
{
    private array $inputKeys = [];

    public function __construct(protected PredictiveModel $model)
    {
        // Collect all unique input keys across all run results
        $this->inputKeys = $this->model->runResults()
            ->orderBy('created_at')
            ->get(['inputs'])
            ->flatMap(function ($run) {
                $decoded = json_decode($run->inputs, true);
                return is_array($decoded) ? array_keys($decoded) : [];
            })
            ->unique()
            ->values()
            ->toArray();
    }

    public function collection()
    {
        return $this->model->runResults()
            ->orderBy('created_at')
            ->get(['id', 'created_at', 'inputs', 'result', 'actual']);
    }

    public function headings(): array
    {
        // Base columns + one column per input key + predicted/actual/residual
        return array_merge(
            ['Run ID', 'Date'],
            array_map('strtoupper', $this->inputKeys),
            ['Predicted', 'Actual', 'Residual']
        );
    }

    public function map($run): array
    {
        $predicted = $run->predictedValue();
        $actual = $run->actualValue();
        $residual = ($predicted !== null && $actual !== null)
            ? round($actual - $predicted, 3)
            : null;

        // Parse inputs JSON and map each key to its column
        $inputs = json_decode($run->inputs, true) ?? [];
        $inputValues = array_map(fn($key) => $inputs[$key] ?? null, $this->inputKeys);

        return array_merge(
            [$run->id, $run->created_at->toDateTimeString()],
            $inputValues,
            [$predicted, $actual, $residual]
        );
    }
}
