<?php

namespace App\Services;

use App\Models\Tour;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    private string $baseUrl;

    private string $embeddingModel;

    public function __construct()
    {
        $this->baseUrl = config('services.ollama.url', 'http://localhost:11434');
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

    public function generateTourEmbedding(Tour $tour): void
    {
        $text = $this->buildTourText($tour);
        $embedding = $this->generateEmbedding($text);

        if ($embedding) {
            $tour->update(['embedding' => $embedding]);
        }
    }

    public function semanticSearch(string $query, int $limit = 12): Collection
    {
        $queryEmbedding = $this->generateEmbedding($query);

        if (! $queryEmbedding) {
            return collect();
        }

        $queryVector = '['.implode(',', $queryEmbedding).']';

        $results = Tour::with(['category', 'images', 'dates'])
            ->where('is_published', true)
            ->whereNotNull('embedding')
            ->limit(100)
            ->get();

        $results = $results->map(function ($tour) use ($queryEmbedding) {
            $tourEmbedding = is_string($tour->embedding) ? json_decode($tour->embedding, true) : $tour->embedding;
            if ($tourEmbedding) {
                $tour->similarity = $this->cosineSimilarity($queryEmbedding, $tourEmbedding);
            } else {
                $tour->similarity = 0;
            }

            return $tour;
        })->sortByDesc('similarity')->take($limit)->values();

        return $results;
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0;
        $normA = 0;
        $normB = 0;

        for ($i = 0; $i < count($a); $i++) {
            if (isset($b[$i])) {
                $dot += $a[$i] * $b[$i];
                $normA += $a[$i] * $a[$i];
                $normB += $b[$i] * $b[$i];
            }
        }

        if ($normA == 0 || $normB == 0) {
            return 0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    private function buildTourText(Tour $tour): string
    {
        $parts = [
            $tour->title,
            $tour->description,
            $tour->category->name ?? '',
            "{$tour->duration_days} дней",
        ];

        if ($tour->highlights) {
            $parts[] = implode('. ', $tour->highlights);
        }

        if ($tour->included) {
            $parts[] = 'Включено: '.$tour->included;
        }

        if ($tour->excluded) {
            $parts[] = 'Не включено: '.$tour->excluded;
        }

        return implode(' ', $parts);
    }
}
