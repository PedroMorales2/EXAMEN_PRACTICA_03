<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleOccupant extends Model
{
    use HasFactory;

    protected $table = 'vehicleoccupants';

    protected $fillable = [
        'status',
        'vehicle_id',
        'user_id',
        'usertype_id',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}