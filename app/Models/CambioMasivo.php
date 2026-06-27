<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CambioMasivo extends Model
{
    protected $table = 'cambios_masivos';

    protected $fillable = [
        'tipo_cambio',
        'fecha_inicio',
        'fecha_fin',
        'zone_id',
        'valor_anterior_id',
        'valor_nuevo_id',
        'cambio_id',
        'descripcion',
        'user_id',
        'afectadas'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function motivo()
    {
        return $this->belongsTo(Cambios::class, 'cambio_id');
    }

    public function ejecutadoPor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ── Helpers para resolver el nombre del valor anterior/nuevo ──

    public function resolverValorAnterior(): string
    {
        return $this->resolverValor($this->valor_anterior_id);
    }

    public function resolverValorNuevo(): string
    {
        return $this->resolverValor($this->valor_nuevo_id);
    }

    private function resolverValor(?int $id): string
    {
        if (!$id) return '—';

        return match ($this->tipo_cambio) {
            'turno'    => optional(Schedule::find($id))->name ?? "#{$id}",
            'vehiculo' => optional(Vehicle::find($id))->code ?? "#{$id}",
            default    => optional(User::find($id))->name ?? "#{$id}", // conductor / ocupante
        };
    }

    // ── Badge color por tipo ───────────────────────────────────
    public static function colorTipo(string $tipo): string
    {
        return match ($tipo) {
            'turno'     => '#F59E0B',
            'conductor' => '#3B82F6',
            'ocupante'  => '#8B5CF6',
            'vehiculo'  => '#10B981',
            default     => '#6B7280',
        };
    }

    public static function labelTipo(string $tipo): string
    {
        return match ($tipo) {
            'turno'     => 'Turno',
            'conductor' => 'Conductor',
            'ocupante'  => 'Ocupante',
            'vehiculo'  => 'Vehículo',
            default     => $tipo,
        };
    }
}