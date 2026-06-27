<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\PersonalGroup;
use App\Models\Zone;
use App\Models\Schedule;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PersonalGroupController extends Controller
{
    // ── Días disponibles ───────────────────────────────────────
    const DAYS = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

    // ── Colores de badge por día ───────────────────────────────
    const DAY_COLORS = [
        'Lun' => '#3B82F6',
        'Mar' => '#8B5CF6',
        'Mié' => '#10B981',
        'Jue' => '#F59E0B',
        'Vie' => '#EF4444',
        'Sáb' => '#EC4899',
        'Dom' => '#6366F1',
    ];

    // ──────────────────────────────────────────────────────────
    // INDEX — DataTable
    // ──────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $groups = PersonalGroup::with([
                'zone',
                'schedule',
                'vehicle',
                'conductor',
                'members',   // FIX #4: era 'ayudantes' (relación inexistente)
            ])->select('personal_groups.*');

            return DataTables::of($groups)
                ->addColumn('zona_name', fn($g) => optional($g->zone)->name ?? '-')
                ->addColumn('schedule_badge', function ($g) {
                    $s = $g->schedule;
                    if (!$s)
                        return '-';
                    $color = str_contains(strtolower($s->name), 'noch') ? '#64748B' : '#F59E0B';
                    $label = $s->name . ' ' . $s->time_start . '-' . $s->time_end;
                    return '<span class="badge-turno" style="background:' . $color . ';color:#fff;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:600;white-space:nowrap;">'
                        . e($label) . '</span>';
                })
                ->addColumn('vehicle_name', fn($g) => optional($g->vehicle)->code ?? '-')
                ->addColumn('conductor_name', function ($g) {
                    $c = $g->conductor()->first();
                    return $c ? e($c->name) : '<span class="text-muted">—</span>';
                })
                // FIX #4: antes iteraba $g->ayudantes (relación inexistente),
                //         ahora filtra $g->members por pivot.role === 'ayudante'
                ->addColumn('ayudantes_names', function ($g) {
                    $html = '';
                    foreach ($g->members->where('pivot.role', 'ayudante') as $a) {
                        $html .= '<div>' . e($a->name) . '</div>';
                    }
                    return $html ?: '<span class="text-muted">—</span>';
                })
                ->addColumn('days_badges', function ($g) {
                    $html = '';
                    foreach ($g->days ?? [] as $day) {
                        $color = self::DAY_COLORS[$day] ?? '#6B7280';
                        $html .= '<span style="display:inline-block;background:' . $color . ';color:#fff;'
                            . 'padding:2px 7px;border-radius:12px;font-size:.7rem;font-weight:700;margin:1px;">'
                            . $day . '</span>';
                    }
                    return $html;
                })
                ->addColumn('badge_status', function ($g) {
                    if ($g->status === 'Activo') {
                        return '<span class="badge bg-success text-white px-2 py-1 rounded"><i class="fas fa-check-circle mr-1"></i>Activo</span>';
                    }
                    return '<span class="badge bg-secondary text-white px-2 py-1 rounded"><i class="fas fa-ban mr-1"></i>Inactivo</span>';
                })
                ->addColumn('created_fmt', fn($g) => $g->created_at ? $g->created_at->format('d/m/Y H:i') : '-')
                ->addColumn('actions', function ($g) {
                    $btnEdit = '<button class="btn btn-sm btn-warning btn-editar mr-1" data-id="' . $g->id . '" title="Editar"><i class="fas fa-pen text-dark"></i></button>';
                    $btnDelete = '<form action="' . route('admin.personal-group.destroy', $g->id) . '" method="POST" class="frmEliminar d-inline" style="margin:0;padding:0;">'
                        . method_field('DELETE') . csrf_field()
                        . '<button class="btn btn-sm btn-danger" type="submit" title="Eliminar"><i class="fas fa-trash-alt text-white"></i></button></form>';
                    return $btnEdit . $btnDelete;
                })
                ->rawColumns(['schedule_badge', 'conductor_name', 'ayudantes_names', 'days_badges', 'badge_status', 'actions'])
                ->make(true);
        }

        return view('admin.personal_groups.index');
    }

    // ──────────────────────────────────────────────────────────
    // CREATE — formulario vacío
    // ──────────────────────────────────────────────────────────
    public function create()
    {
        $zones = Zone::whereRaw('UPPER(status) = ?', ['ACTIVO'])
            ->orderBy('name')
            ->get();

        $schedules = Schedule::orderBy('name')->get();

        $vehicles = Vehicle::whereRaw('UPPER(status) = ?', ['ACTIVO'])
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
                'occupant_capacity'
            ]);

        $days = self::DAYS;

        return view('admin.personal_groups.template.form', compact(
            'zones',
            'schedules',
            'vehicles',
            'days'
        ));
    }

    // ──────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:100|unique:personal_groups,name',
                'zone_id' => 'required|exists:zones,id',
                'schedule_id' => 'required|exists:schedules,id',
                'vehicle_id' => 'required|exists:vehicles,id',
                'days' => 'required|array|min:1',
                'days.*' => 'in:Lun,Mar,Mié,Jue,Vie,Sáb,Dom',
                'conductor_id' => 'required|exists:users,id',
                'ayudantes' => 'nullable|array',
                'ayudantes.*' => 'exists:users,id',
            ]);

            // FIX #3: array_filter nativo elimina IDs "0"/0/null/false silenciosamente.
            //         Filtramos solo valores verdaderamente vacíos (null y string vacío).
            $ayudantes = array_values(array_filter(
                $data['ayudantes'] ?? [],
                fn($v) => $v !== null && $v !== ''
            ));

            // Validar capacidad del vehículo
            $vehicle = Vehicle::findOrFail($data['vehicle_id']);
            $totalPersonal = 1 + count($ayudantes); // conductor + ayudantes

            if ($totalPersonal > $vehicle->occupant_capacity) {
                return response()->json([
                    'message' => "El vehículo solo tiene capacidad para {$vehicle->occupant_capacity} persona(s). Estás registrando {$totalPersonal}."
                ], 422);
            }

            // Validar que conductor y ayudantes no sean el mismo
            if (in_array($data['conductor_id'], $ayudantes)) {
                return response()->json(['message' => 'El conductor no puede ser también ayudante.'], 422);
            }

            // Validar duplicados entre ayudantes
            if (count($ayudantes) !== count(array_unique($ayudantes))) {
                return response()->json(['message' => 'No puedes registrar al mismo ayudante más de una vez.'], 422);
            }

            // Validar tipos de usuario
            $conductor = User::findOrFail($data['conductor_id']);
            $this->validateUserType($conductor, 'conductor');

            foreach ($ayudantes as $aid) {
                $ayudante = User::findOrFail($aid);
                $this->validateUserType($ayudante, 'ayudante');
            }

            // Validar contratos activos
            $allIds = array_merge([$data['conductor_id']], $ayudantes);
            $this->validateActiveContracts($allIds);

            // Validar solapamiento de días
            $this->validateDayOverlap($data['days'], $allIds);

            DB::transaction(function () use ($data, $ayudantes) {
                $group = PersonalGroup::create([
                    'name' => $data['name'],
                    'zone_id' => $data['zone_id'],
                    'schedule_id' => $data['schedule_id'],
                    'vehicle_id' => $data['vehicle_id'],
                    'days' => $data['days'],
                    'status' => 'Activo',
                ]);

                // Conductor
                $group->members()->attach($data['conductor_id'], ['role' => 'conductor', 'order' => 0]);

                // Ayudantes
                foreach (array_values($ayudantes) as $i => $aid) {
                    $group->members()->attach($aid, ['role' => 'ayudante', 'order' => $i + 1]);
                }
            });

            return response()->json(['message' => 'Grupo de personal registrado correctamente.'], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => implode(' ', array_merge(...array_values($e->errors())))], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // EDIT — formulario con datos
    // ──────────────────────────────────────────────────────────
    public function edit($id)
    {
        $group = PersonalGroup::with(['members'])->findOrFail($id);

        $zones = Zone::whereRaw('UPPER(status) = ?', ['ACTIVO'])
            ->orderBy('name')
            ->get();

        $schedules = Schedule::orderBy('name')->get();

        $vehicles = Vehicle::whereRaw('UPPER(status) = ?', ['ACTIVO'])
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
                'occupant_capacity'
            ]);

        $days = self::DAYS;

        return view('admin.personal_groups.template.form', compact(
            'group',
            'zones',
            'schedules',
            'vehicles',
            'days'
        ));
    }

    // ──────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        try {
            $group = PersonalGroup::findOrFail($id);

            $data = $request->validate([
                'name' => 'required|string|max:100|unique:personal_groups,name,' . $id,
                'zone_id' => 'required|exists:zones,id',
                'schedule_id' => 'required|exists:schedules,id',
                'vehicle_id' => 'required|exists:vehicles,id',
                'days' => 'required|array|min:1',
                'days.*' => 'in:Lun,Mar,Mié,Jue,Vie,Sáb,Dom',
                'status' => 'required|in:Activo,Inactivo',
                'conductor_id' => 'required|exists:users,id',
                'ayudantes' => 'nullable|array',
                'ayudantes.*' => 'exists:users,id',
            ]);

            // FIX #3: mismo criterio de filtrado seguro que en store()
            $ayudantes = array_values(array_filter(
                $data['ayudantes'] ?? [],
                fn($v) => $v !== null && $v !== ''
            ));

            $vehicle = Vehicle::findOrFail($data['vehicle_id']);
            $totalPersonal = 1 + count($ayudantes);

            if ($totalPersonal > $vehicle->occupant_capacity) {
                return response()->json([
                    'message' => "El vehículo solo tiene capacidad para {$vehicle->occupant_capacity} persona(s). Estás registrando {$totalPersonal}."
                ], 422);
            }

            if (in_array($data['conductor_id'], $ayudantes)) {
                return response()->json(['message' => 'El conductor no puede ser también ayudante.'], 422);
            }

            if (count($ayudantes) !== count(array_unique($ayudantes))) {
                return response()->json(['message' => 'No puedes registrar al mismo ayudante más de una vez.'], 422);
            }

            $conductor = User::findOrFail($data['conductor_id']);
            $this->validateUserType($conductor, 'conductor');

            foreach ($ayudantes as $aid) {
                $this->validateUserType(User::findOrFail($aid), 'ayudante');
            }

            $allIds = array_merge([$data['conductor_id']], $ayudantes);
            $this->validateActiveContracts($allIds);

            // Solapamiento excluyendo el grupo actual
            $this->validateDayOverlap($data['days'], $allIds, $id);

            DB::transaction(function () use ($group, $data, $ayudantes) {
                $group->update([
                    'name' => $data['name'],
                    'zone_id' => $data['zone_id'],
                    'schedule_id' => $data['schedule_id'],
                    'vehicle_id' => $data['vehicle_id'],
                    'days' => $data['days'],
                    'status' => $data['status'],
                ]);

                $group->members()->detach();
                $group->members()->attach($data['conductor_id'], ['role' => 'conductor', 'order' => 0]);

                foreach (array_values($ayudantes) as $i => $aid) {
                    $group->members()->attach($aid, ['role' => 'ayudante', 'order' => $i + 1]);
                }
            });

            return response()->json(['message' => 'Grupo actualizado correctamente.'], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => implode(' ', array_merge(...array_values($e->errors())))], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────
    public function destroy($id)
    {
        try {
            PersonalGroup::findOrFail($id)->delete(); // cascade borra pivot
            return response()->json(['message' => 'Grupo eliminado correctamente.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // GET GROUP DATA (FIX #1: método faltante referenciado en rutas)
    // ──────────────────────────────────────────────────────────
    public function getGroupData($id)
    {
        try {
            $group = PersonalGroup::with(['zone', 'schedule', 'vehicle', 'members'])
                ->findOrFail($id);

            $conductor = $group->members->firstWhere('pivot.role', 'conductor');
            $ayudantes = $group->members->where('pivot.role', 'ayudante')->values();

            return response()->json([
                'id' => $group->id,
                'name' => $group->name,
                'zone_id' => $group->zone_id,
                'schedule_id' => $group->schedule_id,
                'vehicle_id' => $group->vehicle_id,
                'days' => $group->days ?? [],
                'status' => $group->status,

                
                'zone' => $group->zone ? [
                    'name' => $group->zone->name,
                ] : null,
                'schedule' => $group->schedule ? [
                    'name' => $group->schedule->name,
                    'time_start' => $group->schedule->time_start,
                    'time_end' => $group->schedule->time_end,
                ] : null,
                'vehicle' => $group->vehicle ? [
                    'name' => $group->vehicle->name,
                    'code' => $group->vehicle->code,
                ] : null,

                'conductor' => $conductor ? [
                    'id' => $conductor->id,
                    'name' => $conductor->name,
                    'dni' => $conductor->dni,
                ] : null,
                'ayudantes' => $ayudantes->map(fn($a) => [
                    'id' => $a->id,
                    'name' => $a->name,
                    'dni' => $a->dni,
                ]),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // SEARCH USERS (AJAX para búsqueda en tiempo real)
    // ──────────────────────────────────────────────────────────
    public function searchUsers(Request $request)
    {
        $query = $request->get('q', '');
        $role = $request->get('role', 'ayudante'); // conductor | ayudante
        $days = $request->get('days', []);
        $groupId = $request->get('group_id');         // para excluir en edición
        $excludeIds = $request->get('exclude', []);      // IDs ya seleccionados

        // Buscar usertypes que coincidan con el rol
        $usertypeNames = $role === 'conductor'
            ? ['Conductor', 'conductor']
            : ['Ayudante', 'ayudante'];

        $users = User::query()
            ->whereHas('contracts', fn($q) => $q->where('active', true))
            ->whereHas('usertype', fn($q) => $q->whereIn('name', $usertypeNames))
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('dni', 'LIKE', "%{$query}%");
            })
            ->when(!empty($excludeIds), fn($q) => $q->whereNotIn('id', $excludeIds))
            ->with('usertype')
            ->limit(10)
            ->get(['id', 'name', 'dni', 'usertype_id']);

        $result = $users->map(function ($u) use ($days, $groupId) {
            $conflict = $this->checkDayConflict($u->id, $days, $groupId);
            return [
                'id' => $u->id,
                'name' => $u->name,
                'dni' => $u->dni,
                'role_label' => optional($u->usertype)->name ?? '',
                'available' => !$conflict,
                'conflict' => $conflict,
            ];
        });

        return response()->json($result);
    }

    // ──────────────────────────────────────────────────────────
    // VEHICLE INFO (capacidad dinámica)
    // ──────────────────────────────────────────────────────────
    public function vehicleInfo($id)
    {
        $v = Vehicle::findOrFail($id);
        return response()->json([
            'occupant_capacity' => $v->occupant_capacity,
            'name' => $v->name,
            'code' => $v->code,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ──────────────────────────────────────────────────────────

    private function validateUserType(User $user, string $expectedRole): void
    {
        $typeName = strtolower(optional($user->usertype)->name ?? '');
        if ($typeName !== strtolower($expectedRole)) {
            throw new \Exception(
                "El usuario \"{$user->name}\" tiene tipo \"{$typeName}\" y no puede ser registrado como {$expectedRole}."
            );
        }
    }

    private function validateActiveContracts(array $userIds): void
    {
        $withoutContract = User::whereIn('id', $userIds)
            ->whereDoesntHave('contracts', fn($q) => $q->where('active', true))
            ->pluck('name');

        if ($withoutContract->isNotEmpty()) {
            throw new \Exception(
                'Los siguientes usuarios no tienen contrato activo: ' . $withoutContract->implode(', ')
            );
        }
    }

    private function validateDayOverlap(array $days, array $userIds, ?int $excludeGroupId = null): void
    {
        $conflictingGroups = PersonalGroup::where('status', 'Activo')
            ->when($excludeGroupId, fn($q) => $q->where('id', '!=', $excludeGroupId))
            ->whereHas('members', fn($q) => $q->whereIn('user_id', $userIds))
            ->get(['id', 'name', 'days']);

        foreach ($conflictingGroups as $g) {
            $overlap = array_intersect($days, $g->days ?? []);
            if (!empty($overlap)) {
                $conflictUsers = DB::table('personal_group_users')
                    ->join('users', 'users.id', '=', 'personal_group_users.user_id')
                    ->where('personal_group_users.personal_group_id', $g->id)
                    ->whereIn('personal_group_users.user_id', $userIds)
                    ->pluck('users.name');

                throw new \Exception(
                    'Conflicto de días (' . implode(', ', $overlap) . '): '
                    . $conflictUsers->implode(', ')
                    . ' ya está(n) asignado(s) al grupo "' . $g->name . '" en esos días.'
                );
            }
        }
    }

    private function checkDayConflict(int $userId, array $days, ?int $excludeGroupId = null): ?string
    {
        if (empty($days))
            return null;

        $groups = PersonalGroup::where('status', 'Activo')
            ->when($excludeGroupId, fn($q) => $q->where('id', '!=', $excludeGroupId))
            ->whereHas('members', fn($q) => $q->where('user_id', $userId))
            ->get(['id', 'name', 'days']);

        foreach ($groups as $g) {
            $overlap = array_intersect($days, $g->days ?? []);
            if (!empty($overlap)) {
                return 'Conflicto en ' . implode(', ', $overlap) . ' — grupo "' . $g->name . '"';
            }
        }

        return null;
    }
}