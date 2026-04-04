<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorViewingSession extends Model
{
    protected $fillable = [
        'user_id',
        'organization_id',
    ];
}
