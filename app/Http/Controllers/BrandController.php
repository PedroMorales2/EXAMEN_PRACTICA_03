<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::orderBy('name')->get();
        return view('brand.index', compact('brands'));
    }

    public function create()
    {
        return view('brand.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'logo' => 'nullable|image'
        ]);

        $path = null;

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('brands', 'public');
        }

        $brand = Brand::create([
            'name' => $request->name,
            'description' => $request->description,
            'logo' => $path
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Brand created successfully',
                'brand' => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'description' => $brand->description,
                    'logo' => $brand->logo,
                    'created_at' => $brand->created_at->format('d/m/Y')
                ]
            ], 201);
        }

        return redirect()->route('brand.index')
            ->with('success', 'Brand created successfully');
    }

    public function edit(Brand $brand)
    {
        return view('brand.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|max:100',
            'logo' => 'nullable|image'
        ]);

        $data = [
            'name' => $request->name,
            'description' => $request->description
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Brand updated successfully',
                'brand' => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'description' => $brand->description,
                    'logo' => $brand->logo,
                    'created_at' => $brand->created_at->format('d/m/Y')
                ]
            ], 200);
        }

        return redirect()->route('brand.index')
            ->with('success', 'Brand updated successfully');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['message' => 'Brand deleted successfully'], 200);
        }

        return redirect()->route('brand.index')
            ->with('success', 'Brand deleted successfully');
    }

}