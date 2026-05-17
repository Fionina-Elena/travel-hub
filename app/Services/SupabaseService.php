<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseService
{
    private string $url;
    private string $key;
    private string $restUrl;

    public function __construct()
    {
        $this->url = config('services.supabase.url');
        $this->key = config('services.supabase.key');
        $this->restUrl = $this->url . '/rest/v1';
    }

    public function getHeaders(): array
    {
        return [
            'apikey' => $this->key,
            'Authorization' => 'Bearer ' . $this->key,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ];
    }

    public function get(string $table, array $params = []): array
    {
        $query = http_build_query($params);
        $url = $this->restUrl . '/' . $table . ($query ? '?' . $query : '');
        
        $response = Http::withHeaders($this->getHeaders())->get($url);
        
        if ($response->failed()) {
            Log::error('Supabase GET error: ' . $response->body());
            return [];
        }
        
        return $response->json() ?? [];
    }

    public function find(string $table, int $id): ?array
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->restUrl . '/' . $table . '?id=eq.' . $id . '&limit=1');
        
        if ($response->failed()) {
            Log::error('Supabase find error: ' . $response->body());
            return null;
        }
        
        $data = $response->json();
        return $data[0] ?? null;
    }

    public function create(string $table, array $data): ?array
    {
        $response = Http::withHeaders($this->getHeaders())
            ->post($this->restUrl . '/' . $table, $data);
        
        if ($response->failed()) {
            Log::error('Supabase create error: ' . $response->body());
            return null;
        }
        
        return $response->json()[0] ?? null;
    }

    public function update(string $table, int $id, array $data): ?array
    {
        $headers = $this->getHeaders();
        $headers['Prefer'] = 'return=minimal';
        
        $response = Http::withHeaders($headers)
            ->patch($this->restUrl . '/' . $table . '?id=eq.' . $id, $data);
        
        if ($response->failed()) {
            Log::error('Supabase update error: ' . $response->body());
            return null;
        }
        
        $result = $this->find($table, $id);
        return $result;
    }

    public function delete(string $table, int $id): bool
    {
        $response = Http::withHeaders($this->getHeaders())
            ->delete($this->restUrl . '/' . $table . '?id=eq.' . $id);
        
        return !$response->failed();
    }

    public function rpc(string $function, array $params = []): array
    {
        $response = Http::withHeaders($this->getHeaders())
            ->post($this->url . '/rest/v1/rpc/' . $function, $params);
        
        if ($response->failed()) {
            Log::error('Supabase RPC error: ' . $response->body());
            return [];
        }
        
        return $response->json() ?? [];
    }
}