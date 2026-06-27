<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $brands = Brand::withCount('brandmodels');

            return DataTables::of($brands)
                ->addColumn('logo', function ($brand) {
                    $url = $brand->logo
                        ? asset($brand->logo)
                        : 'https://placehold.co/90x50?text=Sin+Logo';
                    
                    return '<div class="d-flex align-items-center justify-content-center bg-white border rounded shadow-sm mx-auto" style="width: 90px; height: 50px; overflow: hidden; padding: 3px;">' .
                           '<img src="' . $url . '" style="max-width: 100%; max-height: 100%; object-fit: contain;" alt="logo">' .
                           '</div>';
                })
                ->addColumn('actions', function ($brand) {
                    $btnEdit = '<button class="btn btn-sm btn-warning btn-editar mr-1" id="' . $brand->id . '" title="Editar"><i class="fas fa-pen text-dark"></i></button>';
                    
                    $btnDelete = '<form action="' . route('admin.brand.destroy', $brand->id) . '" method="POST" class="frmEliminar" style="display:inline; margin:0; padding:0; border:none;">' 
                        . method_field('DELETE') . csrf_field() . 
                        '<button class="btn btn-sm btn-danger" type="submit" title="Eliminar" style="margin:0; vertical-align:middle;"><i class="fas fa-trash-alt"></i></button></form>';
                    
                    return $btnEdit . $btnDelete;
                })
                ->rawColumns(['logo', 'actions'])
                ->make(true);
        }

        return view('admin.brands.index');
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|unique:brands,name|max:100',
            ]);

            $logoUrl = null;
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/brand_logos');
                $logoUrl = Storage::url($path);
            }

            Brand::create([
                'name'        => $request->name,
                'description' => $request->description,
                'logo'        => $logoUrl,
            ]);

            return response()->json(['message' => 'Marca registrada correctamente'], 200);
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
        $brand = Brand::findOrFail($id);
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $brand = Brand::findOrFail($id);

            $request->validate([
                'name' => 'required|unique:brands,name,' . $id,
            ]);

            $logoUrl = $brand->logo;
            if ($request->hasFile('logo')) {
                if ($brand->logo) {
                    $filePath = str_replace('/storage', 'public', $brand->logo);
                    if (Storage::exists($filePath)) {
                        Storage::delete($filePath);
                    }
                }
                $path = $request->file('logo')->store('public/brand_logos');
                $logoUrl = Storage::url($path);
            }

            $brand->update([
                'name'        => $request->name,
                'description' => $request->description,
                'logo'        => $logoUrl,
            ]);

            return response()->json(['message' => 'Marca actualizada correctamente'], 200);
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
            $brand = Brand::withCount('brandmodels')->findOrFail($id);

            if ($brand->brandmodels_count > 0) {
                return response()->json([
                    'message' => 'No se puede eliminar la marca: tiene ' . $brand->brandmodels_count . ' modelo(s) asociado(s) en el sistema.'
                ], 422);
            }

            if ($brand->logo) {
                $filePath = str_replace('/storage', 'public', $brand->logo);
                if (Storage::exists($filePath)) {
                    Storage::delete($filePath);
                }
            }

            $brand->delete();
            return response()->json(['message' => 'Marca eliminada correctamente'], 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }
}