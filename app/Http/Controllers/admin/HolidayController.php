<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class HolidayController extends Controller
{
    

    public function index(Request $request)
    {
        // 1. VALIDACIÓN EN VIVO: Duplicados desde el formulario del modal
        if ($request->has('check_date')) {
            $checkDate = $request->check_date;
            $holidayId = $request->holiday_id;

            $duplicados = Holiday::where('date', $checkDate)
                ->when($holidayId, function($query) use ($holidayId) {
                    $query->where('id', '!=', $holidayId);
                })->count();

            if ($duplicados > 0) {
                return response()->json([
                    'status' => 'duplicated',
                    'message' => 'La fecha seleccionada ya ha sido registrada anteriormente.'
                ]);
            }
            return response()->json(['status' => 'available']);
        }

        // 2. 🎯 SOLUCIÓN CAJAS VACÍAS: Devolver métricas en JSON según los filtros
        if ($request->has('action') && $request->action == 'get_metrics') {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $status = $request->get('status');

            $query = Holiday::query()
                ->when($startDate, function($q) use ($startDate) { $q->where('date', '>=', $startDate); })
                ->when($endDate, function($q) use ($endDate) { $q->where('date', '<=', $endDate); })
                ->when($status !== null && $status !== '', function($q) use ($status) { $q->where('active', $status); });

            return response()->json([
                'total' => (clone $query)->count(),
                'active' => (clone $query)->where('active', 1)->count(),
                'inactive' => (clone $query)->where('active', 0)->count(),
            ]);
        }

        // 3. Captura de los filtros avanzados para la tabla Yajra DataTables
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $status = $request->get('status');

        if ($request->ajax()) {
            $holidays = Holiday::query()
                ->when($startDate, function($query) use ($startDate) {
                    $query->where('date', '>=', $startDate);
                })
                ->when($endDate, function($query) use ($endDate) {
                    $query->where('date', '<=', $endDate);
                })
                ->when($status !== null && $status !== '', function($query) use ($status) {
                    $query->where('active', $status);
                });

            return DataTables::of($holidays)
                ->editColumn('date', function ($holiday) {
                    return Carbon::parse($holiday->date)->format('d/m/Y');
                })
                ->addColumn('badge_status', function ($holiday) {
                    if ($holiday->active) {
                        return '<span class="badge bg-success text-white px-2.5 py-1.5 rounded-xl font-weight-bold shadow-sm"><i class="fas fa-check-circle mr-1"></i>ACTIVO</span>';
                    } else {
                        return '<span class="badge bg-secondary text-white px-2.5 py-1.5 rounded-xl font-weight-bold shadow-sm"><i class="fas fa-times-circle mr-1"></i>INACTIVO</span>';
                    }
                })
                ->addColumn('day_name', function ($holiday) {
                    return ucfirst(Carbon::parse($holiday->date)->locale('es')->dayName);
                })
                ->addColumn('actions', function ($holiday) {
                    $btnEdit = '<button class="btn btn-sm btn-warning btn-editar mr-1" id="' . $holiday->id . '" title="Editar Feriado"><i class="fas fa-pen text-dark"></i></button>';
                    $btnDelete = '<form action="' . route('admin.holiday.destroy', $holiday->id) . '" method="POST" class="frmEliminar" style="display:inline; margin:0; padding:0;">' 
                        . method_field('DELETE') . csrf_field() . 
                        '<button class="btn btn-sm btn-danger" type="submit" title="Eliminar Feriado"><i class="fas fa-trash-alt text-white"></i></button></form>';
                    return $btnEdit . $btnDelete;
                })
                ->rawColumns(['badge_status', 'actions'])
                ->make(true);
        }

        // 4. Carga inicial de la vista
        $totalHolidays = Holiday::count();
        $activeHolidays = Holiday::where('active', 1)->count();
        $inactiveHolidays = Holiday::where('active', 0)->count();
        $currentYear = date('Y');

        return view('admin.holidays.index', compact('totalHolidays', 'activeHolidays', 'inactiveHolidays', 'currentYear'));
    }

    public function create()
    {
        return view('admin.holidays.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'date'        => 'required|date|unique:holidays,date',
                'description' => 'required|max:255',
                'active'      => 'required|boolean'
            ]);

            Holiday::create($request->all());
            return response()->json(['message' => 'Día feriado registrado correctamente.'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'La fecha seleccionada ya se encuentra registrada.'], 422);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $holiday = Holiday::findOrFail($id);
        return view('admin.holidays.edit', compact('holiday'));
    }

    public function update(Request $request, $id)
    {
        try {
            $holiday = Holiday::findOrFail($id);

            $request->validate([
                'date'        => 'required|date|unique:holidays,date,' . $id,
                'description' => 'required|max:255',
                'active'      => 'required|boolean'
            ]);

            $holiday->update($request->all());
            return response()->json(['message' => 'Día feriado actualizado correctamente.'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'La fecha seleccionada colisiona con otro registro.'], 422);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $holiday = Holiday::findOrFail($id);
            $holiday->delete();
            return response()->json(['message' => 'Día feriado removido correctamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }
}