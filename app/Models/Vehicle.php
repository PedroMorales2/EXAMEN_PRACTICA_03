<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $table = 'vehicles';

    protected $fillable = [
        'name',
        'code',
        'plate',
        'year',
        'occupant_capacity',
        'load_capacity',
        'fuel_capacity',
        'compaction_capacity',
        'description',
        'status',
        'brand_id',
        'model_id',
        'type_id',
        'color_id',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function brandmodel()
    {
        return $this->belongsTo(BrandModel::class, 'model_id');
    }

    public function vehicletype()
    {
        return $this->belongsTo(VehicleType::class, 'type_id');
    }

    public function vehiclecolor()
    {
        return $this->belongsTo(VehicleColor::class, 'color_id');
    }

    public function images()
    {
        return $this->hasMany(VehicleImage::class);
    }

    public function occupants()
    {
        return $this->hasMany(VehicleOccupant::class);
    }
}