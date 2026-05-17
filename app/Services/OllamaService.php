<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaService
{
    private string $baseUrl;

    private string $llmModel;

    private string $embeddingModel;

    public function __construct()
    {
        $this->baseUrl = config('services.ollama.url', 'http://localhost:11434');
        $this->llmModel = config('services.ollama.llm_model', 'llama3.1');
        $this->embeddingModel = config('services.ollama.embedding_model', 'nomic-embed-text');
    }

    public function generateEmbedding(string $text): ?array
    {
        try {
            $response = Http::timeout(30)->post("{$this->baseUrl}/api/embeddings", [
                'model' => $this->embeddingModel,
                'prompt' => $text,
            ]);

            if ($response->successful()) {
                return $response->json('embedding');
            }
        } catch (\Exception $e) {
            Log::error('Embedding generation failed: '.$e->getMessage());
        }

        return null;
    }

    public function chat(string $system, string $user): ?string
    {
        try {
            $response = Http::timeout(120)->post("{$this->baseUrl}/api/chat", [
                'model' => $this->llmModel,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
                'stream' => false,
            ]);

            if ($response->successful()) {
                return $response->json('message.content');
            }
        } catch (\Exception $e) {
            Log::error('LLM chat failed: '.$e->getMessage());
        }

        return null;
    }

    public function generateTour(string $prompt): ?array
    {
        $system = 'Ты - эксперт по созданию описаний туров. Верни ТОЛЬКО валидный JSON без markdown разметки.
Формат:
{
  "title": "Название тура",
  "description": "Описание 2-3 предложения",
  "duration_days": число,
  "highlights": ["пункт 1", "пункт 2", "пункт 3"],
  "included": "Что включено",
  "excluded": "Что не включено"
}';

        $response = $this->chat($system, $prompt);

        if (! $response) {
            return null;
        }

        $json = trim($response);
        $json = preg_replace('/^```json\s*/', '', $json);
        $json = preg_replace('/```$/s', '', $json);

        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Failed to parse LLM response: '.json_last_error_msg().', raw: '.$response);

            return null;
        }

        return $data;
    }
}
