<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleColor;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class VehicleColorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            
            $colors = VehicleColor::all();

            return DataTables::of($colors)

                ->addColumn('actions', function ($color) {
                    $btnEdit = '<button class="btn btn-sm btn-warning btn-editar mr-1" id="' . $color->id . '" title="Editar"><i class="fas fa-pen"></i></button>';
                    $btnDelete = '<form action="' . route('admin.color.destroy', $color->id) . '" method="POST" class="frmEliminar" style="display:inline; margin:0; padding:0; border:none; text-decoration:none;">' 
                        . method_field('DELETE') . csrf_field() . 
                        '<button class="btn btn-sm btn-danger" type="submit" title="Eliminar" style="text-decoration:none;"><i class="fas fa-trash-alt"></i></button></form>';
                    
                    return $btnEdit . $btnDelete;
                })
                ->rawColumns(['actions']) 
                ->make(true);
        }

        return view('admin.colors.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.colors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|unique:vehicle_colors|max:100',
                'code' => 'required|max:50'
            ]);

            VehicleColor::create([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description
            ]);

            return response()->json(['message' => 'Color registrado correctamente'], 200);

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
        $color = VehicleColor::find($id);
        return view('admin.colors.edit', compact('color'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $color = VehicleColor::find($id);

            $request->validate([
                'code' => 'required|max:50',
                'name' => 'required|unique:vehicle_colors,name,' . $id
            ]);

            // Actualizamos el registro en la base de datos
            $color->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description
            ]);

            return response()->json(['message' => 'Color actualizado correctamente'], 200);

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
            $color = VehicleColor::find($id);

            $color->delete();

            return response()->json(['message' => 'Color eliminado correctamente'], 200);
            
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al eliminar: ' . $th->getMessage()], 500);
        }
    }
}
