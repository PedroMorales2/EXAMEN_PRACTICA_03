<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'holidays';

    protected $fillable = [
        'date',
        'description',
        'active'
    ];
}
