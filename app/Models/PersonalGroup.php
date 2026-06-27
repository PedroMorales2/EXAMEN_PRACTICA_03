<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalGroup extends Model
{
    use HasFactory;

    protected $table = 'personal_groups';

    protected $fillable = [
        'name',
        'zone_id',
        'schedule_id',
        'vehicle_id',
        'days',
        'status',
    ];

    protected $casts = [
        'days' => 'array',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * All members (conductor + ayudantes) via pivot.
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'personal_group_users', 'personal_group_id', 'user_id')
                    ->withPivot('role', 'order')
                    ->withTimestamps()
                    ->orderByPivot('order');
    }

    public function conductor()
    {
        return $this->belongsToMany(User::class, 'personal_group_users', 'personal_group_id', 'user_id')
                    ->withPivot('role', 'order')
                    ->wherePivot('role', 'conductor');
    }

    public function ayudantes()
    {
        return $this->belongsToMany(User::class, 'personal_group_users', 'personal_group_id', 'user_id')
                    ->withPivot('role', 'order')
                    ->wherePivot('role', 'ayudante')
                    ->orderByPivot('order');
    }

    // ── Helpers ────────────────────────────────────────────────

    public function getConductorUserAttribute(): ?User
    {
        return $this->conductor()->first();
    }

    public function getDaysLabelAttribute(): string
    {
        return implode(', ', $this->days ?? []);
    }
}