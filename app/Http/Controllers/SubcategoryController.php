<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubcategoryController extends Controller
{
    /**
     * GET /api/categories/{categoryId}/subcategories
     */
    public function index(int $categoryId)
    {
        $subcategories = Subcategory::where('category_id', $categoryId)
            ->where('is_active', true)
            ->withCount('products')
            ->orderBy('order')
            ->get();

        return response()->json($subcategories);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        $data['slug'] = Str::slug($data['name']);

        $subcategory = Subcategory::create($data);

        return response()->json($subcategory, 201);
    }

    public function update(Request $request, Subcategory $subcategory)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $subcategory->update($data);

        return response()->json($subcategory);
    }

    public function destroy(Subcategory $subcategory)
    {
        $subcategory->delete();

        return response()->json(null, 204);
    }
}
