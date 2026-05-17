<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TourDate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TourDateController extends Controller
{
    public function index(TourDate $tourDate): JsonResponse
    {
        $dates = $tourDate->with('tour')->get();

        return response()->json($dates);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tour_id' => 'required|exists:tours,id',
            'date' => 'required|date',
            'price' => 'required|integer|min:0',
            'available_slots' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $date = TourDate::create($validated);

        return response()->json($date->load('tour'), 201);
    }

    public function update(Request $request, TourDate $tourDate): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'sometimes|required|date',
            'price' => 'sometimes|required|integer|min:0',
            'available_slots' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $tourDate->update($validated);

        return response()->json($tourDate);
    }

    public function destroy(TourDate $tourDate): JsonResponse
    {
        $tourDate->delete();

        return response()->json(null, 204);
    }
}
