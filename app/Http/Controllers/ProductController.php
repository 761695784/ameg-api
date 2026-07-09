<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * GET /api/products
     *
     * Filtres disponibles (query params) :
     *  - q              : recherche texte (nom, référence, marque, catégorie, sous-catégorie)
     *  - category_id
     *  - subcategory_id
     *  - brand_id
     *  - availability   : en_stock | sur_commande | rupture
     *  - sort           : name | popularity | availability (défaut : name)
     *  - per_page       : défaut 24
     */
    public function index(Request $request)
    {
        $query = Product::query()
            ->where('is_active', true)
            ->with(['brand', 'category', 'subcategory', 'primaryImage']);

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('subcategory', fn ($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        $query->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id));
        $query->when($request->filled('subcategory_id'), fn ($q) => $q->where('subcategory_id', $request->subcategory_id));
        $query->when($request->filled('brand_id'), fn ($q) => $q->where('brand_id', $request->brand_id));
        $query->when($request->filled('availability'), fn ($q) => $q->where('availability', $request->availability));

        match ($request->input('sort', 'name')) {
            'popularity' => $query->orderByDesc('views_count'),
            'availability' => $query->orderBy('availability'),
            default => $query->orderBy('name'),
        };

        $products = $query->paginate($request->input('per_page', 24));

        return response()->json($products);
    }

    /**
     * GET /api/products/{slug}
     * Fiche produit complète + produits similaires (même sous-catégorie).
     */
    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['brand', 'category', 'subcategory', 'images', 'characteristics', 'documents'])
            ->firstOrFail();

        $product->increment('views_count');

        $similar = Product::where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($product) {
                $q->where('subcategory_id', $product->subcategory_id)
                    ->orWhere('category_id', $product->category_id);
            })
            ->with('primaryImage')
            ->limit(8)
            ->get();

        return response()->json([
            'product' => $product,
            'grouped_characteristics' => $product->groupedCharacteristics,
            'similar_products' => $similar,
        ]);
    }

    /**
     * POST /api/admin/products
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:100', 'unique:products,reference'],
            'name' => ['required', 'string', 'max:255'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'exists:subcategories,id'],
            'description' => ['nullable', 'string'],
            'price_fcfa' => ['nullable', 'numeric', 'min:0'],
            'availability' => ['nullable', 'in:en_stock,sur_commande,rupture'],
            'is_featured' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        $data['slug'] = Str::slug($data['reference'] . '-' . $data['name']);

        $product = Product::create($data);

        return response()->json($product->load(['brand', 'category', 'subcategory']), 201);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'exists:subcategories,id'],
            'description' => ['nullable', 'string'],
            'price_fcfa' => ['nullable', 'numeric', 'min:0'],
            'availability' => ['nullable', 'in:en_stock,sur_commande,rupture'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($product->reference . '-' . $data['name']);
        }

        $product->update($data);

        return response()->json($product->load(['brand', 'category', 'subcategory']));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(null, 204);
    }
}
