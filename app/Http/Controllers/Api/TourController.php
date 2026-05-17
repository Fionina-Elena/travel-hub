<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TourController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Tour::with(['category', 'images', 'dates']);

        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->has('min_days')) {
            $query->where('duration_days', '>=', $request->min_days);
        }

        if ($request->has('max_days')) {
            $query->where('duration_days', '<=', $request->max_days);
        }

        if ($request->has('min_price')) {
            $query->whereHas('dates', function ($q) use ($request) {
                $q->where('price', '>=', $request->min_price);
            });
        }

        if ($request->has('max_price')) {
            $query->whereHas('dates', function ($q) use ($request) {
                $q->where('price', '<=', $request->max_price);
            });
        }

        if ($request->boolean('published_only', false)) {
            $query->where('is_published', true);
        }

        $tours = $query->paginate(12);

        return response()->json($tours);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'duration_days' => 'required|integer|min:1',
            'route_points' => 'nullable|array',
            'highlights' => 'nullable|array',
            'included' => 'nullable|string',
            'excluded' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        $images = $request->input('images', []);

        $tour = Tour::create($validated);

        if (! empty($images)) {
            foreach ($images as $image) {
                if (isset($image['url'])) {
                    $tour->images()->create([
                        'url' => $image['url'],
                        'alt' => $image['alt'] ?? '',
                    ]);
                }
            }
        }

        return response()->json($tour->load(['category', 'images', 'dates']), 201);
    }

    public function show(Tour $tour): JsonResponse
    {
        $tour->load(['category', 'images', 'dates']);

        return response()->json($tour);
    }

    public function update(Request $request, Tour $tour): JsonResponse
    {
        Log::info('Update request images:', $request->input('images', []));

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'duration_days' => 'sometimes|integer|min:1',
            'route_points' => 'nullable|array',
            'highlights' => 'nullable|array',
            'included' => 'nullable|string',
            'excluded' => 'nullable|string',
            'is_published' => 'boolean',
            'images' => 'nullable|array',
        ]);

        $tour->update($validated);

        if ($request->has('images')) {
            $tour->images()->delete();
            foreach ($request->images as $image) {
                if (isset($image['url'])) {
                    $tour->images()->create([
                        'url' => $image['url'],
                        'alt' => $image['alt'] ?? '',
                    ]);
                }
            }
        }

        return response()->json($tour->load(['category', 'images', 'dates']));
    }

    public function destroy(Tour $tour): JsonResponse
    {
        $tour->delete();

        return response()->json(null, 204);
    }

    public function addImages(Request $request, Tour $tour): JsonResponse
    {
        $validated = $request->validate([
            'images' => 'required|array',
            'images.*.url' => 'required|string',
            'images.*.alt' => 'nullable|string',
            'images.*.sort_order' => 'nullable|integer',
        ]);

        foreach ($validated['images'] as $image) {
            $tour->images()->create($image);
        }

        return response()->json($tour->load('images'));
    }

    public function search(Request $request): JsonResponse
    {
        $queryText = $request->validate(['q' => 'required|string'])['q'];

        $searchTerm = '%'.$queryText.'%';
        $results = Tour::with(['category', 'images', 'dates'])
            ->where('is_published', true)
            ->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(title) LIKE ?', [strtolower($searchTerm)])
                    ->orWhereRaw('LOWER(description) LIKE ?', [strtolower($searchTerm)]);
            })
            ->get();

        return response()->json($results);
    }
}
