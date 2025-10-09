<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = [
        'org_name',
        'seats'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getOrgName(): string
    {
        return $this->org_name;
    }

    public function getRemainingSeats(): int
    {
        return ($this->seats) - User::query()->where('org_id', '=', $this->id)->count();
    }

    public function getAllowedSeats(): int
    {
        return $this->seats;
    }
}

