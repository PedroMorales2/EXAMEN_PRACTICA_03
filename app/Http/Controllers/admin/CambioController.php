<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Cambios;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CambioController extends Controller
{
    // ──────────────────────────────────────────────────────────
    // INDEX — DataTable
    // ──────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $cambios = Cambios::select('cambios.*');

            return DataTables::of($cambios)
                ->addColumn('created_fmt', fn($c) =>
                    $c->created_at ? $c->created_at->format('d/m/Y H:i') : '-')
                ->addColumn('updated_fmt', fn($c) =>
                    $c->updated_at ? $c->updated_at->format('d/m/Y H:i') : '-')
                ->addColumn('actions', function ($c) {
                    $btnEdit = '<button class="btn btn-sm btn-warning btn-editar mr-1"
                                        data-id="' . $c->id . '" title="Editar">
                                    <i class="fas fa-pen text-dark"></i>
                                </button>';
                    $btnDelete = '<form action="' . route('admin.cambio.destroy', $c->id) . '"
                                        method="POST" class="frmEliminar d-inline"
                                        style="margin:0;padding:0;">'
                               . method_field('DELETE') . csrf_field()
                               . '<button class="btn btn-sm btn-danger" type="submit" title="Eliminar">
                                      <i class="fas fa-trash-alt text-white"></i>
                                  </button></form>';
                    return $btnEdit . $btnDelete;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.cambios.index');
    }

    // ──────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────
    public function create()
    {
        return view('admin.cambios.template.form');
    }

    // ──────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name'        => 'required|string|max:100|unique:cambios,name',
                'description' => 'nullable|string|max:500',
            ]);

            Cambios::create($data);

            return response()->json(['message' => 'Motivo registrado correctamente.'], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => implode(' ', array_merge(...array_values($e->errors()))),
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────
    public function edit($id)
    {
        $cambio = Cambios::findOrFail($id);
        return view('admin.cambios.template.form', compact('cambio'));
    }

    // ──────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        try {
            $cambio = Cambios::findOrFail($id);

            $data = $request->validate([
                'name'        => 'required|string|max:100|unique:cambios,name,' . $id,
                'description' => 'nullable|string|max:500',
            ]);

            $cambio->update($data);

            return response()->json(['message' => 'Motivo actualizado correctamente.'], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => implode(' ', array_merge(...array_values($e->errors()))),
            ], 422);
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
            Cambios::findOrFail($id)->delete();
            return response()->json(['message' => 'Motivo eliminado correctamente.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}