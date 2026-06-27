<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\UserType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UserTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            // Lectura de datos limpia para el motor de DataTables
            $types = UserType::select('*');

            return DataTables::of($types)
                ->addColumn('actions', function ($type) {
                    $btnEdit = '<button class="btn btn-sm btn-warning btn-editar mr-1" id="' . $type->id . '" title="Editar"><i class="fas fa-pen"></i></button>';
                    
                    $btnDelete = '<form action="' . route('admin.usertype.destroy', $type->id) . '" method="POST" class="frmEliminar" style="display:inline; margin:0; padding:0; border:none; text-decoration:none;">'
                        . method_field('DELETE') . csrf_field() .
                        '<button class="btn btn-sm btn-danger" type="submit" title="Eliminar" style="text-decoration:none;"><i class="fas fa-trash-alt"></i></button></form>';

                    return $btnEdit . $btnDelete;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        // Mantiene tu estándar "admin." apuntando a views/admin/userTypes/index.blade.php
        return view('admin.userTypes.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Mantiene tu estándar "admin."
        return view('admin.userTypes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|unique:user_types,name|max:100',
            ]);

            UserType::create([
                'name'        => $request->name,
                'description' => $request->description,
            ]);

            return response()->json(['message' => 'Tipo de usuario registrado correctamente'], 200);

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
        $type = UserType::find($id);
        
        // Mantiene tu estándar "admin." pasando la variable corregida $type
        return view('admin.userTypes.edit', compact('type'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $type = UserType::find($id);

            $request->validate([
                'name' => 'required|max:100|unique:user_types,name,' . $id,
            ]);

            $type->update([
                'name'        => $request->name,
                'description' => $request->description,
            ]);

            return response()->json(['message' => 'Tipo de usuario actualizado correctamente'], 200);

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
            $type = UserType::find($id);
            
            // Validación segura en caso de que aún no tengas declarada la relación en el Modelo
            if (method_exists($type, 'users') && $type->users()->count() > 0) {
                return response()->json(['message' => 'No se puede eliminar: existen usuarios asignados a este tipo.'], 422);
            }

            $type->delete();

            return response()->json(['message' => 'Tipo de usuario eliminado correctamente'], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al eliminar: ' . $th->getMessage()], 500);
        }
    }
}