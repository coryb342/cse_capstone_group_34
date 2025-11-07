<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrgAccessCode extends Model
{
    protected $fillable = [
        'organization_id',
        'access_code',
        'created_by',
        'is_active'
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function isExpired(): bool
    {
        return Carbon::now() > $this->created_at->addDay();
    }

    public function isActive(): bool
    {
        return $this->is_acitve;
    }

    public function getCreatedByUserId(): int
    {
        return $this->created_by;
    }

    public function getAccessCode(): string
    {
        return $this->access_code;
    }
}
