<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $contracts = Contract::with('user')->get();

        if ($request->ajax()) {
            return DataTables::of($contracts)
                ->addColumn('dni', function ($contract) {
                    return $contract->user ? $contract->user->dni : '-';
                })
                ->addColumn('employee', function ($contract) {
                    return $contract->user ? $contract->user->name : '-';
                })
                ->addColumn('type', function ($contract) {
                    $colors = [
                        'Permanente' => 'primary',
                        'Nombrado'   => 'success',
                        'Temporal'   => 'warning',
                    ];
                    $color = $colors[$contract->type] ?? 'secondary';
                    return '<span class="badge badge-' . $color . '">' . $contract->type . '</span>';
                })
                ->addColumn('start_date', function ($contract) {
                    return $contract->start_date ? $contract->start_date->format('d/m/Y') : '-';
                })
                ->addColumn('end_date', function ($contract) {
                    return $contract->end_date ? $contract->end_date->format('d/m/Y') : '-';
                })
                ->addColumn('salary', function ($contract) {
                    return '<span class="text-success font-weight-bold">S/. ' . number_format($contract->salary, 2) . '</span>';
                })
                ->addColumn('active', function ($contract) {
                    return $contract->active
                        ? '<span class="badge badge-success">Activo</span>'
                        : '<span class="badge badge-danger">Inactivo</span>';
                })
                ->addColumn('actions', function ($contract) {
                    return '
                        <button class="btn btn-sm btn-warning btn-editar" id="' . $contract->id . '" title="Editar">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button class="btn btn-sm btn-' . ($contract->active ? 'danger' : 'success') . ' btn-toggle" 
                                data-id="' . $contract->id . '" 
                                title="' . ($contract->active ? 'Desactivar' : 'Activar') . '">
                            <i class="fas fa-' . ($contract->active ? 'ban' : 'check') . '"></i>
                        </button>';
                })
                ->rawColumns(['type', 'salary', 'active', 'actions'])
                ->make(true);
        }

        return view('admin.contracts.index', compact('contracts'));
    }

    public function create()
    {
        $users = User::select('id', 'name', 'dni')->orderBy('name')->get();
        return view('admin.contracts.create', compact('users'));
    }

    
    public function store(Request $request)
{
    try {
        $rules = [
            'user_id'      => 'required|exists:users,id',
            'type'         => 'required|in:Permanente,Nombrado,Temporal',
            'start_date'   => 'required|date',
            'salary'       => 'required|numeric|min:0',
            'trial_period' => 'nullable|integer|min:0',
        ];

        if ($request->type === 'Temporal') {
            $rules['end_date'] = [
                'required',
                'date',
                'after:start_date',
                function ($attribute, $value, $fail) use ($request) {
                    $start  = Carbon::parse($request->start_date);
                    $end    = Carbon::parse($value);
                    $months = $start->diffInMonths($end);
                    if ($months < 2) {
                        $fail('Los contratos temporales deben tener una duración mínima de 2 meses.');
                    }
                },
            ];
        } else {
            $rules['end_date'] = 'nullable|date';
        }

        $request->validate($rules);

        // ← VALIDACIÓN: no puede haber dos contratos activos para el mismo trabajador
        if ($request->has('active') && $request->active == 1) {
            $contratoActivo = Contract::where('user_id', $request->user_id)
                                      ->where('active', 1)
                                      ->first();

            if ($contratoActivo) {
                return response()->json([
                    'message' => 'Este trabajador ya tiene un contrato activo. Desactívelo antes de registrar uno nuevo.'
                ], 422);
            }
        }

        Contract::create([
            'user_id'      => $request->user_id,
            'type'         => $request->type,
            'start_date'   => $request->start_date,
            'end_date'     => in_array($request->type, ['Permanente', 'Nombrado']) ? null : $request->end_date,
            'salary'       => $request->salary,
            'trial_period' => $request->trial_period ?? 0,
            'active'       => $request->has('active') ? 1 : 0,
        ]);

        return response()->json(['message' => 'Contrato registrado correctamente'], 200);
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
        $contract = Contract::findOrFail($id);
        $users    = User::select('id', 'name', 'dni')->orderBy('name')->get();
        return view('admin.contracts.edit', compact('contract', 'users'));
    }

    
    public function update(Request $request, string $id)
{
    try {
        $contract = Contract::findOrFail($id);

        $rules = [
            'user_id'      => 'required|exists:users,id',
            'type'         => 'required|in:Permanente,Nombrado,Temporal',
            'start_date'   => 'required|date',
            'salary'       => 'required|numeric|min:0',
            'trial_period' => 'nullable|integer|min:0',
        ];

        if ($request->type === 'Temporal') {
            $rules['end_date'] = [
                'required',
                'date',
                'after:start_date',
                function ($attribute, $value, $fail) use ($request) {
                    $start  = Carbon::parse($request->start_date);
                    $end    = Carbon::parse($value);
                    $months = $start->diffInMonths($end);
                    if ($months < 2) {
                        $fail('Los contratos temporales deben tener una duración mínima de 2 meses.');
                    }
                },
            ];
        } else {
            $rules['end_date'] = 'nullable|date';
        }

        $request->validate($rules);

        // Validación: no puede activarse si ya hay otro contrato activo para el mismo trabajador
        if ($request->has('active') && $request->active == 1) {
            $contratoActivo = Contract::where('user_id', $request->user_id)
                                      ->where('active', 1)
                                      ->where('id', '!=', $id)
                                      ->first();

            if ($contratoActivo) {
                return response()->json([
                    'message' => 'Este trabajador ya tiene un contrato activo. Desactívelo antes de activar este.'
                ], 422);
            }
        }

        $contract->update([
            'user_id'      => $request->user_id,
            'type'         => $request->type,
            'start_date'   => $request->start_date,
            'end_date'     => in_array($request->type, ['Permanente', 'Nombrado']) ? null : $request->end_date,
            'salary'       => $request->salary,
            'trial_period' => $request->trial_period ?? 0,
            'active'       => $request->has('active') ? 1 : 0,
        ]);

        return response()->json(['message' => 'Contrato actualizado correctamente'], 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
        $errors = implode(' | ', $e->validator->errors()->all());
        return response()->json(['message' => $errors], 422);
    } catch (\Throwable $th) {
        Log::error($th);
        return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
    }
}

public function toggle(string $id)
{
    try {
        $contract = Contract::findOrFail($id);

        // Si se intenta activar, verificar que no haya otro contrato activo
        if (!$contract->active) {
            $contratoActivo = Contract::where('user_id', $contract->user_id)
                                      ->where('active', 1)
                                      ->where('id', '!=', $id)
                                      ->first();

            if ($contratoActivo) {
                return response()->json([
                    'message' => 'Este trabajador ya tiene un contrato activo. Desactívelo antes de activar este.'
                ], 422);
            }
        }

        $contract->update(['active' => !$contract->active]);
        $status = $contract->active ? 'activado' : 'desactivado';
        return response()->json(['message' => "Contrato $status correctamente"], 200);
    } catch (\Throwable $th) {
        Log::error($th);
        return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
    }
}






}