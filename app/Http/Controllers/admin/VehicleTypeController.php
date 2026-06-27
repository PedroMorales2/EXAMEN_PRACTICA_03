<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class VehicleTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $types = VehicleType::all();

            return DataTables::of($types)
                ->addColumn('actions', function ($type) {
                    $btnEdit = '<button class="btn btn-sm btn-warning btn-editar mr-1" id="' . $type->id . '" title="Editar"><i class="fas fa-pen"></i></button>';
                    $btnDelete = '<form action="' . route('admin.tipo-vehiculo.destroy', $type->id) . '" method="POST" class="frmEliminar" style="display:inline; margin:0; padding:0; border:none; text-decoration:none;">'
                        . method_field('DELETE') . csrf_field() .
                        '<button class="btn btn-sm btn-danger" type="submit" title="Eliminar" style="text-decoration:none;"><i class="fas fa-trash-alt"></i></button></form>';

                    return $btnEdit . $btnDelete;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.tipo_vehiculos.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tipo_vehiculos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|unique:vehicle_types|max:100',
            ]);

            VehicleType::create([
                'name'        => $request->name,
                'description' => $request->description,
            ]);

            return response()->json(['message' => 'Tipo de vehículo registrado correctamente'], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => $e->errors()['name'][0] ?? 'Datos inválidos.'], 422);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al guardar: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $type = VehicleType::find($id);
        return view('admin.tipo_vehiculos.edit', compact('type'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $type = VehicleType::find($id);

            $request->validate([
                'name' => 'required|max:100|unique:vehicle_types,name,' . $id,
            ]);

            $type->update([
                'name'        => $request->name,
                'description' => $request->description,
            ]);

            return response()->json(['message' => 'Tipo de vehículo actualizado correctamente'], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => $e->errors()['name'][0] ?? 'Datos inválidos.'], 422);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al actualizar: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $type = VehicleType::find($id);
            $type->delete();

            return response()->json(['message' => 'Tipo de vehículo eliminado correctamente'], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al eliminar: ' . $th->getMessage()], 500);
        }
    }
}