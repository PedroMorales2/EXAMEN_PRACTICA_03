<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceDay extends Model
{
    use HasFactory;

    protected $table = 'maintenance_days';

    protected $fillable = [
        'maintenance_schedule_id',
        'date',
        'observation',
        'image_path',
        'completed',
    ];

    protected $casts = [
        'date'      => 'date',
        'completed' => 'boolean',
    ];

    public function schedule()
    {
        return $this->belongsTo(MaintenanceSchedule::class, 'maintenance_schedule_id');
    }
}
