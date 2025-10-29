<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PredictiveModel extends Model
{
    protected $fillable = [
        'org_id',
        'path',
        'name',
        'required_parameters'
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

}
