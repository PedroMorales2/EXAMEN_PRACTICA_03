<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model {
    protected $fillable = ['name', 'area', 'description', 'district_id', 'average_waste', 'status'];

    public function district(): BelongsTo {
        return $this->belongsTo(District::class);
    }

    public function zonecoords(): HasMany {
        return $this->hasMany(Zonecoord::class);
    }
}

