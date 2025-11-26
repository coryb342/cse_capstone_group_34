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
        'result' => 'array',
        'actual' => 'array',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(PredictiveModel::class, 'model_id');
    }
}
