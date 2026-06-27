<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programacion extends Model
{
    use HasFactory;

    protected $table = 'programaciones';

    protected $fillable = [
        'batch_id',
        'personal_group_id',
        'zone_id',
        'schedule_id',
        'vehicle_id',
        'conductor_id',
        'fecha',
        'observaciones',
        'status',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function group()
    {
        return $this->belongsTo(PersonalGroup::class, 'personal_group_id');
    }

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

    public function conductor()
    {
        return $this->belongsTo(User::class, 'conductor_id');
    }

    public function ayudantes()
    {
        return $this->belongsToMany(User::class, 'programacion_ayudante', 'programacion_id', 'user_id')
                    ->withPivot('order')
                    ->withTimestamps()
                    ->orderByPivot('order');
    }

    public function cambios()
    {
        return $this->hasMany(ProgramacionCambio::class, 'programacion_id');
    }
}