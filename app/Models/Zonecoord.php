<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Zonecoord extends Model {
    protected $table = 'zonecoords'; // Obligatorio si el plural no es estándar en inglés

    protected $fillable = ['latitude', 'longitude', 'zone_id'];

    public function zone(): BelongsTo {
        return $this->belongsTo(Zone::class);
    }
}

