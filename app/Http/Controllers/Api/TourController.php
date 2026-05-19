<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SupabaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TourController extends Controller
{
    private SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function index(Request $request): JsonResponse
    {
        $params = ['select' => 'id,category_id,title,slug,description,duration_days,route_points,highlights,included,excluded,is_published,created_at,updated_at,category:categories(*),images:tour_images(*),dates:tour_dates(*)'];

        if ($request->has('category')) {
            $params['category_id'] = 'eq.'.$request->category;
        }

        if ($request->has('min_days')) {
            $params['duration_days'] = 'gte.'.$request->min_days;
        }

        if ($request->has('max_days')) {
            $params['duration_days'] = 'lte.'.$request->max_days;
        }

        if ($request->boolean('published_only', false)) {
            $params['is_published'] = 'eq.true';
        }

        $tours = $this->supabase->get('tours', $params);

        foreach ($tours as &$tour) {
            $tour = $this->decodeTourJson($tour);
        }

        return response()->json([
            'data' => $tours,
            'total' => count($tours),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'duration_days' => 'required|integer|min:1',
            'route_points' => 'nullable|array',
            'highlights' => 'nullable|array',
            'included' => 'nullable|string',
            'excluded' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        $tourData = [
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'duration_days' => $validated['duration_days'],
            'is_published' => $validated['is_published'] ?? false,
            'included' => $validated['included'] ?? null,
            'excluded' => $validated['excluded'] ?? null,
        ];

        if (isset($validated['route_points']) && is_array($validated['route_points'])) {
            $tourData['route_points'] = json_encode($validated['route_points'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($validated['highlights']) && is_array($validated['highlights'])) {
            $tourData['highlights'] = json_encode($validated['highlights'], JSON_UNESCAPED_UNICODE);
        }

        $tour = $this->supabase->create('tours', $tourData);

        if (! $tour) {
            return response()->json(['error' => 'Failed to create tour'], 500);
        }

        $images = $request->input('images', []);
        if (! empty($images)) {
            foreach ($images as $image) {
                if (isset($image['url'])) {
                    $this->supabase->create('tour_images', [
                        'tour_id' => $tour['id'],
                        'url' => $image['url'],
                        'alt' => $image['alt'] ?? '',
                    ]);
                }
            }
        }

        $tour['images'] = $this->supabase->get('tour_images', ['tour_id' => 'eq.'.$tour['id']]);
        $tour['dates'] = $this->supabase->get('tour_dates', ['tour_id' => 'eq.'.$tour['id']]);

        return response()->json($tour, 201);
    }

    public function show($id): JsonResponse
    {
        $id = (int) $id;
        $tour = $this->supabase->find('tours', $id);

        if (! $tour) {
            return response()->json(['error' => 'Tour not found'], 404);
        }

        $tour['images'] = $this->supabase->get('tour_images', ['tour_id' => 'eq.'.$id]);
        $tour['dates'] = $this->supabase->get('tour_dates', ['tour_id' => 'eq.'.$id]);

        $tour = $this->decodeTourJson($tour);

        return response()->json($tour);
    }

    private function decodeTourJson(array $tour): array
    {
        if (array_key_exists('route_points', $tour) && $tour['route_points'] !== null) {
            if (is_string($tour['route_points'])) {
                $decoded = json_decode($tour['route_points'], true);
                $tour['route_points'] = is_array($decoded) ? $decoded : [];
            } elseif (! is_array($tour['route_points'])) {
                $tour['route_points'] = [];
            }
        } else {
            $tour['route_points'] = [];
        }

        if (array_key_exists('highlights', $tour) && $tour['highlights'] !== null) {
            if (is_string($tour['highlights'])) {
                $decoded = json_decode($tour['highlights'], true);
                $tour['highlights'] = is_array($decoded) ? $decoded : [];
            } elseif (! is_array($tour['highlights'])) {
                $tour['highlights'] = [];
            }
        } else {
            $tour['highlights'] = [];
        }

        return $tour;
    }

    public function update(Request $request, $id): JsonResponse
    {
        $id = (int) $id;
        $validated = $request->validate([
            'category_id' => 'sometimes|integer',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'duration_days' => 'sometimes|integer|min:1',
            'route_points' => 'nullable|array',
            'highlights' => 'nullable|array',
            'included' => 'nullable|string',
            'excluded' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        $tourData = [];
        foreach ($validated as $key => $value) {
            if (in_array($key, ['route_points', 'highlights']) && is_array($value)) {
                $tourData[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
            } elseif ($key !== 'images') {
                $tourData[$key] = $value;
            }
        }

        $tour = $this->supabase->update('tours', $id, $tourData);

        if (! $tour) {
            return response()->json(['error' => 'Failed to update tour'], 500);
        }

        if ($request->has('images')) {
            $existingImages = $this->supabase->get('tour_images', ['tour_id' => 'eq.'.$id]);
            foreach ($existingImages as $img) {
                $this->supabase->delete('tour_images', $img['id']);
            }

            foreach ($request->images as $image) {
                if (isset($image['url'])) {
                    $this->supabase->create('tour_images', [
                        'tour_id' => $id,
                        'url' => $image['url'],
                        'alt' => $image['alt'] ?? '',
                    ]);
                }
            }
        }

        $tour['images'] = $this->supabase->get('tour_images', ['tour_id' => 'eq.'.$id]);
        $tour['dates'] = $this->supabase->get('tour_dates', ['tour_id' => 'eq.'.$id]);

        return response()->json($tour);
    }

    public function destroy($id): JsonResponse
    {
        $id = (int) $id;
        $this->supabase->delete('tours', $id);

        return response()->json(null, 204);
    }

    public function search(Request $request): JsonResponse
    {
        $queryText = $request->validate(['q' => 'required|string'])['q'];
        $searchTerm = '%'.$queryText.'%';

        $params = [
            'is_published' => 'eq.true',
            'select' => 'id,category_id,title,slug,description,duration_days,route_points,highlights,included,excluded,is_published,created_at,updated_at,category:categories(*),images:tour_images(*),dates:tour_dates(*)',
        ];

        $allTours = $this->supabase->get('tours', $params);

        $results = array_filter($allTours, function ($tour) use ($queryText) {
            $tour = $this->decodeTourJson($tour);
            $searchIn = [
                strtolower($tour['title'] ?? ''),
                strtolower($tour['description'] ?? ''),
                strtolower($tour['included'] ?? ''),
                strtolower($tour['excluded'] ?? ''),
            ];

            if (isset($tour['highlights']) && is_array($tour['highlights'])) {
                foreach ($tour['highlights'] as $h) {
                    $searchIn[] = strtolower($h);
                }
            }
            if (isset($tour['route_points']) && is_array($tour['route_points'])) {
                foreach ($tour['route_points'] as $rp) {
                    $searchIn[] = strtolower($rp['name'] ?? '');
                }
            }
            if (isset($tour['category']['name'])) {
                $searchIn[] = strtolower($tour['category']['name']);
            }

            $queryLower = strtolower($queryText);
            foreach ($searchIn as $text) {
                if (str_contains($text, $queryLower)) {
                    return true;
                }
            }

            return false;
        });

        return response()->json(array_values($results));
    }
}
