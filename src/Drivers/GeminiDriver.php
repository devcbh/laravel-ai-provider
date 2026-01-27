<?php

namespace Devcbh\LaravelAiProvider\Drivers;

use Devcbh\LaravelAiProvider\Contracts\Driver;
use Illuminate\Support\Facades\Http;
use Exception;

class GeminiDriver implements Driver
{
    public function __construct(protected array $config) {}

    public function chat(array $messages, array $options = []): string
    {
        $systemInstructions = '';
        $contents = [];

        foreach ($messages as $message) {
            if ($message->role === 'system') {
                $systemInstructions = $message->content;
                continue;
            }

            $contents[] = [
                'role' => $message->role === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $message->content]]
            ];
        }

        $model = $options['model'] ?? $this->config['model'] ?? 'gemini-1.5-flash';
        $apiKey = $this->config['api_key'];
        $baseUrl = $this->config['base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta';

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? $this->config['temperature'] ?? 0.7,
            ]
        ];

        if (($options['response_format'] ?? null) === 'json') {
            $payload['generationConfig']['response_mime_type'] = 'application/json';

            if (isset($options['schema'])) {
                $payload['generationConfig']['response_schema'] = $options['schema'];
            }
        }

        if ($systemInstructions) {
            $payload['system_instruction'] = [
                'parts' => [['text' => $systemInstructions]]
            ];
        }

        $response = Http::post("{$baseUrl}/models/{$model}:generateContent?key={$apiKey}", $payload);

        if ($response->failed()) {
            throw new Exception("Gemini API Error: " . $response->body());
        }

        return $response->json('candidates.0.content.parts.0.text');
    }
}
