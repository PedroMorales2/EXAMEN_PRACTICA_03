<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\CambioMasivo;
use App\Models\Programacion;
use App\Models\ProgramacionCambio;
use App\Models\Schedule;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Zone;
use App\Models\Cambios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CambioMasivoController extends Controller
{
    // ──────────────────────────────────────────────────────────
    // INDEX — DataTable
    // ──────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ProgramacionCambio::with(['programacion', 'user', 'cambioMasivo'])
                ->select('programacion_cambios.*');

            if ($request->filled('start_date')) {
                $query->whereHas('programacion', fn($q) => $q->where('fecha', '>=', $request->start_date));
            }
            if ($request->filled('end_date')) {
                $query->whereHas('programacion', fn($q) => $q->where('fecha', '<=', $request->end_date));
            }
            if ($request->filled('tipo_cambio')) {
                $query->where('campo', $request->tipo_cambio);
            }

            return DataTables::of($query->orderByDesc('created_at'))
                ->addColumn('tipo_badge', function ($c) {
                    $color = CambioMasivo::colorTipo($c->campo);
                    $label = CambioMasivo::labelTipo($c->campo);
                    return '<span style="background:' . $color . ';color:#fff;padding:3px 10px;'
                        . 'border-radius:20px;font-size:.75rem;font-weight:700;">' . $label . '</span>';
                })
                ->addColumn('fecha_cambio', fn($c) =>
                    '<div style="font-size:.82rem;">'
                    . $c->created_at->format('d/m/Y')
                    . '<br><small class="text-muted">' . $c->created_at->format('H:i') . '</small></div>')
                ->addColumn('periodo', fn($c) =>
                    '<span style="font-size:.8rem;">'
                    . (optional(optional($c->programacion)->fecha)?->format('d/m/Y') ?? '—')
                    . '</span>')
                ->addColumn('antes_col', fn($c) =>
                    '<span style="color:#dc3545;font-size:.82rem;">' . e($c->valor_anterior ?? '—') . '</span>')
                ->addColumn('despues_col', fn($c) =>
                    '<span style="color:#198754;font-size:.82rem;">' . e($c->valor_nuevo ?? '—') . '</span>')
                ->addColumn('ejecutado_col', function ($c) {
                    $u = $c->user;
                    if (!$u)
                        return '—';
                    return '<div style="font-size:.8rem;">'
                        . '<i class="fas fa-user-circle mr-1 text-secondary"></i>'
                        . '<strong>' . e($u->name) . '</strong>'
                        . '<br><small class="text-muted">' . e($u->email ?? '') . '</small></div>';
                })
                ->addColumn('actions', function ($c) {
                    $btnVer = '<button class="btn btn-sm btn-info btn-ver-cambio mr-1" data-id="' . $c->id . '">'
                        . '<i class="fas fa-eye text-white"></i></button>';
                    // Solo mostrar eliminar/revertir si no está ya revertido
                    $btnDel = !$c->revertido
                        ? '<button class="btn btn-sm btn-danger btn-revertir-fila" data-id="' . $c->id . '">'
                        . '<i class="fas fa-undo text-white"></i></button>'
                        : '<span class="badge badge-secondary px-2 py-1" style="font-size:.7rem;">Revertido</span>';
                    return $btnVer . ' ' . $btnDel;
                })
                ->rawColumns(['tipo_badge', 'fecha_cambio', 'periodo', 'antes_col', 'despues_col', 'ejecutado_col', 'actions'])
                ->make(true);
        }

        $zones = Zone::whereRaw('UPPER(status) = ?', ['ACTIVO'])->orderBy('name')->get();
        $motivos = Cambios::orderBy('name')->get();

        return view('admin.cambios_masivos.index', compact('zones', 'motivos'));
    }

    // ──────────────────────────────────────────────────────────
    // SHOW — detalle de un cambio masivo + sus filas afectadas
    // ──────────────────────────────────────────────────────────
    public function show($id)
    {
        $cambio = ProgramacionCambio::with(['programacion', 'user', 'cambioMasivo'])
            ->findOrFail($id);

        $prog = $cambio->programacion;

        return response()->json([
            'id' => $cambio->id,
            'tipo_cambio' => $cambio->campo,
            'tipo_label' => CambioMasivo::labelTipo($cambio->campo),
            'tipo_color' => CambioMasivo::colorTipo($cambio->campo),
            'fecha_cambio' => $cambio->created_at->format('d/m/Y H:i'),
            'fecha_prog' => optional(optional($prog)->fecha)?->format('d/m/Y') ?? '—',
            'prog_id' => $cambio->programacion_id,
            'valor_anterior' => $cambio->valor_anterior ?? '—',
            'valor_nuevo' => $cambio->valor_nuevo ?? '—',
            'motivo' => $cambio->motivo ?? '—',
            'ejecutado_por' => optional($cambio->user)->name ?? '—',
            'es_masivo' => !is_null($cambio->cambio_masivo_id),
            'lote_id' => $cambio->cambio_masivo_id,
            'revertido' => (bool) $cambio->revertido,
        ]);
    }


    public function createForm()
    {
        $zones = Zone::whereRaw('UPPER(status) = ?', ['ACTIVO'])->orderBy('name')->get();
        $motivos = Cambios::orderBy('name')->get();
        $schedules = Schedule::orderBy('name')->get();
        $vehicles = Vehicle::where('status', 'Activo')->orderBy('name')->get(['id', 'name', 'code']);

        return view('admin.cambios_masivos.template.form', compact('zones', 'motivos', 'schedules', 'vehicles'));
    }

    // ──────────────────────────────────────────────────────────
    // STORE — ejecutar cambio masivo
    // ──────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        try {
            $request->validate([
                'tipo_cambio' => 'required|in:turno,conductor,ocupante,vehiculo',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'zone_id' => 'nullable|exists:zones,id',
                'valor_anterior_id' => 'required|integer',
                'valor_nuevo_id' => 'required|integer|different:valor_anterior_id',
                'cambio_id' => 'required|exists:cambios,id',
                'descripcion' => 'nullable|string|max:500',
            ]);

            $tipo = $request->tipo_cambio;
            $fi = $request->fecha_inicio;
            $ff = $request->fecha_fin;
            $zoneId = $request->zone_id;
            $anteriorId = (int) $request->valor_anterior_id;
            $nuevoId = (int) $request->valor_nuevo_id;
            $authId = Auth::id();

            // Construir motivo legible para el historial
            $motivo = Cambios::find($request->cambio_id)?->name ?? '—';
            if ($request->filled('descripcion')) {
                $motivo .= ' — ' . $request->descripcion;
            }

            // Buscar programaciones en el rango (solo Programado o Reprogramado)
            $query = Programacion::whereBetween('fecha', [$fi, $ff])
                ->whereIn('status', ['Programado', 'Reprogramado']);

            if ($zoneId) {
                $query->where('zone_id', $zoneId);
            }

            // Filtrar solo las que tienen el valor anterior según tipo
            $programaciones = $this->filtrarPorValorAnterior($query, $tipo, $anteriorId)->get();

            if ($programaciones->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron programaciones que coincidan con los criterios seleccionados en el rango de fechas.',
                ], 422);
            }

            $afectadas = 0;

            DB::transaction(function () use ($programaciones, $tipo, $anteriorId, $nuevoId, $authId, $motivo, $request, $fi, $ff, $zoneId, &$afectadas) {
                // Crear el lote
                $lote = CambioMasivo::create([
                    'tipo_cambio' => $tipo,
                    'fecha_inicio' => $fi,
                    'fecha_fin' => $ff,
                    'zone_id' => $zoneId,
                    'valor_anterior_id' => $anteriorId,
                    'valor_nuevo_id' => $nuevoId,
                    'cambio_id' => $request->cambio_id,
                    'descripcion' => $request->descripcion,
                    'user_id' => $authId,
                    'afectadas' => 0,
                ]);

                foreach ($programaciones as $prog) {
                    $this->aplicarCambio($prog, $tipo, $anteriorId, $nuevoId, $authId, $motivo, $lote->id);
                    $afectadas++;
                }

                $lote->update(['afectadas' => $afectadas]);
            });

            return response()->json([
                'message' => "Cambio masivo aplicado correctamente. {$afectadas} programación(es) afectada(s).",
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => implode(' ', array_merge(...array_values($e->errors()))),
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // REVERT — revertir UNA fila (un día) de un cambio masivo
    // ──────────────────────────────────────────────────────────
    // ──────────────────────────────────────────────────────────
// REVERT — revertir UNA fila (un día) de un cambio masivo
// ──────────────────────────────────────────────────────────
    public function revertFila(Request $request, $filaId)
    {
        try {
            $fila = ProgramacionCambio::with('programacion')->findOrFail($filaId);

            if ($fila->revertido) {
                return response()->json(['message' => 'Este cambio ya fue revertido anteriormente.'], 422);
            }

            $prog = $fila->programacion;
            if (!$prog) {
                return response()->json(['message' => 'La programación asociada ya no existe.'], 422);
            }

            if (!in_array($prog->status, ['Programado', 'Reprogramado'])) {
                return response()->json(['message' => 'No se puede revertir: la programación está Finalizada o Cancelada.'], 422);
            }

            // Obtener el lote (puede ser null si el cambio fue individual, no masivo)
            $lote = CambioMasivo::find($fila->cambio_masivo_id);
            $tipoCambio = $lote ? $lote->tipo_cambio : $fila->campo;

            // Resolver IDs desde el lote (masivo) o buscarlos por label (individual)
            $anteriorId = $lote ? $lote->valor_anterior_id : $this->resolverIdDesdeLabel($tipoCambio, $fila->valor_anterior);
            $nuevoId = $lote ? $lote->valor_nuevo_id : $this->resolverIdDesdeLabel($tipoCambio, $fila->valor_nuevo);

            if (!$anteriorId || !$nuevoId) {
                return response()->json(['message' => 'No se pueden resolver los valores para revertir este cambio.'], 422);
            }

            DB::transaction(function () use ($fila, $prog, $tipoCambio, $anteriorId, $nuevoId, $lote) {
                $authId = Auth::id();

                // Revertir según tipo
                match ($tipoCambio) {
                    'turno' => $prog->update([
                        'schedule_id' => $anteriorId,
                        'status' => 'Reprogramado',
                    ]),
                    'vehiculo' => $prog->update([
                        'vehicle_id' => $anteriorId,
                        'status' => 'Reprogramado',
                    ]),
                    'conductor' => $prog->update([
                        'conductor_id' => $anteriorId,
                        'status' => 'Reprogramado',
                    ]),
                    'ocupante' => (function () use ($prog, $anteriorId, $nuevoId) {
                            $prog->ayudantes()->detach($nuevoId);
                            if ($prog->conductor_id == $nuevoId) {
                                $prog->update(['conductor_id' => $anteriorId, 'status' => 'Reprogramado']);
                            } else {
                                $pivot = DB::table('programacion_ayudante')
                                ->where('programacion_id', $prog->id)
                                ->where('user_id', $nuevoId)
                                ->first();
                                $order = $pivot ? $pivot->order : 99;
                                $prog->ayudantes()->detach($nuevoId);
                                $prog->ayudantes()->attach($anteriorId, ['order' => $order]);
                                $prog->update(['status' => 'Reprogramado']);
                            }
                        })(),
                    default => null,
                };

                // Registrar reversión en historial
                ProgramacionCambio::create([
                    'programacion_id' => $prog->id,
                    'user_id' => $authId,
                    'campo' => $tipoCambio,
                    'valor_anterior' => $fila->valor_nuevo,   // lo que estaba (post-cambio)
                    'valor_nuevo' => $fila->valor_anterior,   // a lo que volvemos
                    'motivo' => 'Reversión de cambio masivo #' . ($lote ? $lote->id : 'individual'),
                    'cambio_masivo_id' => null,
                ]);

                // Marcar la fila como revertida
                $fila->update(['revertido' => true]);
            });

            return response()->json(['message' => 'Cambio revertido correctamente para esta programación.'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
// Resuelve el ID de un modelo a partir del label de texto
// guardado en valor_anterior / valor_nuevo (cambios individuales)
// ──────────────────────────────────────────────────────────
    private function resolverIdDesdeLabel(string $tipo, string $label): ?int
    {
        return match ($tipo) {
            'turno' => optional(Schedule::where('name', 'LIKE', '%' . explode(' (', $label)[0] . '%')->first())->id,
            'vehiculo' => optional(Vehicle::where('code', explode(' —', $label)[0])->first())->id,
            'conductor',
            'ocupante' => optional(User::where('name', $label)->first())->id,
            default => null,
        };
    }

    // ──────────────────────────────────────────────────────────
    // SEARCH USERS — para el campo de búsqueda en vivo
    // ──────────────────────────────────────────────────────────
    public function searchUsers(Request $request)
    {
        $q = $request->get('q', '');
        $rol = $request->get('rol', 'conductor'); // conductor | ayudante
        $excl = $request->get('exclude', []);

        $usertypeNames = $rol === 'conductor'
            ? ['Conductor', 'conductor']
            : ['Ayudante', 'ayudante'];

        $users = User::whereHas('usertype', fn($q2) => $q2->whereIn('name', $usertypeNames))
            ->whereHas('contracts', fn($q2) => $q2->where('active', true))
            ->where(fn($q2) => $q2->where('name', 'LIKE', "%{$q}%")->orWhere('dni', 'LIKE', "%{$q}%"))
            ->when(!empty($excl), fn($q2) => $q2->whereNotIn('id', $excl))
            ->limit(10)
            ->get(['id', 'name', 'dni']);

        return response()->json($users);
    }

    // ──────────────────────────────────────────────────────────
    // GET PERSONAS EN RANGO — para poblar el select "a quién reemplazar"
    // devuelve las personas únicas que aparecen en programaciones del rango
    // ──────────────────────────────────────────────────────────
    public function getPersonasEnRango(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'rol' => 'required|in:conductor,ocupante',
            'zone_id' => 'nullable|exists:zones,id',
        ]);

        $fi = $request->fecha_inicio;
        $ff = $request->fecha_fin;
        $zoneId = $request->zone_id;
        $rol = $request->rol;

        $query = Programacion::whereBetween('fecha', [$fi, $ff])
            ->whereIn('status', ['Programado', 'Reprogramado'])
            ->when($zoneId, fn($q) => $q->where('zone_id', $zoneId));

        if ($rol === 'conductor') {
            $ids = $query->pluck('conductor_id')->unique()->filter();
            $users = User::whereIn('id', $ids)->orderBy('name')->get(['id', 'name', 'dni']);
        } else {
            // ocupante = ayudantes en pivot
            $progIds = $query->pluck('id');
            $ids = DB::table('programacion_ayudante')
                ->whereIn('programacion_id', $progIds)
                ->pluck('user_id')->unique()->filter();
            $users = User::whereIn('id', $ids)->orderBy('name')->get(['id', 'name', 'dni']);
        }

        return response()->json($users);
    }

    // ──────────────────────────────────────────────────────────
    // GET RECURSOS EN RANGO (turno/vehiculo) — para el select "a reemplazar"
    // ──────────────────────────────────────────────────────────
    public function getRecursosEnRango(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'tipo' => 'required|in:turno,vehiculo',
            'zone_id' => 'nullable|exists:zones,id',
        ]);

        $fi = $request->fecha_inicio;
        $ff = $request->fecha_fin;
        $zoneId = $request->zone_id;
        $tipo = $request->tipo;

        $query = Programacion::whereBetween('fecha', [$fi, $ff])
            ->whereIn('status', ['Programado', 'Reprogramado'])
            ->when($zoneId, fn($q) => $q->where('zone_id', $zoneId));

        if ($tipo === 'turno') {
            $ids = $query->pluck('schedule_id')->unique()->filter();
            $data = Schedule::whereIn('id', $ids)->orderBy('name')
                ->get(['id', 'name', 'time_start', 'time_end'])
                ->map(fn($s) => ['id' => $s->id, 'label' => $s->name . ' (' . $s->time_start . ' - ' . $s->time_end . ')']);
        } else {
            $ids = $query->pluck('vehicle_id')->unique()->filter();
            $data = Vehicle::whereIn('id', $ids)->orderBy('name')
                ->get(['id', 'name', 'code'])
                ->map(fn($v) => ['id' => $v->id, 'label' => $v->code . ' — ' . $v->name]);
        }

        return response()->json($data);
    }

    // ──────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ──────────────────────────────────────────────────────────

    private function filtrarPorValorAnterior($query, string $tipo, int $anteriorId)
    {
        return match ($tipo) {
            'turno' => $query->where('schedule_id', $anteriorId),
            'vehiculo' => $query->where('vehicle_id', $anteriorId),
            'conductor' => $query->where('conductor_id', $anteriorId),
            'ocupante' => $query->whereHas('ayudantes', fn($q) => $q->where('user_id', $anteriorId)),
            default => $query,
        };
    }

    private function aplicarCambio(
        Programacion $prog,
        string $tipo,
        int $anteriorId,
        int $nuevoId,
        int $authId,
        string $motivo,
        int $loteId
    ): void {
        $valorAnteriorLabel = $this->resolverLabel($tipo, $anteriorId);
        $valorNuevoLabel = $this->resolverLabel($tipo, $nuevoId);

        match ($tipo) {
            'turno' => $prog->update(['schedule_id' => $nuevoId, 'status' => 'Reprogramado']),
            'vehiculo' => $prog->update(['vehicle_id' => $nuevoId, 'status' => 'Reprogramado']),
            'conductor' => $prog->update(['conductor_id' => $nuevoId, 'status' => 'Reprogramado']),
            'ocupante' => (function () use ($prog, $anteriorId, $nuevoId) {
                    $pivot = DB::table('programacion_ayudante')
                    ->where('programacion_id', $prog->id)
                    ->where('user_id', $anteriorId)
                    ->first();
                    $order = $pivot ? $pivot->order : 99;
                    $prog->ayudantes()->detach($anteriorId);
                    $prog->ayudantes()->attach($nuevoId, ['order' => $order]);
                    $prog->update(['status' => 'Reprogramado']);
                })(),
            default => null,
        };

        ProgramacionCambio::create([
            'programacion_id' => $prog->id,
            'user_id' => $authId,
            'campo' => $tipo,
            'valor_anterior' => $valorAnteriorLabel,
            'valor_nuevo' => $valorNuevoLabel,
            'motivo' => $motivo,
            'cambio_masivo_id' => $loteId,
        ]);
    }

    private function resolverLabel(string $tipo, int $id): string
    {
        return match ($tipo) {
            'turno' => (function () use ($id) {
                    $s = Schedule::find($id);
                    return $s ? $s->name . ' (' . $s->time_start . ' - ' . $s->time_end . ')' : "#{$id}";
                })(),
            'vehiculo' => optional(Vehicle::find($id))->code ?? "#{$id}",
            default => optional(User::find($id))->name ?? "#{$id}",
        };
    }
}