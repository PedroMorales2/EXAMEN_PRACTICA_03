<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cambios extends Model
{
    use HasFactory;

    protected $table = 'cambios';

    protected $fillable = [
        'name',
        'description',
    ];
}