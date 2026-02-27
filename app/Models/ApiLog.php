<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    protected $fillable = [
        'source_ip',
        'method',
        'response_code',
        'user_id',
        'predictive_model_access_token_id',
        'predictive_model_run_result_id',
    ];
}
