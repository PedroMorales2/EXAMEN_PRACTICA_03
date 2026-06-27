<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Programacion;
use App\Models\ProgramacionCambio;
use App\Models\PersonalGroup;
use App\Models\User;
use App\Models\Vacation;
use App\Models\Contract;
use App\Models\Zone;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Feriado;
use App\Models\Vehicle;

class ProgramacionController extends Controller
{
    // ── Mapa día abreviado → número dayOfWeek de Carbon ───────
    // Carbon: 0=Dom, 1=Lun, 2=Mar, 3=Mié, 4=Jue, 5=Vie, 6=Sáb
    const DAY_MAP = [
        'Lun' => 1,
        'Mar' => 2,
        'Mié' => 3,
        'Jue' => 4,
        'Vie' => 5,
        'Sáb' => 6,
        'Dom' => 0,
    ];

    // ──────────────────────────────────────────────────────────
    // INDEX — DataTable (una fila por fecha concreta)
    // ──────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Programacion::with([
                'group',
                'zone',
                'schedule',
                'vehicle',
                'conductor',
                'ayudantes',
            ])->select('programaciones.*');

            if ($request->filled('start_date')) {
                $query->where('fecha', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->where('fecha', '<=', $request->end_date);
            }
            if ($request->filled('zone_id')) {
                $query->where('zone_id', $request->zone_id);
            }
            if ($request->filled('schedule_id')) {
                $query->where('schedule_id', $request->schedule_id);
            }

            return DataTables::of($query)
                ->addColumn('fecha_fmt', fn($p) => $p->fecha->format('d/m/Y'))
                ->addColumn('dia_semana', fn($p) => $p->fecha->translatedFormat('l'))
                ->addColumn('badge_status', function ($p) {
                    return match ($p->status) {
                        'Finalizado' => '<span class="badge bg-success text-white px-2 py-1 rounded"><i class="fas fa-check-circle mr-1"></i>Finalizado</span>',
                        'Cancelado' => '<span class="badge bg-danger text-white px-2 py-1 rounded"><i class="fas fa-ban mr-1"></i>Cancelado</span>',
                        'Reprogramado' => '<span class="badge bg-warning text-dark px-2 py-1 rounded"><i class="fas fa-calendar-alt mr-1"></i>Reprogramado</span>',
                        default => '<span class="badge bg-primary text-white px-2 py-1 rounded"><i class="fas fa-calendar-check mr-1"></i>Programado</span>',
                    };
                })
                ->addColumn('zona_name', fn($p) => optional($p->zone)->name ?? '-')
                ->addColumn('turno_name', function ($p) {
                    if (!$p->schedule)
                        return '-';
                    $color = str_contains(strtolower($p->schedule->name), 'noch') ? '#64748B' : '#F59E0B';
                    return '<span class="badge px-2 py-1" style="background:' . $color . ';color:#fff;">'
                        . e($p->schedule->name) . '</span>';
                })
                ->addColumn('vehicle_name', function ($p) {
                    if (!$p->vehicle)
                        return '-';
                    return '<strong>' . e($p->vehicle->name) . '</strong>'
                        . '<br><small class="text-muted">' . e($p->vehicle->code) . '</small>';
                })
                ->addColumn('conductor_name', fn($p) => optional($p->conductor)->name ?? '-')
                ->addColumn('ayudantes_names', function ($p) {
                    $html = '';
                    foreach ($p->ayudantes as $a) {
                        $html .= '<div><small>' . e($a->name) . '</small></div>';
                    }
                    return $html ?: '<span class="text-muted">—</span>';
                })
                ->addColumn('grupo_name', fn($p) => optional($p->group)->name ?? '-')
                ->addColumn('actions', function ($p) {

                    // Estados que permiten todas las acciones
                    $editable = in_array($p->status, ['Programado', 'Reprogramado']);

                    $btnView = '<button class="btn btn-sm btn-info btn-ver mr-1" data-id="' . $p->id . '" title="Ver Detalle">'
                        . '<i class="fas fa-eye text-white"></i></button>';

                    $btnEdit = $editable
                        ? '<button class="btn btn-sm btn-warning btn-editar mr-1" data-id="' . $p->id . '" title="Editar">'
                        . '<i class="fas fa-pen text-dark"></i></button>'
                        : '';

                    $btnHistory = '<button class="btn btn-sm btn-secondary btn-historial mr-1" data-id="' . $p->id . '" title="Historial">'
                        . '<i class="fas fa-history text-white"></i></button>';

                    $btnFinish = $editable
                        ? '<button class="btn btn-sm btn-success btn-finalizar mr-1" data-id="' . $p->id . '" title="Finalizar">'
                        . '<i class="fas fa-flag-checkered text-white"></i></button>'
                        : '';

                    $btnDelete = $editable
                        ? '<form action="' . route('admin.programacion.destroy', $p->id) . '" method="POST" class="frmEliminar d-inline" style="margin:0;padding:0;">'
                        . method_field('DELETE')
                        . csrf_field()
                        . '<button class="btn btn-sm btn-danger" type="submit" title="Eliminar">'
                        . '<i class="fas fa-trash-alt text-white"></i></button></form>'
                        : '';

                    return $btnView . $btnEdit . $btnHistory . $btnFinish . $btnDelete;
                })
                ->rawColumns(['badge_status', 'turno_name', 'vehicle_name', 'ayudantes_names', 'actions'])
                ->order(fn($q) => $q->orderBy('fecha', 'desc'))
                ->make(true);
        }

        $zones = Zone::whereRaw('UPPER(status) = ?', ['ACTIVO'])->orderBy('name')->get();
        $schedules = Schedule::orderBy('name')->get();
        $groups = PersonalGroup::whereRaw('UPPER(status) = ?', ['ACTIVO'])->orderBy('name')->get();

        return view('admin.programaciones.index', compact('zones', 'schedules', 'groups'));
    }

    // ──────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────
    public function create()
    {
        $groups = PersonalGroup::whereRaw('UPPER(status) = ?', ['ACTIVO'])->orderBy('name')->get();
        return view('admin.programaciones.template.form', compact('groups'));
    }

    // ──────────────────────────────────────────────────────────
    // STORE — expande el rango × días en N filas (1 por fecha)
    // ──────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'personal_group_id' => 'required|exists:personal_groups,id',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'conductor_id' => 'required|exists:users,id',
                'ayudantes' => 'nullable|array',
                'ayudantes.*' => 'exists:users,id',
                'dias' => 'required|array|min:1',
                'dias.*' => 'in:Lun,Mar,Mié,Jue,Vie,Sáb,Dom',
                'observaciones' => 'nullable|string|max:500',
            ]);

            $ayudantes = array_values(array_filter(
                $data['ayudantes'] ?? [],
                fn($v) => $v !== null && $v !== ''
            ));

            $group = PersonalGroup::findOrFail($data['personal_group_id']);

            // ── Generar las fechas concretas del rango según los días marcados ──
            $fechas = $this->expandirFechas($data['fecha_inicio'], $data['fecha_fin'], $data['dias']);

            if (empty($fechas)) {
                return response()->json([
                    'message' => 'El rango de fechas seleccionado no contiene ningún día de los marcados.'
                ], 422);
            }

            $allIds = array_merge([$data['conductor_id']], $ayudantes);

            // ── Validar reglas de negocio que NO dependen de la fecha exacta ──
            $this->validarReglasBase($data['conductor_id'], $ayudantes);

            // ── Filtrar fechas: separar las que se pueden crear de las que ya existen ──
            $fechasACrear = [];
            $fechasSaltadas = [];
            $erroresBloqueantes = [];

            foreach ($fechas as $fecha) {
                $conflictos = $this->checkConflictosFecha($allIds, $fecha, null);

                if (!empty($conflictos['ocupado'])) {
                    // Ya existe registro de alguno de estos usuarios ese día → se salta
                    $fechasSaltadas[] = $fecha;
                    continue;
                }

                if (!empty($conflictos['sin_contrato']) || !empty($conflictos['vacaciones'])) {
                    // Esto bloquea TODA la creación porque es un problema estructural
                    // (la persona no puede trabajar ese día en absoluto)
                    foreach ($conflictos['sin_contrato'] as $nombre) {
                        $erroresBloqueantes[] = "{$nombre} no tiene contrato activo para el {$fecha}.";
                    }
                    foreach ($conflictos['vacaciones'] as $nombre) {
                        $erroresBloqueantes[] = "{$nombre} tiene vacaciones aprobadas el {$fecha}.";
                    }
                    continue;
                }

                $fechasACrear[] = $fecha;
            }

            if (!empty($erroresBloqueantes)) {
                return response()->json([
                    'message' => 'No se puede programar: ' . implode(' ', array_unique($erroresBloqueantes))
                ], 422);
            }

            if (empty($fechasACrear)) {
                return response()->json([
                    'message' => 'Todas las fechas del rango ya tienen una programación existente para este personal. No se creó ningún registro nuevo.'
                ], 422);
            }

            $batchId = (string) Str::uuid();
            $creadas = 0;

            DB::transaction(function () use ($data, $group, $ayudantes, $fechasACrear, $batchId, &$creadas) {
                foreach ($fechasACrear as $fecha) {
                    $prog = Programacion::create([
                        'batch_id' => $batchId,
                        'personal_group_id' => $group->id,
                        'zone_id' => $group->zone_id,
                        'schedule_id' => $group->schedule_id,
                        'vehicle_id' => $group->vehicle_id,
                        'conductor_id' => $data['conductor_id'],
                        'fecha' => $fecha,
                        'observaciones' => $data['observaciones'] ?? null,
                        'status' => 'Programado',
                    ]);

                    foreach ($ayudantes as $i => $uid) {
                        $prog->ayudantes()->attach($uid, ['order' => $i]);
                    }
                    $creadas++;
                }
            });

            $msg = "Se crearon {$creadas} programación(es), una por cada fecha seleccionada.";
            if (!empty($fechasSaltadas)) {
                $msg .= ' Se omitieron ' . count($fechasSaltadas) . ' fecha(s) por tener ya una programación existente para este personal: '
                    . implode(', ', array_map(fn($f) => Carbon::parse($f)->format('d/m/Y'), $fechasSaltadas)) . '.';
            }

            return response()->json(['message' => $msg], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => implode(' ', array_merge(...array_values($e->errors()))),
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // SHOW — detalle JSON de UNA fecha puntual
    // ──────────────────────────────────────────────────────────
    public function show($id)
    {
        $prog = Programacion::with(['group', 'zone', 'schedule', 'vehicle', 'conductor', 'ayudantes', 'cambios.user'])
            ->findOrFail($id);

        return response()->json([
            'id' => $prog->id,
            'fecha' => $prog->fecha->format('d/m/Y'),
            'status' => $prog->status,
            'observaciones' => $prog->observaciones,
            'group' => ['name' => optional($prog->group)->name],
            'zone' => ['name' => optional($prog->zone)->name],
            'schedule' => [
                'name' => optional($prog->schedule)->name,
                'time_start' => optional($prog->schedule)->time_start,
                'time_end' => optional($prog->schedule)->time_end,
            ],
            'vehicle' => [
                'name' => optional($prog->vehicle)->name,
                'code' => optional($prog->vehicle)->code,
            ],
            'conductor' => ['name' => optional($prog->conductor)->name],
            'ayudantes' => $prog->ayudantes->map(fn($a) => ['name' => $a->name]),
            'cambios' => $prog->cambios->sortByDesc('created_at')->map(fn($c) => [
                'fecha' => $c->created_at->format('d/m/Y H:i'),
                'usuario' => optional($c->user)->name ?? '—',
                'campo' => $c->campo,
                'valor_anterior' => $c->valor_anterior ?? '—',
                'valor_nuevo' => $c->valor_nuevo ?? '—',
                'motivo' => $c->motivo ?? '—',
            ]),
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // EDIT — formulario de edición de UNA fecha puntual (reprogramación)
    // ──────────────────────────────────────────────────────────
    public function edit($id)
    {
        $prog = Programacion::with([
            'group',
            'zone',
            'schedule',
            'vehicle',
            'conductor',
            'ayudantes' => fn($q) => $q->orderByPivot('order'),
            'cambios.user',
        ])->findOrFail($id);

        if (!in_array($prog->status, ['Programado', 'Reprogramado'])) {
            abort(422, 'Solo se pueden editar programaciones en estado Programado o Reprogramado.');
        }

        $schedules = Schedule::orderBy('name')->get();
        $vehicles = Vehicle::where('status', 'Activo')->orderBy('name')
            ->get(['id', 'name', 'code', 'occupant_capacity']);
        $cambios = $prog->cambios->sortByDesc('created_at');

        return view('admin.programaciones.template.form_edit', compact(
            'prog',
            'schedules',
            'vehicles',
            'cambios'
        ));
    }

    // ──────────────────────────────────────────────────────────
    // UPDATE — reprogramación de UNA fecha puntual
    // Permite cambiar turno, vehículo y/o personal de forma independiente.
    // Cada cambio genera su propio registro en el historial.
    // El status pasa a "Reprogramado".
    // ──────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        try {
            $prog = Programacion::with('conductor', 'ayudantes', 'schedule', 'vehicle')->findOrFail($id);

            if (!in_array($prog->status, ['Programado', 'Reprogramado'])) {
                return response()->json(['message' => 'Solo se pueden editar programaciones en estado Programado o Reprogramado.'], 422);
            }

            $request->validate([
                'cambiar_turno' => 'nullable|in:0,1',
                'schedule_id' => 'required_if:cambiar_turno,1|nullable|exists:schedules,id',
                'motivo_turno_predefinido' => 'required_if:cambiar_turno,1|nullable|string|max:255',
                'motivo_turno_detalle' => 'nullable|string|max:500',

                'cambiar_vehiculo' => 'nullable|in:0,1',
                'vehicle_id' => 'required_if:cambiar_vehiculo,1|nullable|exists:vehicles,id',
                'motivo_vehiculo_predefinido' => 'required_if:cambiar_vehiculo,1|nullable|string|max:255',
                'motivo_vehiculo_detalle' => 'nullable|string|max:500',

                'cambiar_personal' => 'nullable|in:0,1',
                'conductor_id' => 'required_if:cambiar_personal,1|nullable|exists:users,id',
                'ayudantes' => 'nullable|array',
                'ayudantes.*' => 'exists:users,id',
                'motivo_personal_predefinido' => 'required_if:cambiar_personal,1|nullable|string|max:255',
                'motivo_personal_detalle' => 'nullable|string|max:500',
            ]);

            $cambiarTurno = $request->input('cambiar_turno') == '1';
            $cambiarVehiculo = $request->input('cambiar_vehiculo') == '1';
            $cambiarPersonal = $request->input('cambiar_personal') == '1';

            if (!$cambiarTurno && !$cambiarVehiculo && !$cambiarPersonal) {
                return response()->json(['message' => 'Debe seleccionar al menos un tipo de cambio (Turno, Vehículo o Personal).'], 422);
            }

            $fechaStr = $prog->fecha->toDateString();
            $authUser = Auth::id();

            // ── Validar personal si se va a cambiar ───────────
            if ($cambiarPersonal) {
                $ayudantes = array_values(array_filter(
                    $request->input('ayudantes', []),
                    fn($v) => $v !== null && $v !== ''
                ));
                $conductorId = (int) $request->input('conductor_id');
                $ayudantes = array_map('intval', $ayudantes);

                $this->validarReglasBase($conductorId, $ayudantes);

                $allIds = array_merge([$conductorId], $ayudantes);
                $conflictos = $this->checkConflictosFecha($allIds, $fechaStr, $prog->id);

                $errores = [];
                foreach ($conflictos['sin_contrato'] as $nombre) {
                    $errores[] = "{$nombre} no tiene contrato activo para el " . Carbon::parse($fechaStr)->format('d/m/Y') . '.';
                }
                foreach ($conflictos['vacaciones'] as $nombre) {
                    $errores[] = "{$nombre} tiene vacaciones aprobadas el " . Carbon::parse($fechaStr)->format('d/m/Y') . '.';
                }
                foreach ($conflictos['ocupado'] as $nombre) {
                    $errores[] = "{$nombre} ya tiene otra programación el " . Carbon::parse($fechaStr)->format('d/m/Y') . '.';
                }
                if (!empty($errores)) {
                    return response()->json(['message' => implode(' ', $errores)], 422);
                }
            }

            DB::transaction(function () use ($prog, $request, $authUser, $fechaStr, $cambiarTurno, $cambiarVehiculo, $cambiarPersonal) {
                $actualizaciones = [];

                // ── CAMBIO DE TURNO ────────────────────────────
                if ($cambiarTurno) {
                    $nuevoScheduleId = $request->input('schedule_id');
                    $motivoTurno = $request->input('motivo_turno_predefinido')
                        . ($request->filled('motivo_turno_detalle')
                            ? ' — ' . $request->input('motivo_turno_detalle')
                            : '');

                    if ($prog->schedule_id != $nuevoScheduleId) {
                        ProgramacionCambio::create([
                            'programacion_id' => $prog->id,
                            'user_id' => $authUser,
                            'campo' => 'turno',
                            'valor_anterior' => optional($prog->schedule)->name
                                . ' (' . optional($prog->schedule)->time_start
                                . ' - ' . optional($prog->schedule)->time_end . ')',
                            'valor_nuevo' => optional(Schedule::find($nuevoScheduleId))->name
                                . ' (' . optional(Schedule::find($nuevoScheduleId))->time_start
                                . ' - ' . optional(Schedule::find($nuevoScheduleId))->time_end . ')',
                            'motivo' => $motivoTurno,
                        ]);
                        $actualizaciones['schedule_id'] = $nuevoScheduleId;
                    }
                }

                // ── CAMBIO DE VEHÍCULO ─────────────────────────
                if ($cambiarVehiculo) {
                    $nuevoVehicleId = $request->input('vehicle_id');
                    $motivoVehiculo = $request->input('motivo_vehiculo_predefinido')
                        . ($request->filled('motivo_vehiculo_detalle')
                            ? ' — ' . $request->input('motivo_vehiculo_detalle')
                            : '');

                    if ($prog->vehicle_id != $nuevoVehicleId) {
                        $vOld = $prog->vehicle;
                        $vNew = Vehicle::find($nuevoVehicleId);
                        ProgramacionCambio::create([
                            'programacion_id' => $prog->id,
                            'user_id' => $authUser,
                            'campo' => 'vehiculo',
                            'valor_anterior' => optional($vOld)->name . ' — ' . optional($vOld)->code,
                            'valor_nuevo' => optional($vNew)->name . ' — ' . optional($vNew)->code,
                            'motivo' => $motivoVehiculo,
                        ]);
                        $actualizaciones['vehicle_id'] = $nuevoVehicleId;
                    }
                }

                // ── CAMBIO DE PERSONAL ─────────────────────────
                if ($cambiarPersonal) {
                    $conductorId = (int) $request->input('conductor_id');
                    $ayudantes = array_values(array_filter(
                        $request->input('ayudantes', []),
                        fn($v) => $v !== null && $v !== ''
                    ));
                    $motivoPersonal = $request->input('motivo_personal_predefinido')
                        . ($request->filled('motivo_personal_detalle')
                            ? ' — ' . $request->input('motivo_personal_detalle')
                            : '');

                    if ($prog->conductor_id != $conductorId) {
                        ProgramacionCambio::create([
                            'programacion_id' => $prog->id,
                            'user_id' => $authUser,
                            'campo' => 'conductor',
                            'valor_anterior' => optional($prog->conductor)->name,
                            'valor_nuevo' => optional(User::find($conductorId))->name,
                            'motivo' => $motivoPersonal,
                        ]);
                        $actualizaciones['conductor_id'] = $conductorId;
                    }

                    $oldAyudantes = $prog->ayudantes->pluck('name')->sort()->values()->toArray();
                    $newAyudantes = User::whereIn('id', $ayudantes)->pluck('name')->sort()->values()->toArray();
                    if ($oldAyudantes !== $newAyudantes) {
                        ProgramacionCambio::create([
                            'programacion_id' => $prog->id,
                            'user_id' => $authUser,
                            'campo' => 'ayudantes',
                            'valor_anterior' => implode(', ', $oldAyudantes),
                            'valor_nuevo' => implode(', ', $newAyudantes),
                            'motivo' => $motivoPersonal,
                        ]);
                    }

                    $prog->ayudantes()->detach();
                    foreach ($ayudantes as $i => $uid) {
                        $prog->ayudantes()->attach($uid, ['order' => $i]);
                    }
                }

                // ── Aplicar cambios + cambiar status ──────────
                $actualizaciones['status'] = 'Reprogramado';
                $prog->update($actualizaciones);

            });

            return response()->json(['message' => 'Programación reprogramada correctamente.'], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => implode(' ', array_merge(...array_values($e->errors()))),
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // DESTROY — elimina UNA fila/fecha puntual
    // ──────────────────────────────────────────────────────────
    public function destroy($id)
    {
        try {
            $prog = Programacion::findOrFail($id);
            if ($prog->status !== 'Programado') {
                return response()->json(['message' => 'Solo se pueden eliminar programaciones en estado Programado.'], 422);
            }
            $prog->delete();
            return response()->json(['message' => 'Programación eliminada correctamente.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // FINALIZAR — UNA fila/fecha puntual
    // ──────────────────────────────────────────────────────────
    public function finalize($id)
    {
        try {
            $prog = Programacion::findOrFail($id);
            if ($prog->status !== 'Programado') {
                return response()->json(['message' => 'La programación ya fue finalizada o cancelada.'], 422);
            }
            $prog->update(['status' => 'Finalizado']);

            ProgramacionCambio::create([
                'programacion_id' => $prog->id,
                'user_id' => Auth::id(),
                'campo' => 'status',
                'valor_anterior' => 'Programado',
                'valor_nuevo' => 'Finalizado',
                'motivo' => 'Finalización manual',
            ]);

            return response()->json(['message' => 'Programación finalizada correctamente.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // HISTORIAL
    // ──────────────────────────────────────────────────────────
    public function historial($id)
    {
        $prog = Programacion::findOrFail($id);
        $cambios = ProgramacionCambio::with('user')
            ->where('programacion_id', $id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'programacion' => [
                'id' => $prog->id,
                'fecha' => $prog->fecha->format('d/m/Y'),
            ],
            'cambios' => $cambios->map(fn($c) => [
                'fecha' => $c->created_at->format('d/m/Y H:i'),
                'usuario' => optional($c->user)->name ?? '—',
                'campo' => $c->campo,
                'valor_anterior' => $c->valor_anterior,
                'valor_nuevo' => $c->valor_nuevo,
                'motivo' => $c->motivo,
            ]),
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // VALIDATE AVAILABILITY — simula la expansión y valida cada fecha
    // ──────────────────────────────────────────────────────────
    public function validateAvailability(Request $request)
    {
        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'conductor_id' => 'required|exists:users,id',
                'ayudantes' => 'nullable|array',
                'ayudantes.*' => 'exists:users,id',
                'dias' => 'required|array|min:1',
                'dias.*' => 'in:Lun,Mar,Mié,Jue,Vie,Sáb,Dom',
                'programacion_id' => 'nullable|exists:programaciones,id', // para edición puntual (no aplica expansión)
            ]);

            $ayudantes = array_values(array_filter(
                $request->ayudantes ?? [],
                fn($v) => $v !== null && $v !== ''
            ));

            $allIds = array_merge([$request->conductor_id], $ayudantes);
            $excludeProgId = $request->programacion_id;

            $fechas = $this->expandirFechas($request->fecha_inicio, $request->fecha_fin, $request->dias);

            if (empty($fechas)) {
                return response()->json([
                    'status' => 'error',
                    'errors' => ['El rango de fechas seleccionado no contiene ningún día de los marcados.'],
                    'suggestions' => [],
                ]);
            }

            $errors = [];
            $suggestions = [];
            $fechasConProblema = [];
            $fechasOcupadas = [];

            foreach ($fechas as $fecha) {
                $conflictos = $this->checkConflictosFecha($allIds, $fecha, $excludeProgId);

                foreach ($conflictos['sin_contrato'] as $nombre) {
                    $errors[] = "{$nombre}: Sin contrato activo el " . Carbon::parse($fecha)->format('d/m/Y') . '.';
                    $fechasConProblema[] = $fecha;
                }
                foreach ($conflictos['vacaciones'] as $nombre) {
                    $errors[] = "{$nombre}: Vacaciones aprobadas el " . Carbon::parse($fecha)->format('d/m/Y') . '.';
                    $fechasConProblema[] = $fecha;
                }
                foreach ($conflictos['ocupado'] as $nombre) {
                    $fechasOcupadas[] = $fecha;
                }
            }

            // Las fechas ocupadas no son "error" sino que se informan como aviso —
            // el store las salta automáticamente y crea el resto.
            if (!empty($fechasOcupadas)) {
                $fechasUnicas = array_unique($fechasOcupadas);
                $suggestions[] = count($fechasUnicas) . ' fecha(s) ya tienen programación para este personal y serán omitidas automáticamente al guardar: '
                    . implode(', ', array_map(fn($f) => Carbon::parse($f)->format('d/m/Y'), $fechasUnicas));
            }

            if (!empty($errors)) {
                return response()->json([
                    'status' => 'error',
                    'errors' => array_values(array_unique($errors)),
                    'suggestions' => $suggestions,
                ]);
            }

            $totalACrear = count($fechas) - count(array_unique($fechasOcupadas));

            return response()->json([
                'status' => 'success',
                'message' => "Disponibilidad correcta. Se crearán {$totalACrear} programación(es).",
                'suggestions' => $suggestions,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => implode(' ', array_merge(...array_values($e->errors()))),
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // SEARCH USERS para reemplazo en el form
    // ──────────────────────────────────────────────────────────
    public function searchUsers(Request $request)
    {
        $q = $request->get('q', '');
        $rol = $request->get('rol', 'ayudante');
        $excludeIds = $request->get('exclude', []);

        $usertypeNames = $rol === 'conductor'
            ? ['Conductor', 'conductor']
            : ['Ayudante', 'ayudante'];

        $users = User::query()
            ->whereHas('usertype', fn($q2) => $q2->whereIn('name', $usertypeNames))
            ->whereHas('contracts', fn($query) => $query->where('active', true))
            ->where(fn($query) => $query
                ->where('name', 'LIKE', "%{$q}%")
                ->orWhere('dni', 'LIKE', "%{$q}%"))
            ->when(!empty($excludeIds), fn($query) => $query->whereNotIn('id', $excludeIds))
            ->limit(10)
            ->get(['id', 'name', 'dni']);

        return response()->json($users);
    }

    // ──────────────────────────────────────────────────────────
    // CREATE MASIVO
    // ──────────────────────────────────────────────────────────
    public function createMasivo()
    {
        $groups = PersonalGroup::with(['zone', 'schedule', 'vehicle', 'members.usertype'])
            ->whereRaw('UPPER(status) = ?', ['ACTIVO'])
            ->orderBy('name')
            ->get();

        $schedules = Schedule::orderBy('name')->get();

        $conductores = User::whereHas('usertype', fn($q) => $q->whereIn('name', ['Conductor', 'conductor']))
            ->whereHas('contracts', fn($q) => $q->where('active', true))
            ->orderBy('name')->get(['id', 'name']);

        $ayudantesAll = User::whereHas('usertype', fn($q) => $q->whereIn('name', ['Ayudante', 'ayudante']))
            ->whereHas('contracts', fn($q) => $q->where('active', true))
            ->orderBy('name')->get(['id', 'name']);

        return view('admin.programaciones.template.form_masivo', compact(
            'groups',
            'schedules',
            'conductores',
            'ayudantesAll'
        ));
    }

    // ──────────────────────────────────────────────────────────
    // FERIADOS
    // ──────────────────────────────────────────────────────────
    public function getFeriados(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $feriados = Feriado::where('active', true)
            ->whereBetween('date', [$request->fecha_inicio, $request->fecha_fin])
            ->orderBy('date')
            ->get();

        return response()->json($feriados->map(fn($f) => [
            'id' => $f->id,
            'date' => $f->date,
            'date_fmt' => Carbon::parse($f->date)->format('d/m/Y'),
            'description' => $f->description,
        ]));
    }

    // ──────────────────────────────────────────────────────────
    // VALIDATE MASIVO — por grupo, simula expansión y valida
    // ──────────────────────────────────────────────────────────
    public function validateMasivo(Request $request)
    {
        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'grupos' => 'required|array|min:1',
                'grupos.*.group_id' => 'required|exists:personal_groups,id',
                'feriados_excluir' => 'nullable|array',
            ]);

            $fechaInicio = $request->fecha_inicio;
            $fechaFin = $request->fecha_fin;
            $feriadosExcluir = $request->feriados_excluir ?? [];
            $resultados = [];

            foreach ($request->grupos as $grupoData) {
                $gid = $grupoData['group_id'];
                $conductorId = $grupoData['conductor_id'] ?? null;
                $ayudantes = array_values(array_filter(
                    $grupoData['ayudantes'] ?? [],
                    fn($v) => $v !== null && $v !== ''
                ));
                $diasGrupo = $grupoData['dias'] ?? [];

                $group = PersonalGroup::with('zone', 'schedule')->find($gid);
                $errores = [];
                $advertencias = [];
                $avisosPersona = [];

                $fechas = $this->expandirFechas($fechaInicio, $fechaFin, $diasGrupo, $feriadosExcluir);

                if (!$conductorId) {
                    $errores[] = 'No se ha asignado conductor al grupo.';
                } else {
                    $conductor = User::find($conductorId);
                    if ($conductor) {
                        $r = $this->validarPersonaMasivo($conductor, 'conductor', $fechas, $gid);
                        if ($r['error']) {
                            $errores[] = 'Conductor ' . $conductor->name . ': ' . $r['error'];
                            $avisosPersona[] = ['rol' => 'conductor', 'tipo' => 'error', 'mensaje' => $r['error']];
                        }
                        if ($r['advertencia']) {
                            $advertencias[] = 'Conductor ' . $conductor->name . ': ' . $r['advertencia'];
                            $avisosPersona[] = ['rol' => 'conductor', 'tipo' => 'advertencia', 'mensaje' => $r['advertencia']];
                        }
                    }
                }

                foreach ($ayudantes as $i => $aid) {
                    $ayudante = User::find($aid);
                    if (!$ayudante)
                        continue;

                    $r = $this->validarPersonaMasivo($ayudante, 'ayudante' . $i, $fechas, $gid);
                    if ($r['error']) {
                        $errores[] = 'Ayudante ' . ($i + 1) . ' ' . $ayudante->name . ': ' . $r['error'];
                        $avisosPersona[] = ['rol' => 'ayudante' . $i, 'tipo' => 'error', 'mensaje' => $r['error']];
                    }
                    if ($r['advertencia']) {
                        $advertencias[] = 'Ayudante ' . ($i + 1) . ' ' . $ayudante->name . ': ' . $r['advertencia'];
                        $avisosPersona[] = ['rol' => 'ayudante' . $i, 'tipo' => 'advertencia', 'mensaje' => $r['advertencia']];
                    }
                }

                // Cuántas fechas de las generadas ya tienen registro (se omitirán, no es error)
                $allIds = array_merge($conductorId ? [$conductorId] : [], $ayudantes);
                $fechasOcupadas = 0;
                if (!empty($allIds)) {
                    foreach ($fechas as $fecha) {
                        $conflictos = $this->checkConflictosFecha($allIds, $fecha, null);
                        if (!empty($conflictos['ocupado'])) {
                            $fechasOcupadas++;
                        }
                    }
                }
                if ($fechasOcupadas > 0) {
                    $advertencias[] = "{$fechasOcupadas} fecha(s) ya tienen programación para este personal y serán omitidas.";
                }

                $resultados[] = [
                    'group_id' => $gid,
                    'group_name' => optional($group)->name ?? "Grupo #{$gid}",
                    'dias_grupo' => $diasGrupo,
                    'total_fechas' => count($fechas),
                    'fechas_a_crear' => count($fechas) - $fechasOcupadas,
                    'fechas_omitidas' => $fechasOcupadas,
                    'errores' => $errores,
                    'advertencias' => $advertencias,
                    'avisos_persona' => $avisosPersona,
                ];
            }

            $hayErrores = collect($resultados)->contains(fn($r) => count($r['errores']) > 0);

            return response()->json([
                'status' => $hayErrores ? 'error' : 'success',
                'grupos' => $resultados,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => implode(' ', array_merge(...array_values($e->errors()))),
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // STORE MASIVO — expande por grupo, salta fechas ocupadas
    // ──────────────────────────────────────────────────────────
    public function storeMasivo(Request $request)
    {
        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'grupos' => 'required|array|min:1',
                'grupos.*.conductor_id' => 'required|exists:users,id',
                'feriados_excluir' => 'nullable|array',
            ]);

            $fechaInicio = $request->fecha_inicio;
            $fechaFin = $request->fecha_fin;
            $feriadosExcluir = $request->feriados_excluir ?? [];

            $totalCreadas = 0;
            $totalOmitidas = 0;
            $gruposConDatos = 0;

            DB::transaction(function () use ($request, $fechaInicio, $fechaFin, $feriadosExcluir, &$totalCreadas, &$totalOmitidas, &$gruposConDatos) {
                foreach ($request->grupos as $gid => $grupoData) {
                    $group = PersonalGroup::find($gid);
                    if (!$group)
                        continue;

                    $conductorId = $grupoData['conductor_id'];
                    $ayudantes = array_values(array_filter(
                        $grupoData['ayudantes'] ?? [],
                        fn($v) => $v !== null && $v !== ''
                    ));
                    $diasGrupo = $grupoData['dias'] ?? $group->days ?? [];

                    $fechas = $this->expandirFechas($fechaInicio, $fechaFin, $diasGrupo, $feriadosExcluir);
                    if (empty($fechas))
                        continue;

                    $allIds = array_merge([$conductorId], $ayudantes);
                    $batchId = (string) Str::uuid();
                    $creadasGrupo = 0;

                    foreach ($fechas as $fecha) {
                        $conflictos = $this->checkConflictosFecha($allIds, $fecha, null);

                        // Si hay conflicto de cualquier tipo (ocupado, sin contrato, vacaciones)
                        // para ESA fecha puntual, se salta esa fecha (no todo el grupo)
                        if (!empty($conflictos['ocupado']) || !empty($conflictos['sin_contrato']) || !empty($conflictos['vacaciones'])) {
                            $totalOmitidas++;
                            continue;
                        }

                        $prog = Programacion::create([
                            'batch_id' => $batchId,
                            'personal_group_id' => $gid,
                            'zone_id' => $group->zone_id,
                            'schedule_id' => $group->schedule_id,
                            'vehicle_id' => $group->vehicle_id,
                            'conductor_id' => $conductorId,
                            'fecha' => $fecha,
                            'observaciones' => 'Programación masiva',
                            'status' => 'Programado',
                        ]);

                        foreach ($ayudantes as $i => $uid) {
                            $prog->ayudantes()->attach($uid, ['order' => $i]);
                        }

                        $creadasGrupo++;
                        $totalCreadas++;
                    }

                    if ($creadasGrupo > 0) {
                        $gruposConDatos++;
                    }
                }
            });

            $msg = "Programación masiva completada. {$totalCreadas} fecha(s) programada(s) en {$gruposConDatos} grupo(s).";
            if ($totalOmitidas > 0) {
                $msg .= " {$totalOmitidas} fecha(s) omitida(s) por conflicto (programación existente, sin contrato o vacaciones).";
            }

            return response()->json(['message' => $msg], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => implode(' ', array_merge(...array_values($e->errors()))),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ──────────────────────────────────────────────────────────

    /**
     * Expande un rango fecha_inicio→fecha_fin en una lista de fechas concretas (Y-m-d)
     * que caen en los días de la semana indicados, excluyendo feriados si se pasan.
     */
    private function expandirFechas(string $fechaInicio, string $fechaFin, array $dias, array $feriadosExcluir = []): array
    {
        $dayNumbers = array_map(fn($d) => self::DAY_MAP[$d] ?? -1, $dias);
        $current = Carbon::parse($fechaInicio);
        $end = Carbon::parse($fechaFin);
        $fechas = [];

        while ($current->lte($end)) {
            $dateStr = $current->toDateString();
            if (in_array($current->dayOfWeek, $dayNumbers) && !in_array($dateStr, $feriadosExcluir)) {
                $fechas[] = $dateStr;
            }
            $current->addDay();
        }

        return $fechas;
    }

    /**
     * Valida reglas que no dependen de la fecha exacta: conductor != ayudante,
     * ayudantes sin duplicados, tipos de usuario correctos.
     * Lanza \Exception si algo falla.
     */
    private function validarReglasBase(int $conductorId, array $ayudantes): void
    {
        if (in_array($conductorId, $ayudantes)) {
            throw new \Exception('El conductor no puede ser también ayudante.');
        }

        if (count($ayudantes) !== count(array_unique($ayudantes))) {
            throw new \Exception('No puedes asignar al mismo ayudante más de una vez.');
        }

        $conductor = User::with('usertype')->find($conductorId);
        if (!$conductor || strtolower(optional($conductor->usertype)->name ?? '') !== 'conductor') {
            throw new \Exception('El usuario seleccionado como conductor no tiene el tipo "Conductor".');
        }

        foreach ($ayudantes as $aid) {
            $ay = User::with('usertype')->find($aid);
            if (!$ay || strtolower(optional($ay->usertype)->name ?? '') !== 'ayudante') {
                throw new \Exception('El usuario "' . optional($ay)->name . '" no tiene el tipo "Ayudante".');
            }
        }
    }

    /**
     * Revisa, para una fecha puntual y un conjunto de usuarios, tres tipos de conflicto:
     * - sin_contrato: usuarios sin contrato activo que cubra esa fecha
     * - vacaciones: usuarios con vacación aprobada esa fecha
     * - ocupado: usuarios que ya tienen una fila de Programacion (no cancelada) ese día,
     *            ya sea como conductor o ayudante, excluyendo $excludeProgId
     *
     * Retorna ['sin_contrato' => [nombres], 'vacaciones' => [nombres], 'ocupado' => [nombres]]
     */
    private function checkConflictosFecha(array $userIds, string $fecha, ?int $excludeProgId = null): array
    {
        $sinContrato = [];
        $vacaciones = [];
        $ocupado = [];

        $users = User::whereIn('id', $userIds)->get(['id', 'name']);

        foreach ($users as $user) {
            // ── Contrato activo que cubra esa fecha ──
            $hasContract = Contract::where('user_id', $user->id)
                ->where('active', true)
                ->where('start_date', '<=', $fecha)
                ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $fecha))
                ->exists();

            if (!$hasContract) {
                $sinContrato[] = $user->name;
                continue; // si no tiene contrato, no seguimos evaluando para este usuario
            }

            // ── Vacaciones aprobadas esa fecha ──
            $onVacation = Vacation::where('user_id', $user->id)
                ->whereRaw('UPPER(status) = ?', ['APROBADA'])
                ->where('start_date', '<=', $fecha)
                ->where('end_date', '>=', $fecha)
                ->exists();

            if ($onVacation) {
                $vacaciones[] = $user->name;
                continue;
            }

            // ── Ya tiene una programación (no cancelada) esa fecha, como conductor o ayudante ──
            $yaOcupado = Programacion::where('fecha', $fecha)
                ->where('status', '!=', 'Cancelado')
                ->when($excludeProgId, fn($q) => $q->where('id', '!=', $excludeProgId))
                ->where(function ($q) use ($user) {
                    $q->where('conductor_id', $user->id)
                        ->orWhereHas('ayudantes', fn($q2) => $q2->where('user_id', $user->id));
                })
                ->exists();

            if ($yaOcupado) {
                $ocupado[] = $user->name;
            }
        }

        return [
            'sin_contrato' => $sinContrato,
            'vacaciones' => $vacaciones,
            'ocupado' => $ocupado,
        ];
    }

    /**
     * Valida disponibilidad de una persona para programación masiva,
     * a lo largo de TODAS las fechas expandidas del grupo.
     * Retorna ['error' => ?string, 'advertencia' => ?string]
     */
    private function validarPersonaMasivo(User $user, string $rol, array $fechas, int $groupId): array
    {
        if (empty($fechas)) {
            return ['error' => null, 'advertencia' => null];
        }

        $sinContratoFechas = [];
        $vacacionFechas = [];

        foreach ($fechas as $fecha) {
            $hasContract = Contract::where('user_id', $user->id)
                ->where('active', true)
                ->where('start_date', '<=', $fecha)
                ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $fecha))
                ->exists();

            if (!$hasContract) {
                $sinContratoFechas[] = $fecha;
                continue;
            }

            $onVacation = Vacation::where('user_id', $user->id)
                ->whereRaw('UPPER(status) = ?', ['APROBADA'])
                ->where('start_date', '<=', $fecha)
                ->where('end_date', '>=', $fecha)
                ->exists();

            if ($onVacation) {
                $vacacionFechas[] = $fecha;
            }
        }

        $error = null;
        if (!empty($sinContratoFechas)) {
            $preview = array_slice($sinContratoFechas, 0, 3);
            $extra = count($sinContratoFechas) - count($preview);
            $error = 'Sin contrato activo en: ' . implode(', ', array_map(fn($f) => Carbon::parse($f)->format('d/m/Y'), $preview))
                . ($extra > 0 ? " y {$extra} fecha(s) más" : '') . '.';
        } elseif (!empty($vacacionFechas)) {
            $preview = array_slice($vacacionFechas, 0, 3);
            $extra = count($vacacionFechas) - count($preview);
            $error = 'Vacaciones aprobadas en: ' . implode(', ', array_map(fn($f) => Carbon::parse($f)->format('d/m/Y'), $preview))
                . ($extra > 0 ? " y {$extra} fecha(s) más" : '') . '.';
        }

        return ['error' => $error, 'advertencia' => null];
    }
}