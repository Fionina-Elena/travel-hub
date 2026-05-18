<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OllamaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AIGeneratorController extends Controller
{
    public function __construct(private OllamaService $ollama) {}

    public function generateTour(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|min:10|max:1000',
            'category_id' => 'required|integer|min:1',
        ]);

        $result = $this->ollama->generateTour($validated['prompt']);

        if (! $result) {
            return response()->json([
                'error' => 'LLM недоступен. Убедитесь, что Ollama запущен.',
            ], 503);
        }

        $result['category_id'] = $validated['category_id'];
        $result['is_published'] = false;

        return response()->json($result);
    }

    public function checkStatus(): JsonResponse
    {
        return response()->json($this->ollama->checkConnection());
    }
}
