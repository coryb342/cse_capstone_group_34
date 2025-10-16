<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PredictiveModel extends Model
{
    protected $fillable = [
        'org_id',
        'path',
        'name',
        'required_parameters',
        'run_access_code'
    ];

    protected $hidden = [
        'run_access_code'
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getRequiredParameters(): string
    {
        return $this->required_parameters;
    }

    public function getRunAccessCode(): string
    {
        return $this->run_access_code;
    }
}
