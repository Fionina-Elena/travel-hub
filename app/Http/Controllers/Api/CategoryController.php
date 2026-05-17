<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SupabaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    private SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function index(): JsonResponse
    {
        $categories = $this->supabase->get('categories', ['order' => 'id']);

        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $slug = Str::slug($validated['name']);

        $checkSlug = $this->supabase->get('categories', [
            'slug' => 'eq.' . $slug,
            'limit' => 1,
        ]);

        if (!empty($checkSlug)) {
            $slug = $slug . '-' . time();
        }

        $category = $this->supabase->create('categories', [
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
        ]);

        if (!$category) {
            return response()->json(['error' => 'Failed to create category'], 500);
        }

        return response()->json($category, 201);
    }

    public function show($id): JsonResponse
    {
        $id = (int) $id;
        $category = $this->supabase->find('categories', $id);

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        return response()->json($category);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $id = (int) $id;
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data = [];
        if (isset($validated['name'])) {
            $data['name'] = $validated['name'];
            $data['slug'] = Str::slug($validated['name']);
        }
        if (array_key_exists('description', $validated)) {
            $data['description'] = $validated['description'];
        }

        $category = $this->supabase->update('categories', $id, $data);

        if (!$category) {
            return response()->json(['error' => 'Failed to update category'], 500);
        }

        return response()->json($category);
    }

    public function destroy($id): JsonResponse
    {
        $id = (int) $id;
        $this->supabase->delete('categories', $id);

        return response()->json(null, 204);
    }
}