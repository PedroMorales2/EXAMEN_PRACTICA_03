<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 


class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'dni',          // Agregado para permitir registro masivo
        'birthdate',    // Agregado para permitir registro masivo
        'license',      // Agregado para permitir registro masivo
        'address',      // Agregado para permitir registro masivo
        'usertype_id',  // Agregado para vincular el tipo de usuario
        'zone_id',      // Agregado para vincular la zona (si aplica)
        'profile_photo_path',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthdate' => 'date', // Convierte automáticamente la fecha a un objeto Carbon de PHP
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * RELACIÓN: Un usuario pertenece a un Tipo de Usuario
     * Esto te permitirá hacer cosas como: $user->userType->name
     */
    public function userType(): BelongsTo
    {
        // Vinculamos con la clase correspondiente indicando la llave foránea de la migración
        return $this->belongsTo(UserType::class, 'usertype_id');
    }


    public function contracts()
    {
        return $this->hasMany(Contract::class, 'user_id');
    }

    
    public function vacations()
    {
        return $this->hasMany(Vacation::class, 'user_id');
    }
}