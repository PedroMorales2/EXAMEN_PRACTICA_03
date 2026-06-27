<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceSchedule extends Model
{
    use HasFactory;

    protected $table = 'maintenance_schedules';

    protected $fillable = [
        'maintenance_id',
        'vehicle_id',
        'responsible_id',
        'type',
        'weekday',
        'start_time',
        'end_time',
    ];

    /**
     * Nombres de los días de la semana en formato ISO (1=Lunes ... 7=Domingo).
     */
    public const WEEKDAYS = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo',
    ];

    public const TYPES = ['PREVENTIVO', 'LIMPIEZA', 'REPARACION'];

    public function getWeekdayNameAttribute(): string
    {
        return self::WEEKDAYS[$this->weekday] ?? '-';
    }

    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function days()
    {
        return $this->hasMany(MaintenanceDay::class);
    }
}
