<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PredictiveModel extends Model
{
    protected $fillable = [
        'organization_id',
        'path',
        'name',
        'required_parameters',
        'target',
        'description',
        'type',
        'status',
        'last_trained_on',
        'accuracy',
    ];

    protected $hidden = [
        'run_access_code'
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function accessTokens(): HasMany
    {
        return $this->hasMany(PredictiveModelAccessToken::class);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getRequiredParameters(): string
    {
        return $this->required_parameters;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getLastTrainedOn(): string
    {
        return $this->last_trained_on;
    }

    public function getAccuracy(): float
    {
        return $this->accuracy;
    }

    public function runResults(): HasMany
    {
        return $this->hasMany(PredictiveModelRunResult::class, 'model_id');
    }

    public function analytics()
    {
        return $this->hasOne(PredictiveModelAnalytics::class, 'model_id');
    }
    public function latestRunResult()
    {
        return $this->hasOne(\App\Models\PredictiveModelRunResult::class, 'model_id')->latestOfMany();
    }
}
