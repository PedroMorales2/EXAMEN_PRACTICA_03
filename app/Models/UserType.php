<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; 

class UserType extends Model
{
    use HasFactory;

    protected $table = 'user_types';

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * RELACIÓN INVERSA: Un tipo de usuario puede tener muchos usuarios.
     * Esto te servirá para hacer consultas como: $userType->users
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'usertype_id');
    }
}