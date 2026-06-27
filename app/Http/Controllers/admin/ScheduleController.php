<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $schedules = Schedule::all();

        if ($request->ajax()) {
            return DataTables::of($schedules)
                ->addColumn('time_start', function ($s) {
                    return '<span class="badge badge-success px-2 py-1">' . $s->time_start . '</span>';
                })
                ->addColumn('time_end', function ($s) {
                    return '<span class="badge badge-danger px-2 py-1">' . $s->time_end . '</span>';
                })
                ->addColumn('actions', function ($s) {
                    return '
                        <button class="btn btn-sm btn-warning btn-editar" id="' . $s->id . '" title="Editar">
                            <i class="fas fa-pen"></i>
                        </button>
                        <form action="' . route('admin.schedule.destroy', $s->id) . '" method="POST"
                            class="frmEliminar d-inline">' . method_field('DELETE') . csrf_field() . '
                            <button class="btn btn-sm btn-danger" type="submit" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>';
                })
                ->rawColumns(['time_start', 'time_end', 'actions'])
                ->make(true);
        }

        return view('admin.schedules.index', compact('schedules'));
    }

    public function create()
    {
        return view('admin.schedules.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'       => 'required|unique:schedules,name',
                'time_start' => 'required',
                'time_end'   => 'required|different:time_start',
            ]);

            Schedule::create([
                'name'        => $request->name,
                'time_start'  => $request->time_start,
                'time_end'    => $request->time_end,
                'description' => $request->description,
            ]);

            return response()->json(['message' => 'Turno registrado correctamente'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = implode(' | ', $e->validator->errors()->all());
            return response()->json(['message' => $errors], 422);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function edit(string $id)
    {
        $schedule = Schedule::findOrFail($id);
        return view('admin.schedules.edit', compact('schedule'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $schedule = Schedule::findOrFail($id);

            $request->validate([
                'name'       => 'required|unique:schedules,name,' . $id,
                'time_start' => 'required',
                'time_end'   => 'required|different:time_start',
            ]);

            $schedule->update([
                'name'        => $request->name,
                'time_start'  => $request->time_start,
                'time_end'    => $request->time_end,
                'description' => $request->description,
            ]);

            return response()->json(['message' => 'Turno actualizado correctamente'], 200);
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
            $schedule = Schedule::findOrFail($id);
            $schedule->delete();
            return response()->json(['message' => 'Turno eliminado correctamente'], 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    // Retorna el turno correspondiente a una hora dada
    public function getByTime(Request $request)
    {
        $time = $request->time ?? now()->format('H:i');

        $schedule = Schedule::all()->first(function ($s) use ($time) {
            $start = $s->time_start;
            $end   = $s->time_end;

            if ($start <= $end) {
                return $time >= $start && $time <= $end;
            } else {
                // Turno que cruza medianoche
                return $time >= $start || $time <= $end;
            }
        });

        return response()->json($schedule);
    }
}