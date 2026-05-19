<?php

namespace App\Console\Commands;

use App\Services\EmbeddingService;
use App\Services\SupabaseService;
use Illuminate\Console\Command;

class GenerateEmbeddings extends Command
{
    protected $signature = 'embeddings:generate';
    protected $description = 'Generate embeddings for all tours';

    public function handle()
    {
        $svc = app(EmbeddingService::class);
        $supa = app(SupabaseService::class);

        $tours = $supa->get('tours', ['select' => 'id,title,description,duration_days,included,excluded,highlights,category_id']);

        $this->info("Найдено туров: " . count($tours));

        foreach ($tours as $tour) {
            $text = implode(' ', array_filter([
                $tour['title'] ?? '',
                $tour['description'] ?? '',
                $tour['included'] ?? '',
                $tour['excluded'] ?? '',
                ($tour['duration_days'] ?? '') . ' дней',
            ]));

            if (is_string($tour['highlights'] ?? null)) {
                $tour['highlights'] = json_decode($tour['highlights'], true) ?? [];
            }
            if (is_array($tour['highlights'] ?? null)) {
                $text .= ' ' . implode(' ', $tour['highlights']);
            }

            $embedding = $svc->generateEmbedding($text);

            if ($embedding) {
                $supa->update('tours', $tour['id'], [
                    'embedding' => json_encode($embedding),
                ]);
                $this->info("Тур {$tour['id']} ({$tour['title']}): OK");
            } else {
                $this->error("Тур {$tour['id']} ({$tour['title']}): FAIL");
            }
        }

        $this->info("Готово!");
    }
}
