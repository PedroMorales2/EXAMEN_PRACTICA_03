<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\MaintenanceDayGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class MaintenanceScheduleController extends Controller
{
    public function __construct(
        private MaintenanceDayGeneratorService $dayGenerator
    ) {}

    // ──────────────────────────────────────────────────────────
    // INDEX — horarios de un mantenimiento (DataTable)
    // ──────────────────────────────────────────────────────────
    public function index(Request $request, Maintenance $maintenance)
    {
        if ($request->ajax()) {
            $schedules = MaintenanceSchedule::with(['vehicle', 'responsible'])
                ->where('maintenance_id', $maintenance->id)
                ->select('maintenance_schedules.*');

            return DataTables::of($schedules)
                ->addColumn('weekday_name', fn($s) => $s->weekday_name)
                ->addColumn('vehicle_name', fn($s) => $s->vehicle->name ?? '-')
                ->addColumn('responsible_name', fn($s) => $s->responsible->name ?? '-')
                ->addColumn('type_fmt', fn($s) => ucfirst(strtolower($s->type)))
                ->addColumn('start_fmt', fn($s) => \Carbon\Carbon::parse($s->start_time)->format('h:i a'))
                ->addColumn('end_fmt', fn($s) => \Carbon\Carbon::parse($s->end_time)->format('h:i a'))
                ->addColumn('ver', function ($s) {
                    return '<a href="' . route('admin.maintenance-day.index', $s->id) . '"
                               class="btn btn-sm btn-primary" title="Ver días">
                                <i class="fas fa-car-side text-white"></i>
                            </a>';
                })
                ->addColumn('edit', function ($s) {
                    return '<button class="btn btn-sm btn-warning btn-editar"
                                    data-id="' . $s->id . '" title="Editar">
                                <i class="fas fa-pen text-dark"></i>
                            </button>';
                })
                ->addColumn('delete', function ($s) {
                    return '<button class="btn btn-sm btn-danger btn-eliminar"
                                    data-id="' . $s->id . '" title="Eliminar">
                                <i class="fas fa-trash-alt text-white"></i>
                            </button>';
                })
                ->rawColumns(['ver', 'edit', 'delete'])
                ->make(true);
        }

        return view('admin.maintenance_schedules.index', compact('maintenance'));
    }

    // ──────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────
    public function create(Maintenance $maintenance)
    {
        $vehicles = Vehicle::orderBy('name')->get();
        $users    = User::orderBy('name')->get();

        return view('admin.maintenance_schedules.template.form', compact('maintenance', 'vehicles', 'users'));
    }

    // ──────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────
    public function store(Request $request, Maintenance $maintenance)
    {
        try {
            $data = $this->validateData($request);
            $data['maintenance_id'] = $maintenance->id;
            $this->ensureNoScheduleOverlap($data);

            DB::transaction(function () use ($maintenance, $data) {
                $schedule = $maintenance->schedules()->create($data);
                $this->dayGenerator->generate($schedule);
            });

            return response()->json(['message' => 'Horario registrado y días generados correctamente.'], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => implode(' ', array_merge(...array_values($e->errors()))),
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────
    public function edit(MaintenanceSchedule $schedule)
    {
        $maintenance = $schedule->maintenance;
        $vehicles    = Vehicle::orderBy('name')->get();
        $users       = User::orderBy('name')->get();

        return view('admin.maintenance_schedules.template.form',
            compact('schedule', 'maintenance', 'vehicles', 'users'));
    }

    // ──────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────
    public function update(Request $request, MaintenanceSchedule $schedule)
    {
        try {
            $data = $this->validateData($request);
            $data['maintenance_id'] = $schedule->maintenance_id;
            $this->ensureNoScheduleOverlap($data, $schedule->id);

            DB::transaction(function () use ($schedule, $data) {
                $weekdayChanged = (int) $schedule->weekday !== (int) $data['weekday'];
                $schedule->update($data);

                // Si cambió el día de la semana, se regeneran los días de detalle.
                if ($weekdayChanged) {
                    $this->dayGenerator->regenerate($schedule->fresh());
                }
            });

            return response()->json(['message' => 'Horario actualizado correctamente.'], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => implode(' ', array_merge(...array_values($e->errors()))),
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // DESTROY  (cascade BD elimina los días generados)
    // ──────────────────────────────────────────────────────────
    public function destroy(MaintenanceSchedule $schedule)
    {
        try {
            DB::transaction(function () use ($schedule) {
                // Elimina imágenes físicas de los días antes del borrado en cascada.
                foreach ($schedule->days as $day) {
                    if ($day->image_path) {
                        \Storage::disk('public')->delete($day->image_path);
                    }
                }
                $schedule->delete();
            });

            return response()->json(['message' => 'Horario y sus días eliminados correctamente.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────
    private function validateData(Request $request): array
    {
        return $request->validate([
            'vehicle_id'     => 'required|exists:vehicles,id',
            'responsible_id' => 'required|exists:users,id',
            'type'           => 'required|in:' . implode(',', MaintenanceSchedule::TYPES),
            'weekday'        => 'required|integer|between:1,7',
            'start_time'     => 'required|date_format:H:i',
            'end_time'       => 'required|date_format:H:i|after:start_time',
        ], [
            'vehicle_id.required'     => 'El vehículo es obligatorio.',
            'responsible_id.required' => 'El responsable es obligatorio.',
            'type.required'           => 'El tipo de mantenimiento es obligatorio.',
            'weekday.required'        => 'El día de la semana es obligatorio.',
            'start_time.required'     => 'La hora de inicio es obligatoria.',
            'end_time.required'       => 'La hora de fin es obligatoria.',
            'end_time.after'          => 'La hora de fin debe ser mayor a la hora de inicio.',
        ]);
    }

    /**
     * No se permiten horarios solapados para el MISMO vehículo en el MISMO día
     * de la semana cuando las horas se cruzan.
     * Dos intervalos [s,e] y [s2,e2] se cruzan si: s < e2 AND s2 < e
     * (el toque exacto, p.ej. 11:00 tras 08:00-11:00, NO se considera solape).
     */
    private function ensureNoScheduleOverlap(array $data, $ignoreId = null): void
    {
        $overlap = MaintenanceSchedule::query()
            ->where('maintenance_id', $data['maintenance_id'])
            ->where('vehicle_id', $data['vehicle_id'])
            ->where('weekday', $data['weekday'])
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'start_time' => 'El vehículo ya tiene un horario que se cruza ese día en ese rango de horas.',
            ]);
        }
    }
}
