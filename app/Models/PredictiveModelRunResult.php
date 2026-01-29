<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PredictiveModelRunResult extends Model
{
    protected $fillable = [
        'model_id',
        'inputs',
        'result',
        'actual',
    ];

    protected $casts = [
        'inputs' => 'array',
        'result' => 'string',
        'actual' => 'string',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(PredictiveModel::class, 'model_id');
    }

    public function predictedValue(): ?float
    {
        return $this->decodeMaybeJsonNumber($this->result);
    }

    public function actualValue(): ?float
    {
        return $this->decodeMaybeJsonNumber($this->actual);
    }

    private function decodeMaybeJsonNumber($value): ?float
    {
        if ($value === null)
            return null;

        if (is_numeric($value))
            return (float) $value;

        $v = $value;

        for ($i = 0; $i < 2; $i++) {
            if (!is_string($v))
                break;

            $decoded = json_decode($v, true);
            if (json_last_error() !== JSON_ERROR_NONE)
                break;

            $v = $decoded;
        }
        return is_numeric($v) ? (float) $v : null;
    }


}
