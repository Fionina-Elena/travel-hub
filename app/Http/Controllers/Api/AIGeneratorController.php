<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OllamaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AIGeneratorController extends Controller
{
    public function __construct(private OllamaService $ollama) {}

    public function generateTour(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|min:10|max:1000',
            'category_id' => 'required|exists:categories,id',
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
        $status = [
            'ollama' => false,
            'embedding_model' => false,
            'llm_model' => false,
        ];

        try {
            $response = Http::timeout(5)
                ->get('http://localhost:11434/api/tags');

            if ($response->successful()) {
                $status['ollama'] = true;
                $models = collect($response->json('models'))->pluck('name')->toArray();
                $status['embedding_model'] = in_array('nomic-embed-text:latest', $models);
                $status['llm_model'] = in_array('llama3.1:latest', $models) || in_array('llama3:latest', $models) || in_array('llama3.2:3b', $models);
            }
        } catch (\Exception $e) {
            // Ollama not running
        }

        return response()->json($status);
    }
}
