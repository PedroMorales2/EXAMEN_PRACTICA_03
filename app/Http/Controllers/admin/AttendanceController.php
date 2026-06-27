<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Attendance::with('user', 'schedule');

            // Filtros de búsqueda
            if ($request->filled('start_date')) {
                $query->whereDate('date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('date', '<=', $request->end_date);
            }
            if ($request->filled('search_employee')) {
                $search = $request->search_employee;
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('dni', 'like', "%$search%");
                });
            }

            $attendances = $query->orderBy('date', 'desc')->orderBy('time', 'desc')->get();

            return DataTables::of($attendances)
                ->addColumn('dni', function ($a) {
                    return $a->user ? $a->user->dni : '-';
                })
                ->addColumn('employee', function ($a) {
                    return $a->user ? $a->user->name : '-';
                })
                ->addColumn('datetime', function ($a) {
                    $fecha = $a->date instanceof Carbon ? $a->date->format('d/m/Y') : Carbon::parse($a->date)->format('d/m/Y');
                    return $fecha . '<br><small class="text-muted">' . substr($a->time, 0, 5) . '</small>';
                })
                ->addColumn('type', function ($a) {
                    $color = $a->type === 'Entrada' ? 'success' : 'info';
                    return '<span class="badge badge-' . $color . '">' . $a->type . '</span>';
                })
                ->addColumn('status', function ($a) {
                    $color = $a->status === 'Presente' ? 'success' : 'danger';
                    return '<span class="badge badge-' . $color . '">' . $a->status . '</span>';
                })
                ->addColumn('schedule_name', function ($a) {
                    return $a->schedule ? $a->schedule->name : '-';
                })
                ->addColumn('actions', function ($a) {
                    return '
                        <button class="btn btn-sm btn-warning btn-editar" id="' . $a->id . '" title="Editar">
                            <i class="fas fa-pen"></i>
                        </button>
                        <form action="' . route('admin.attendance.destroy', $a->id) . '" method="POST"
                            class="frmEliminar d-inline">' . method_field('DELETE') . csrf_field() . '
                            <button class="btn btn-sm btn-danger" type="submit" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>';
                })
                ->rawColumns(['datetime', 'type', 'status', 'actions'])
                ->make(true);
        }

        return view('admin.attendances.index');
    }

    public function create()
    {
        $users = User::select('id', 'name', 'dni')
        ->whereHas('contracts') 
        ->orderBy('name')
        ->get();
        $schedules = Schedule::orderBy('time_start')->get();
        $now       = Carbon::now();
        $currentSchedule = $this->detectSchedule($now->format('H:i'));

        return view('admin.attendances.create', compact('users', 'schedules', 'currentSchedule', 'now'));
    }

    public function store(Request $request)
{
    try {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'date'        => 'required|date',
            'time'        => 'required',
            'schedule_id' => 'required|exists:schedules,id',
            'status'      => 'required|in:Presente,Ausente',
        ]);

        // NUEVA LÓGICA: Contamos cuántas asistencias ya tiene el usuario en este día
        $conteoRegistros = Attendance::where('user_id', $request->user_id)
            ->whereDate('date', $request->date)
            ->count();

        // Si el conteo es PAR (0, 2, 4...), toca ENTRADA. Si es IMPAR (1, 3, 5...), toca SALIDA.
        $tipoCalculado = ($conteoRegistros % 2 === 0) ? 'Entrada' : 'Salida';

        Attendance::create([
            'user_id'     => $request->user_id,
            'date'        => $request->date,
            'time'        => $request->time,
            'schedule_id' => $request->schedule_id,
            'type'        => $tipoCalculado,
            'status'      => $request->status,
            'notes'       => $request->notes,
        ]);

        return response()->json(['message' => 'Asistencia registrada correctamente'], 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
        $errors = implode(' | ', $e->validator->errors()->all());
        return response()->json(['message' => $errors], 422);
    } catch (\Throwable $th) {
        Log::error($th);
        return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
    }
}

public function getAttendanceType(Request $request)
{
    $userId = $request->user_id;
    $date   = $request->date ?? Carbon::now()->toDateString();

    if (!$userId) {
        return response()->json(['type' => 'Automático']);
    }

    // 1. Obtener los datos del empleado
    $user = User::find($userId);

    // 2. Obtener las asistencias registradas para este usuario en el día seleccionado
    $asistenciasDelDia = Attendance::where('user_id', $userId)
        ->whereDate('date', $date)
        ->orderBy('time', 'asc')
        ->get();

    $conteoRegistros = $asistenciasDelDia->count();
    $type = ($conteoRegistros % 2 === 0) ? 'Entrada' : 'Salida';

    // 3. Formatear la cadena de texto con las horas guardadas (ejemplo: "Entrada (08:30), Salida (13:15)")
    if ($conteoRegistros > 0) {
        $logStrings = [];
        foreach ($asistenciasDelDia as $asistencia) {
            $logStrings[] = $asistencia->type . ' (' . substr($asistencia->time, 0, 5) . ')';
        }
        $historialTexto = implode(', ', $logStrings);
    } else {
        $historialTexto = 'No hay registros para este día.';
    }

    return response()->json([
        'user_name'  => $user->name ?? 'No registrado',
        'user_dni'   => $user->dni ?? 'No registrado',
        'user_email' => $user->email ?? 'No registrado',
        'user_phone' => $user->phone ?? 'No registrado',
        'type'       => $type,
        'historial'  => $historialTexto,
        'sugerencia' => $conteoRegistros === 0 
                        ? 'Primer registro del día - debe ser ENTRADA' 
                        : 'Siguiente registro correlativo - debe ser ' . strtoupper($type)
    ]);
}

    public function edit(string $id)
    {
        $attendance = Attendance::findOrFail($id);
        $users      = User::select('id', 'name', 'dni')->orderBy('name')->get();
        $schedules  = Schedule::orderBy('time_start')->get();

        return view('admin.attendances.edit', compact('attendance', 'users', 'schedules'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $attendance = Attendance::findOrFail($id);

            $request->validate([
                'user_id'     => 'required|exists:users,id',
                'date'        => 'required|date',
                'time'        => 'required',
                'schedule_id' => 'required|exists:schedules,id',
                'type'        => 'required|in:Entrada,Salida',
                'status'      => 'required|in:Presente,Ausente',
            ]);

            $attendance->update([
                'user_id'     => $request->user_id,
                'date'        => $request->date,
                'time'        => $request->time,
                'schedule_id' => $request->schedule_id,
                'type'         => $request->type,
                'status'      => $request->status,
                'notes'       => $request->notes,
            ]);

            return response()->json(['message' => 'Asistencia actualizada correctamente'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = implode(' | ', $e->validator->errors()->all());
            return response()->json(['message' => $errors], 422);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            Attendance::findOrFail($id)->delete();
            return response()->json(['message' => 'Asistencia eliminada correctamente'], 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function getScheduleByTime(Request $request)
    {
        $time     = $request->time ?? Carbon::now()->format('H:i');
        $schedule = $this->detectSchedule($time);
        return response()->json($schedule);
    }

   

    public function getUserInfo(Request $request)
    {
        $user = User::find($request->user_id);
        if (!$user) return response()->json(null);

        return response()->json([
            'name'  => $user->name,
            'dni'   => $user->dni,
        ]);
    }

    private function detectSchedule(string $time): ?Schedule
    {
        return Schedule::all()->first(function ($s) use ($time) {
            $start = $s->time_start;
            $end   = $s->time_end;
            if ($start <= $end) {
                return $time >= $start && $time <= $end;
            }
            return $time >= $start || $time <= $end;
        });
    }
}