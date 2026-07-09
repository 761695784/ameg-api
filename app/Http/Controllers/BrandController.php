<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index()
    {
        return response()->json(Brand::withCount('products')->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:brands,name'],
            'logo' => ['nullable', 'string'],
        ]);

        $data['slug'] = Str::slug($data['name']);

        $brand = Brand::create($data);

        return response()->json($brand, 201);
    }

    public function update(Request $request, Brand $brand)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', 'unique:brands,name,' . $brand->id],
            'logo' => ['nullable', 'string'],
        ]);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $brand->update($data);

        return response()->json($brand);
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();

        return response()->json(null, 204);
    }
}
