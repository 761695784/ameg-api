<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * GET /api/categories
     * Liste des 16 catégories avec leurs sous-catégories (pour le menu du catalogue).
     */
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->with(['subcategories' => fn ($q) => $q->where('is_active', true)->orderBy('order')])
            ->withCount('products')
            ->orderBy('order')
            ->get();

        return response()->json($categories);
    }

    /**
     * GET /api/categories/{slug}
     */
    public function show(string $slug)
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->with(['subcategories' => fn ($q) => $q->where('is_active', true)->orderBy('order')])
            ->firstOrFail();

        return response()->json($category);
    }

    /**
     * POST /api/admin/categories (protégé par auth:sanctum)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        $data['slug'] = \Illuminate\Support\Str::slug($data['name']);

        $category = Category::create($data);

        return response()->json($category, 201);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        if (isset($data['name'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        }

        $category->update($data);

        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json(null, 204);
    }
}
