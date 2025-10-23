<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    /** @use HasFactory<\Database\Factories\OrganizationFactory> */
    use HasFactory;

    protected $fillable = [
        'org_name',
        'num_seats'
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
        return ($this->num_seats) - User::query()->where('org_id', '=', $this->id)->count();
    }

    public function getAllowedSeats(): int
    {
        return $this->num_seats;
    }

    public function getAccessCodes(): HasMany
    {
        return $this->hasMany(OrgAccessCode::class);
    }
}

