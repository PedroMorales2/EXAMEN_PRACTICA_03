<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class MaintenanceController extends Controller
{
    // ──────────────────────────────────────────────────────────
    // INDEX — DataTable
    // ──────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $maintenances = Maintenance::withCount('schedules')->select('maintenances.*');

            return DataTables::of($maintenances)
                ->addColumn('start_fmt', fn($m) => $m->start_date->format('d/m/Y'))
                ->addColumn('end_fmt', fn($m) => $m->end_date->format('d/m/Y'))
                ->addColumn('horarios', function ($m) {
                    return '<a href="' . route('admin.maintenance-schedule.index', $m->id) . '"
                               class="btn btn-sm btn-info" title="Horarios">
                                <i class="fas fa-calendar-alt text-white"></i>
                            </a>';
                })
                ->addColumn('edit', function ($m) {
                    return '<button class="btn btn-sm btn-warning btn-editar"
                                    data-id="' . $m->id . '" title="Editar">
                                <i class="fas fa-pen text-dark"></i>
                            </button>';
                })
                ->addColumn('delete', function ($m) {
                    return '<button class="btn btn-sm btn-danger btn-eliminar"
                                    data-id="' . $m->id . '"
                                    data-name="' . e($m->name) . '"
                                    data-count="' . $m->schedules_count . '" title="Eliminar">
                                <i class="fas fa-trash-alt text-white"></i>
                            </button>';
                })
                ->rawColumns(['horarios', 'edit', 'delete'])
                ->make(true);
        }

        return view('admin.maintenances.index');
    }

    // ──────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────
    public function create()
    {
        return view('admin.maintenances.template.form');
    }

    // ──────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        try {
            $data = $this->validateData($request);
            $this->ensureNoDateOverlap($data['start_date'], $data['end_date']);

            Maintenance::create($data);

            return response()->json(['message' => 'Mantenimiento registrado correctamente.'], 200);
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
    public function edit($id)
    {
        $maintenance = Maintenance::findOrFail($id);
        return view('admin.maintenances.template.form', compact('maintenance'));
    }

    // ──────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        try {
            $maintenance = Maintenance::findOrFail($id);

            $data = $this->validateData($request, $id);
            $this->ensureNoDateOverlap($data['start_date'], $data['end_date'], $id);

            $maintenance->update($data);

            return response()->json(['message' => 'Mantenimiento actualizado correctamente.'], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => implode(' ', array_merge(...array_values($e->errors()))),
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // DESTROY  (cascade BD elimina horarios y días asociados)
    // ──────────────────────────────────────────────────────────
    public function destroy($id)
    {
        try {
            Maintenance::findOrFail($id)->delete();
            return response()->json(['message' => 'Mantenimiento eliminado correctamente.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────
    private function validateData(Request $request, $id = null): array
    {
        return $request->validate([
            'name'       => 'required|string|max:150|unique:maintenances,name' . ($id ? ',' . $id : ''),
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ], [
            'name.required'           => 'El nombre es obligatorio.',
            'name.unique'             => 'Ya existe un mantenimiento con ese nombre.',
            'start_date.required'     => 'La fecha de inicio es obligatoria.',
            'end_date.required'       => 'La fecha de fin es obligatoria.',
            'end_date.after_or_equal' => 'La fecha de inicio no puede ser mayor a la fecha de fin.',
        ]);
    }

    /**
     * Valida que el rango de fechas no se solape con otro mantenimiento.
     * Dos rangos [s,e] y [s2,e2] se solapan si: s <= e2 AND s2 <= e.
     */
    private function ensureNoDateOverlap(string $start, string $end, $ignoreId = null): void
    {
        $overlap = Maintenance::query()
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'start_date' => 'El rango de fechas se solapa con otro mantenimiento registrado.',
            ]);
        }
    }
}
