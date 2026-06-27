<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramacionCambio extends Model
{
    protected $table = 'programacion_cambios';

    protected $fillable = [
        'programacion_id',
        'user_id',
        'campo',
        'valor_anterior',
        'valor_nuevo',
        'motivo',
    ];

    public function programacion()
    {
        return $this->belongsTo(Programacion::class, 'programacion_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cambioMasivo()
{
    return $this->belongsTo(CambioMasivo::class, 'cambio_masivo_id');
}
}