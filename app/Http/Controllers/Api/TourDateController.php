<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SupabaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TourDateController extends Controller
{
    private SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function index(): JsonResponse
    {
        $dates = $this->supabase->get('tour_dates', ['order' => 'date']);

        return response()->json($dates);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tour_id' => 'required|integer',
            'date' => 'required|date',
            'price' => 'required|integer|min:0',
            'available_slots' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data = [
            'tour_id' => $validated['tour_id'],
            'date' => $validated['date'],
            'price' => $validated['price'],
            'available_slots' => $validated['available_slots'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ];

        $date = $this->supabase->create('tour_dates', $data);

        if (!$date) {
            return response()->json(['error' => 'Failed to create date'], 500);
        }

        return response()->json($date, 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $id = (int) $id;
        $validated = $request->validate([
            'date' => 'sometimes|required|date',
            'price' => 'sometimes|required|integer|min:0',
            'available_slots' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data = [];
        foreach ($validated as $key => $value) {
            $data[$key] = $value;
        }

        $date = $this->supabase->update('tour_dates', $id, $data);

        if (!$date) {
            return response()->json(['error' => 'Failed to update date'], 500);
        }

        return response()->json($date);
    }

    public function destroy($id): JsonResponse
    {
        $id = (int) $id;
        $this->supabase->delete('tour_dates', $id);

        return response()->json(null, 204);
    }
}