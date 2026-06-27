<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\BrandModel;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class BrandModelController extends Controller
{
    public function index(Request $request)
    {
        $models = BrandModel::with('brand')->get();

        if ($request->ajax()) {
            return DataTables::of($models)
                ->addColumn('brand_id', function ($model) {
                    return $model->brand
                        ? '<span class="badge badge-info">' . $model->brand->name . '</span>'
                        : 'Sin Marca';
                })
                ->addColumn('actions', function ($model) {
                    return '
                        <button class="btn btn-sm btn-warning btn-editar" id="' . $model->id . '">
                            <i class="fas fa-pen"></i>
                        </button>
                        <form action="' . route('admin.brandmodel.destroy', $model->id) . '" method="POST"
                            class="frmEliminar d-inline">' . method_field('DELETE') . csrf_field() . '
                            <button class="btn btn-sm btn-danger" type="submit">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>';
                })
                ->rawColumns(['brand_id', 'actions'])
                ->make(true);
        }

        return view('admin.brandmodels.index', compact('models'));
    }

    public function create()
    {
        $brands = Brand::pluck('name', 'id');
        return view('admin.brandmodels.create', compact('brands'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'     => 'required|unique:brandmodels,name',
                'code'     => 'required|unique:brandmodels,code',
                'brand_id' => 'required|integer|exists:brands,id',
            ]);

            BrandModel::create([
                'name'        => $request->name,
                'code'        => $request->code,
                'description' => $request->description,
                'brand_id'    => $request->brand_id,
            ]);

            return response()->json(['message' => 'Modelo registrado correctamente'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = implode(' | ', $e->validator->errors()->all());
            return response()->json(['message' => 'Validación: ' . $errors], 422);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function edit(string $id)
    {
        $model  = BrandModel::findOrFail($id);
        $brands = Brand::pluck('name', 'id');
        return view('admin.brandmodels.edit', compact('model', 'brands'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $model = BrandModel::findOrFail($id);

            $request->validate([
                'name'     => 'required|unique:brandmodels,name,' . $id,
                'code'     => 'required|unique:brandmodels,code,' . $id,
                'brand_id' => 'required|integer|exists:brands,id',
            ]);

            $model->update([
                'name'        => $request->name,
                'code'        => $request->code,
                'description' => $request->description,
                'brand_id'    => $request->brand_id,
            ]);

            return response()->json(['message' => 'Modelo actualizado correctamente'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = implode(' | ', $e->validator->errors()->all());
            return response()->json(['message' => 'Validación: ' . $errors], 422);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $model = BrandModel::withCount('vehicles')->findOrFail($id);

            if ($model->vehicles_count > 0) {
                return response()->json([
                    'message' => 'No se puede eliminar: el modelo tiene ' . $model->vehicles_count . ' vehículo(s) asociado(s).'
                ], 422);
            }

            $model->delete();
            return response()->json(['message' => 'Modelo eliminado correctamente'], 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }
}