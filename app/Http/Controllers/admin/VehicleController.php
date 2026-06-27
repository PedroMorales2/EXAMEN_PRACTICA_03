<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Brand;
use App\Models\BrandModel;
use App\Models\VehicleType;
use App\Models\VehicleColor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Models\VehicleImage;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $vehicles = Vehicle::with('brand', 'brandmodel', 'vehicletype', 'vehiclecolor', 'images')->get();

        if ($request->ajax()) {
            return DataTables::of($vehicles)
                ->addColumn('image', function ($vehicle) {
                    $img = $vehicle->images->where('profile', 1)->first();
                    $url = $img ? asset($img->image) : 'https://placehold.co/50x50?text=Sin+Foto';
                    return '<img src="' . $url . '" width="50px" class="img-thumbnail rounded">';
                })
                ->addColumn('brand_id', function ($vehicle) {
                    return $vehicle->brand ? $vehicle->brand->name : 'Sin Marca';
                })
                ->addColumn('model_id', function ($vehicle) {
                    return $vehicle->brandmodel ? $vehicle->brandmodel->name : 'Sin Modelo';
                })
                ->addColumn('type_id', function ($vehicle) {
                    return $vehicle->vehicletype ? $vehicle->vehicletype->name : 'Sin Tipo';
                })
                ->addColumn('color_id', function ($vehicle) {
                    if (!$vehicle->vehiclecolor) return 'Sin Color';
                    return '<span class="badge" style="background-color:' . ($vehicle->vehiclecolor->code ?? '#ccc') . '; color:#fff;">'
                        . $vehicle->vehiclecolor->name . '</span>';
                })
                ->addColumn('actions', function ($vehicle) {
                    return '
                        <button class="btn btn-sm btn-warning btn-editar" id="' . $vehicle->id . '" title="Editar">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button class="btn btn-sm btn-info btn-imagenes" data-id="' . $vehicle->id . '" title="Imágenes">
                            <i class="fas fa-images"></i>
                        </button>
                        <form action="' . route('admin.vehicle.destroy', $vehicle->id) . '" method="POST"
                            class="frmEliminar d-inline">' . method_field('DELETE') . csrf_field() . '
                            <button class="btn btn-sm btn-danger" type="submit" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>';
                })
                ->rawColumns(['image', 'color_id', 'actions'])
                ->make(true);
        }

        return view('admin.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        $brands     = Brand::pluck('name', 'id');
        $types      = VehicleType::pluck('name', 'id');
        $colors     = VehicleColor::pluck('name', 'id');
        $models     = collect();

        return view('admin.vehicles.create', compact('brands', 'types', 'colors', 'models'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'code'                 => 'required|unique:vehicles,code',
                'name'                 => 'required',
                'plate'                => ['required', 'unique:vehicles,plate', 'regex:/^([A-Z0-9]{6}|[A-Z0-9]{2}-[A-Z0-9]{4}|[A-Z0-9]{3}-[A-Z0-9]{3})$/'],
                'year'                 => 'required|digits:4|integer|min:1900|max:' . (date('Y') + 1),
                'brand_id'             => 'required|exists:brands,id',
                'model_id'             => 'required|exists:brandmodels,id',
                'type_id'              => 'required|exists:vehicle_types,id',
                'color_id'             => 'required|exists:vehicle_colors,id',
                'load_capacity'        => 'required|numeric|min:0',
                'fuel_capacity'        => 'required|numeric|min:0',
                'compaction_capacity'  => 'nullable|numeric|min:0',
                'occupant_capacity'    => 'required|integer|min:1',
            ]);

            Vehicle::create([
                'code'                => $request->code,
                'name'                => $request->name,
                'plate'               => $request->plate,
                'year'                => $request->year,
                'brand_id'            => $request->brand_id,
                'model_id'            => $request->model_id,
                'type_id'             => $request->type_id,
                'color_id'            => $request->color_id,
                'load_capacity'       => $request->load_capacity,
                'fuel_capacity'       => $request->fuel_capacity,
                'compaction_capacity' => $request->compaction_capacity,
                'occupant_capacity'   => $request->occupant_capacity,
                'description'         => $request->description,
                'status'              => $request->status ?? 'activo',
            ]);

            return response()->json(['message' => 'Vehículo registrado correctamente'], 200);
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
        $vehicle = Vehicle::findOrFail($id);
        $brands  = Brand::pluck('name', 'id');
        $types   = VehicleType::pluck('name', 'id');
        $colors  = VehicleColor::pluck('name', 'id');
        $models  = BrandModel::where('brand_id', $vehicle->brand_id)->pluck('name', 'id');

        return view('admin.vehicles.edit', compact('vehicle', 'brands', 'types', 'colors', 'models'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $vehicle = Vehicle::findOrFail($id);

            $request->validate([
                'code'                => 'required|unique:vehicles,code,' . $id,
                'name'                => 'required',
                'plate'               => ['required', 'unique:vehicles,plate,' . $id, 'regex:/^([A-Z0-9]{6}|[A-Z0-9]{2}-[A-Z0-9]{4}|[A-Z0-9]{3}-[A-Z0-9]{3})$/'],
                'year'                => 'required|digits:4|integer|min:1900|max:' . (date('Y') + 1),
                'brand_id'            => 'required|exists:brands,id',
                'model_id'            => 'required|exists:brandmodels,id',
                'type_id'             => 'required|exists:vehicle_types,id',
                'color_id'            => 'required|exists:vehicle_colors,id',
                'load_capacity'       => 'required|numeric|min:0',
                'fuel_capacity'       => 'required|numeric|min:0',
                'compaction_capacity' => 'nullable|numeric|min:0',
                'occupant_capacity'   => 'required|integer|min:1',
            ]);

            $vehicle->update([
                'code'                => $request->code,
                'name'                => $request->name,
                'plate'               => $request->plate,
                'year'                => $request->year,
                'brand_id'            => $request->brand_id,
                'model_id'            => $request->model_id,
                'type_id'             => $request->type_id,
                'color_id'            => $request->color_id,
                'load_capacity'       => $request->load_capacity,
                'fuel_capacity'       => $request->fuel_capacity,
                'compaction_capacity' => $request->compaction_capacity,
                'occupant_capacity'   => $request->occupant_capacity,
                'description'         => $request->description,
                'status'              => $request->status,
            ]);

            return response()->json(['message' => 'Vehículo actualizado correctamente'], 200);
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
            $vehicle = Vehicle::findOrFail($id);
            $vehicle->delete();
            return response()->json(['message' => 'Vehículo eliminado correctamente'], 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    // Cargar modelos según marca seleccionada
    public function modelsByBrand(Request $request)
    {
        $models = BrandModel::where('brand_id', $request->brand_id)
                            ->select('id', 'name')
                            ->get();
        return response()->json($models);
    }

    // Mostrar imágenes de un vehículo
public function getImages(string $id)
{
    try {
        $vehicle = Vehicle::with('images')->findOrFail($id);
        return response()->json([
            'images' => $vehicle->images,
            'vehicle' => $vehicle->name . ' - ' . $vehicle->plate,
        ]);
    } catch (\Throwable $th) {
        return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
    }
}

// Subir imagen
public function uploadImage(Request $request, string $id)
{
    try {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $vehicle = Vehicle::findOrFail($id);

        $file     = $request->file('image');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path     = $file->storeAs('vehicles', $filename, 'public');

        // Si es la primera imagen, marcarla como perfil
        $isFirst = $vehicle->images()->count() === 0;

        VehicleImage::create([
            'vehicle_id' => $vehicle->id,
            'image'      => 'storage/' . $path,
            'profile'    => $isFirst ? 1 : 0,
        ]);

        return response()->json(['message' => 'Imagen subida correctamente'], 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['message' => implode(' | ', $e->validator->errors()->all())], 422);
    } catch (\Throwable $th) {
        Log::error($th);
        return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
    }
}

// Eliminar imagen
public function deleteImage(string $imageId)
{
    try {
        $image = VehicleImage::findOrFail($imageId);
        $wasProfile = $image->profile;
        $vehicleId  = $image->vehicle_id;

        // Borrar archivo físico
        $filePath = str_replace('storage/', '', $image->image);
        \Storage::disk('public')->delete($filePath);

        $image->delete();

        // Si era la de perfil, asignar la siguiente como perfil
        if ($wasProfile) {
            $next = VehicleImage::where('vehicle_id', $vehicleId)->first();
            if ($next) $next->update(['profile' => 1]);
        }

        return response()->json(['message' => 'Imagen eliminada correctamente'], 200);
    } catch (\Throwable $th) {
        return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
    }
}

// Marcar imagen como perfil
public function setProfile(string $imageId)
{
    try {
        $image = VehicleImage::findOrFail($imageId);

        // Quitar perfil a todas las del vehículo
        VehicleImage::where('vehicle_id', $image->vehicle_id)->update(['profile' => 0]);

        // Marcar esta como perfil
        $image->update(['profile' => 1]);

        return response()->json(['message' => 'Imagen de perfil actualizada'], 200);
    } catch (\Throwable $th) {
        return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
    }
}
}