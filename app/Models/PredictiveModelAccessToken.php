<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PredictiveModelAccessToken extends Model
{
    protected $fillable = [
        'model_id',
        'user_id',
        'access_token',
    ];

    protected $hidden = [
        'access_token',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(PredictiveModel::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
