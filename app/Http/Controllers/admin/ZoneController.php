<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\District;
use App\Models\Province;
use App\Models\Department;
use App\Models\Zonecoord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $zones = Zone::with(['district.province.department', 'zonecoords'])->select('zones.*');

            return DataTables::of($zones)
                ->addColumn('district_name', function ($zone) {
                    return $zone->district ? $zone->district->name : '<i class="text-muted">Sin asignar</i>';
                })
                ->addColumn('province_name', function ($zone) {
                    return ($zone->district && $zone->district->province)
                        ? $zone->district->province->name
                        : '<i class="text-muted">Sin asignar</i>';
                })
                ->addColumn('department_name', function ($zone) {
                    return ($zone->district && $zone->district->province && $zone->district->province->department)
                        ? $zone->district->province->department->name
                        : '<i class="text-muted">Sin asignar</i>';
                })
                ->addColumn('coords_count', function ($zone) {
                    $count = $zone->zonecoords ? $zone->zonecoords->count() : 0;

                    if ($count > 0) {
                        return '<span class="badge badge-info px-2 py-1" style="border-radius:50px;"><i class="fas fa-map-pin mr-1"></i>' . $count . ' Puntos</span>';
                    }
                    return '<span class="badge badge-secondary px-2 py-1" style="border-radius:50px;">Sin trazar</span>';
                })
                ->addColumn('status_badge', function ($zone) {
                    // Limpiamos espacios y estandarizamos a mayúsculas para evitar falsos "Inactivos"
                    $status = trim(strtoupper($zone->status));
                    if ($status === 'ACTIVO') {
                        return '<span class="badge badge-success px-2 py-1" style="border-radius:50px;"><i class="fas fa-check-circle mr-1"></i>Activo</span>';
                    }
                    return '<span class="badge badge-danger px-2 py-1" style="border-radius:50px;"><i class="fas fa-times-circle mr-1"></i>Inactivo</span>';
                })
                ->addColumn('formatted_date', function ($zone) {
                    return $zone->created_at ? Carbon::parse($zone->created_at)->format('d/m/Y H:i') : '-';
                })
                ->addColumn('actions', function ($zone) {
                    $btnEdit = '<button class="btn btn-sm btn-warning btn-editar mr-1" id="' . $zone->id . '" title="Editar"><i class="fas fa-pen"></i></button>';
                    $btnDelete = '<form action="' . route('admin.zone.destroy', $zone->id) . '" method="POST" class="frmEliminar" style="display:inline; margin:0; padding:0; border:none;">'
                        . method_field('DELETE') . csrf_field() .
                        '<button class="btn btn-sm btn-danger" type="submit" title="Eliminar"><i class="fas fa-trash-alt"></i></button></form>';
                    $btnMap = '<button class="btn btn-sm btn-primary btn-mapa ml-1"
                            id="' . $zone->id . '"
                            title="Ver Mapa">
                            <i class="fas fa-map"></i>
                        </button>';

                    return $btnEdit . $btnDelete . $btnMap;
                })
                ->rawColumns(['district_name', 'province_name', 'department_name', 'coords_count', 'status_badge', 'actions'])
                ->make(true);
        }

        return view('admin.zones.index');
    }

    public function create()
    {
        $departments = Department::orderBy('name', 'asc')->pluck('name', 'id');
        $provinces = [];
        $districts = [];
        return view('admin.zones.create', compact('departments', 'provinces', 'districts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|max:150',
                'department_id' => 'required|exists:departments,id',
                'province_id' => 'required|exists:provinces,id',
                'district_id' => 'required|exists:districts,id',
                'description' => 'nullable',
                'average_waste' => 'nullable|numeric|min:0',
                'status' => 'required|in:ACTIVO,INACTIVO',
                'coordinates' => 'required|string',
                'area' => 'nullable|numeric|min:0'
            ]);

            $coordsArray = json_decode($request->coordinates, true);

            if (!is_array($coordsArray) || count($coordsArray) < 3) {
                return response()->json(['message' => 'Debe trazar un polígono válido en el mapa (mínimo 3 puntos).'], 422);
            }

            DB::transaction(function () use ($request, $coordsArray) {

                $zone = Zone::create([
                    'name' => $request->name,
                    'district_id' => $request->district_id,
                    'description' => $request->description,
                    'average_waste' => $request->average_waste,
                    'status' => $request->status,
                    'area' => $request->area
                ]);

                foreach ($coordsArray as $point) {
                    Zonecoord::create([
                        'zone_id' => $zone->id,
                        'latitude' => $point['lat'],
                        'longitude' => $point['lng']
                    ]);
                }
            });

            return response()->json(['message' => 'Zona y perímetro registrados correctamente'], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $firstError = reset($errors)[0] ?? 'Datos inválidos.';
            return response()->json(['message' => $firstError], 422);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al guardar en el servidor: ' . $th->getMessage()], 500);
        }
    }

    public function edit(string $id)
    {
        $zone = Zone::with('zonecoords')->findOrFail($id);

        $currentDistrict = District::find($zone->district_id);
        $departments = Department::orderBy('name', 'asc')->pluck('name', 'id');

        $provinces = Province::where('department_id', $currentDistrict->department_id)->pluck('name', 'id');
        $districts = District::where('province_id', $currentDistrict->province_id)->pluck('name', 'id');

        $zone->department_id = $currentDistrict->department_id;
        $zone->province_id = $currentDistrict->province_id;

        // Formateo plano de coordenadas para Leaflet
        $mappedCoords = $zone->zonecoords->map(function ($coord) {
            return [
                'lat' => (float) $coord->latitude,
                'lng' => (float) $coord->longitude
            ];
        });

        $zone->coordinates = json_encode($mappedCoords);

        return view('admin.zones.edit', compact('zone', 'departments', 'provinces', 'districts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $zone = Zone::findOrFail($id);

            $request->validate([
                'name' => 'required|max:150',
                'department_id' => 'required|exists:departments,id',
                'province_id' => 'required|exists:provinces,id',
                'district_id' => 'required|exists:districts,id',
                'description' => 'nullable',
                'average_waste' => 'nullable|numeric|min:0',
                'status' => 'required|in:ACTIVO,INACTIVO',
                'coordinates' => 'required|string',
                'area' => 'nullable|numeric|min:0'
            ]);

            $coordsArray = json_decode($request->coordinates, true);

            if (!is_array($coordsArray) || count($coordsArray) < 3) {
                return response()->json(['message' => 'Debe trazar un polígono válido en el mapa (mínimo 3 puntos).'], 422);
            }

            DB::transaction(function () use ($request, $zone, $coordsArray) {

                $zone->update([
                    'name' => $request->name,
                    'district_id' => $request->district_id,
                    'description' => $request->description,
                    'average_waste' => $request->average_waste,
                    'status' => $request->status,
                    'area' => $request->area
                ]);

                $zone->zonecoords()->delete();

                foreach ($coordsArray as $point) {
                    Zonecoord::create([
                        'zone_id' => $zone->id,
                        'latitude' => $point['lat'],
                        'longitude' => $point['lng']
                    ]);
                }
            });

            return response()->json(['message' => 'Zona actualizada correctamente'], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $firstError = reset($errors)[0] ?? 'Datos inválidos.';
            return response()->json(['message' => $firstError], 422);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al actualizar en el servidor: ' . $th->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $zone = Zone::findOrFail($id);

            DB::transaction(function () use ($zone) {
                $zone->zonecoords()->delete();
                $zone->delete();
            });

            return response()->json(['message' => 'Zona eliminada correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al eliminar: ' . $th->getMessage()], 500);
        }
    }

    // Método adicional para obtener zonas con coordenadas formateadas para el mapa
    public function getZonesForMap(Request $request)
    {
        try {
            $query = Zone::with(['zonecoords', 'district']);

            // Filtro opcional por distrito
            if ($request->filled('district_id')) {
                $query->where('district_id', $request->district_id);
            }

            $zones = $query->get();

            $formatted = $zones->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'status' => $zone->status,
                    'description' => $zone->description,
                    'district_name' => $zone->district ? $zone->district->name : null,
                    'coords' => $zone->zonecoords->map(function ($coord) {
                        return [
                            (float) $coord->latitude,
                            (float) $coord->longitude,
                        ];
                    })->toArray(),
                ];
            });

            return response()->json($formatted, 200);

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }


    public function getSingleZoneMapDetails($id)
    {
        try {
            $zone = Zone::with(['zonecoords', 'district.province.department'])->findOrFail($id);
            // Retornamos la vista pasando los datos directamente
            return view('admin.zones.formMapaId', compact('zone'));

        } catch (\Throwable $th) {
            // En caso de error, redirige con un mensaje
            return redirect()->back()->with('error', 'No se pudo cargar la zona.');
        }
    }
}