<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Vacation;
use App\Models\User;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class VacationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $vacations = Vacation::with('user')->select('vacations.*');

            return DataTables::of($vacations)
                ->addColumn('dni', function ($vacation) {
                    return $vacation->user->dni ?? 'Sin DNI';
                })
                ->addColumn('employee_name', function ($vacation) {
                    return $vacation->user->name;
                })
                ->addColumn('available_days', function ($vacation) {
                    return '<span class="badge badge-light border px-2.5 py-1.5 font-weight-bold shadow-sm" style="color: #071D38;">' . $vacation->user_available_days_at_moment . ' días</span>';
                })
                ->addColumn('badge_status', function ($vacation) {
                    if ($vacation->status === 'PENDIENTE') {
                        return '<span class="badge bg-warning text-dark px-2.5 py-1.5 rounded-xl font-weight-bold shadow-sm"><i class="fas fa-clock mr-1"></i>PENDIENTE</span>';
                    } elseif ($vacation->status === 'APROBADA') {
                        return '<span class="badge bg-success text-white px-2.5 py-1.5 rounded-xl font-weight-bold shadow-sm"><i class="fas fa-check-circle mr-1"></i>APROBADA</span>';
                    } else {
                        return '<span class="badge bg-danger text-white px-2.5 py-1.5 rounded-xl font-weight-bold shadow-sm"><i class="fas fa-times-circle mr-1"></i>RECHAZADA</span>';
                    }
                })
                ->addColumn('actions', function ($vacation) {
                    $btnApprove = ''; $btnReject = ''; $btnEdit = '';

                    if ($vacation->status === 'PENDIENTE') {
                        $btnApprove = '<button class="btn btn-sm btn-success btn-aprobar mr-1" id="' . $vacation->id . '" title="Aprobar"><i class="fas fa-check text-white"></i></button>';
                        $btnReject = '<button class="btn btn-sm btn-danger btn-rechazar mr-1" id="' . $vacation->id . '" title="Rechazar"><i class="fas fa-ban text-white"></i></button>';
                        $btnEdit = '<button class="btn btn-sm btn-warning btn-editar mr-1" id="' . $vacation->id . '" title="Editar"><i class="fas fa-pen text-dark"></i></button>';
                    }

                    $btnDelete = '<form action="' . route('admin.vacation.destroy', $vacation->id) . '" method="POST" class="frmEliminar" style="display:inline; margin:0; padding:0;">' 
                        . method_field('DELETE') . csrf_field() . 
                        '<button class="btn btn-sm btn-secondary" type="submit" title="Eliminar"><i class="fas fa-trash-alt text-white"></i></button></form>';
                    
                    return $btnApprove . $btnReject . $btnEdit . $btnDelete;
                })
                ->rawColumns(['available_days', 'badge_status', 'actions'])
                ->make(true);
        }

        return view('admin.vacations.index');
    }

    public function create()
    {
        $users = User::whereHas('contracts', function($query) {
            $query->where('active', true)->whereIn('type', ['Nombrado', 'Permanente']);
        })->get();

        return view('admin.vacations.create', compact('users'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'user_id'    => 'required',
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
            ]);

            $user = User::findOrFail($request->user_id);
            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);
            
            $primerDiaMesActual = Carbon::now()->startOfMonth();
            $ultimoDiaAnioSiguiente = Carbon::now()->addYear()->endOfYear();

            if ($start->lt($primerDiaMesActual) || $end->gt($ultimoDiaAnioSiguiente)) {
                return response()->json(['message' => 'Rango de fechas fuera del periodo académico/laboral permitido.'], 422);
            }

            $daysRequested = $start->diffInDays($end) + 1;
            
            $anioSeleccionado = $start->year;
            
            $diasConsumidosAnio = Vacation::where('user_id', $user->id)
                ->whereYear('start_date', $anioSeleccionado)
                ->whereIn('status', ['PENDIENTE', 'APROBADA'])
                ->sum('days');

            $diasRestantesReales = 30 - $diasConsumidosAnio;

            if ($diasRestantesReales <= 0) {
                return response()->json(['message' => 'El usuario ya cumplió con sus 30 días de vacaciones acumuladas para el año ' . $anioSeleccionado . '.'], 422);
            }

            if ($daysRequested > $diasRestantesReales) {
                return response()->json([
                    'message' => 'Exceso de días para el año ' . $anioSeleccionado . '. Ya consumió ' . $diasConsumidosAnio . ' días, por lo que le resta un saldo de ' . $diasRestantesReales . ' día(s).'
                ], 422);
            }

            // Evitar cruce o colisión de días en la base de datos
            $coincidencias = Vacation::where('user_id', $user->id)
                ->whereIn('status', ['PENDIENTE', 'APROBADA'])
                ->where(function($query) use ($request) {
                    $query->where('start_date', '<=', $request->end_date)
                          ->where('end_date', '>=', $request->start_date);
                })->count();

            if ($coincidencias > 0) {
                return response()->json(['message' => 'El rango seleccionado colisiona con otra solicitud existente.'], 422);
            }

            Vacation::create([
                'user_id'       => $request->user_id,
                'request_date'  => Carbon::now()->toDateString(),
                'start_date'    => $request->start_date,
                'end_date'      => $request->end_date,
                'days'          => $daysRequested,
                'status'        => 'PENDIENTE',
                'user_available_days_at_moment' => $diasRestantesReales - $daysRequested, // Guardamos el saldo final proyectado para ese año
                'notes'         => $request->notes
            ]);

            return response()->json(['message' => 'Solicitud de vacaciones procesada en estado PENDIENTE para el año ' . $anioSeleccionado . '.'], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el servidor: ' . $th->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $vacation = Vacation::findOrFail($id);
        $users = User::whereHas('contracts', function($query) {
            $query->where('active', true)->whereIn('type', ['Nombrado', 'Permanente']);
        })->get();
        return view('admin.vacations.edit', compact('vacation', 'users'));
    }

    public function update(Request $request, $id)
    {
        try {
            $vacation = Vacation::findOrFail($id);
            if ($vacation->status !== 'PENDIENTE') {
                return response()->json(['message' => 'Solo se pueden modificar solicitudes pendientes.'], 422);
            }

            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);
            $daysRequested = $start->diffInDays($end) + 1;
            
            $anioSeleccionado = $start->year;

            // Recalcular consumo del año omitiendo la solicitud actual que se edita
            $diasConsumidosOtros = Vacation::where('user_id', $vacation->user_id)
                ->where('id', '!=', $vacation->id)
                ->whereYear('start_date', $anioSeleccionado)
                ->whereIn('status', ['PENDIENTE', 'APROBADA'])
                ->sum('days');

            $diasRestantesReales = 30 - $diasConsumidosOtros;

            if ($daysRequested > $diasRestantesReales) {
                return response()->json(['message' => 'La actualización excede la bolsa de días del año ' . $anioSeleccionado . '. Disponible: ' . $diasRestantesReales . ' días.'], 422);
            }

            $vacation->update([
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
                'days'       => $daysRequested,
                'user_available_days_at_moment' => $diasRestantesReales - $daysRequested,
                'notes'      => $request->notes
            ]);

            return response()->json(['message' => 'Solicitud actualizada correctamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $vacation = Vacation::findOrFail($id);
            if ($vacation->status !== 'PENDIENTE') {
                return response()->json(['message' => 'Esta solicitud ya no está pendiente.'], 422);
            }

            $vacation->status = 'APROBADA';
            $vacation->save();

            DB::commit();
            return response()->json(['message' => 'Solicitud aprobada de forma conforme para su año respectivo.'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function reject($id)
    {
        try {
            $vacation = Vacation::findOrFail($id);
            if ($vacation->status !== 'PENDIENTE') {
                return response()->json(['message' => 'Esta solicitud ya no está pendiente.'], 422);
            }

            // Si es rechazada, restauramos el saldo volviendo a sumar los días que no usará
            $vacation->user_available_days_at_moment += $vacation->days;
            $vacation->status = 'RECHAZADA';
            $vacation->save();

            return response()->json(['message' => 'Solicitud rechazada correctamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $vacation = Vacation::findOrFail($id);
            $vacation->delete();
            return response()->json(['message' => 'Solicitud removida del sistema.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function checkLive(Request $request)
    {
        $userId = $request->user_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $vacationId = $request->vacation_id; 

        if (!$userId) {
            return response()->json(['status' => 'waiting', 'message' => 'Selecciona un usuario para calcular su saldo de días.']);
        }

        // Si no hay fecha de inicio seleccionada aún, damos un mensaje general indicando que cada año tiene 30 días
        if (!$startDate) {
            return response()->json([
                'status' => 'user_selected',
                'message' => 'Usuario seleccionado. El sistema renovará de forma automática <b>30 días libres</b> por cada año programado.'
            ]);
        }

        // Extraemos el año dinámicamente de la fecha ingresada
        $start = Carbon::parse($startDate);
        $anioSeleccionado = $start->year;

        // Calculamos los días consumidos por el usuario ÚNICAMENTE dentro de ese año específico
        $diasConsumidosAnio = Vacation::where('user_id', $userId)
            ->whereYear('start_date', $anioSeleccionado)
            ->whereIn('status', ['PENDIENTE', 'APROBADA'])
            ->when($vacationId, function($query) use ($vacationId) {
                $query->where('id', '!=', $vacationId);
            })
            ->sum('days');

        $diasRestantesReales = 30 - $diasConsumidosAnio;

        $fechaMaximaSugerida = $start->copy()->addDays($diasRestantesReales - 1)->toDateString();

        if (!$endDate) {
            return response()->json([
                'status' => 'suggest_end',
                'available_days' => $diasRestantesReales,
                'suggested_end_date' => $fechaMaximaSugerida,
                'message' => 'Calculando rango máximo para el año <b>' . $anioSeleccionado . '</b> (' . $diasRestantesReales . ' días disponibles).'
            ]);
        }

        $end = Carbon::parse($endDate);
        if ($end->lt($start)) {
            return response()->json(['status' => 'error', 'message' => '❌ Error: La fecha de término no puede ser menor a la de inicio.']);
        }

        if ($end->year !== $anioSeleccionado) {
            return response()->json(['status' => 'error', 'message' => '❌ Las vacaciones deben solicitarse y cerrarse dentro del mismo año fiscal. No cruce periodos.']);
        }

        $daysRequested = $start->diffInDays($end) + 1;

        if ($daysRequested > $diasRestantesReales) {
            return response()->json([
                'status' => 'error',
                'message' => '⚠️ ¡Límite excedido para el año ' . $anioSeleccionado . '! Al usuario solo le restan ' . $diasRestantesReales . ' días libres en este periodo, pero intentas pedir ' . $daysRequested . ' días.'
            ]);
        }

        // Comprobación de intersección de periodos
        $coincidencias = Vacation::where('user_id', $userId)
            ->whereIn('status', ['PENDIENTE', 'APROBADA'])
            ->when($vacationId, function($query) use ($vacationId) {
                $query->where('id', '!=', $vacationId);
            })
            ->where(function($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                      ->where('end_date', '>=', $startDate);
            })->count();

        if ($coincidencias > 0) {
            return response()->json([
                'status' => 'error',
                'message' => '❌ Conflicto de fechas: El periodo seleccionado se cruza con una solicitud ya registrada para este usuario.'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'available_days' => $diasRestantesReales,
            'days_requested' => $daysRequested,
            'message' => '✓ Periodo disponible para el año <b>' . $anioSeleccionado . '</b>. Se registrarán <b>' . $daysRequested . ' día(s)</b> de vacaciones conforme a ley.'
        ]);
    }
}