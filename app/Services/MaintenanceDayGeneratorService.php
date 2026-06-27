<?php

namespace App\Services;

use App\Models\MaintenanceSchedule;
use Carbon\Carbon;

/**
 * Genera automáticamente los días de detalle de un horario de mantenimiento,
 * tomando todas las fechas dentro del rango del mantenimiento que coincidan
 * con el día de la semana (ISO 1=Lunes ... 7=Domingo) del horario.
 *
 * Ejemplo: Mantenimiento "DICIEMBRE 2025" (01/12 - 31/12) + horario LUNES
 *          => genera 01, 08, 15, 22 y 29 de diciembre.
 */
class MaintenanceDayGeneratorService
{
    /**
     * Crea los registros de días para un horario recién creado.
     */
    public function generate(MaintenanceSchedule $schedule): void
    {
        $maintenance = $schedule->maintenance;

        $start = Carbon::parse($maintenance->start_date)->startOfDay();
        $end   = Carbon::parse($maintenance->end_date)->startOfDay();

        $rows = [];
        $now  = now();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->dayOfWeekIso === (int) $schedule->weekday) {
                $rows[] = [
                    'maintenance_schedule_id' => $schedule->id,
                    'date'       => $date->toDateString(),
                    'completed'  => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (! empty($rows)) {
            $schedule->days()->insert($rows);
        }
    }

    /**
     * Regenera los días de un horario (al editar el día de la semana o el rango).
     * Elimina los días previos y vuelve a generarlos.
     */
    public function regenerate(MaintenanceSchedule $schedule): void
    {
        $schedule->days()->delete();
        $this->generate($schedule);
    }
}
