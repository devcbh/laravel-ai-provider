<?php

namespace Devcbh\LaravelAiProvider\Drivers;

use Devcbh\LaravelAiProvider\Contracts\Driver;
use Illuminate\Support\Facades\Http;
use Exception;

class OllamaDriver implements Driver
{
    public function __construct(protected array $config) {}

    public function chat(array $messages, array $options = []): string
    {
        $response = Http::post(($this->config['base_url'] ?? 'http://localhost:11434') . '/api/chat', [
            'model' => $options['model'] ?? $this->config['model'] ?? 'llama3',
            'messages' => array_map(fn($m) => $m->toArray(), $messages),
            'options' => [
                'temperature' => $options['temperature'] ?? $this->config['temperature'] ?? 0.7,
            ],
            'stream' => false,
        ]);

        if ($response->failed()) {
            throw new Exception("Ollama API Error: " . $response->body());
        }

        return $response->json('message.content');
    }
}
