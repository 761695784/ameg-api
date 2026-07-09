<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Realisation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RealisationController extends Controller
{
    /**
     * GET /api/realisations?sector=hotels
     */
    public function index(Request $request)
    {
        $query = Realisation::where('is_active', true)
            ->with(['images', 'services']);

        $query->when($request->filled('sector'), fn ($q) => $q->where('sector', $request->sector));

        return response()->json($query->latest()->paginate(12));
    }

    public function show(string $slug)
    {
        $realisation = Realisation::where('slug', $slug)
            ->where('is_active', true)
            ->with(['images', 'services'])
            ->firstOrFail();

        return response()->json($realisation);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'sector' => ['required', 'in:hotels,restaurants,fast_foods,boulangeries,patisseries,collectivites'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['exists:services,id'],
        ]);

        $data['slug'] = Str::slug($data['title']);
        $serviceIds = $data['service_ids'] ?? [];
        unset($data['service_ids']);

        $realisation = Realisation::create($data);
        $realisation->services()->sync($serviceIds);

        return response()->json($realisation->load('services'), 201);
    }

    public function update(Request $request, Realisation $realisation)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'sector' => ['sometimes', 'required', 'in:hotels,restaurants,fast_foods,boulangeries,patisseries,collectivites'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['exists:services,id'],
        ]);

        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if (isset($data['service_ids'])) {
            $realisation->services()->sync($data['service_ids']);
            unset($data['service_ids']);
        }

        $realisation->update($data);

        return response()->json($realisation->load('services'));
    }

    public function destroy(Realisation $realisation)
    {
        $realisation->delete();

        return response()->json(null, 204);
    }
}
