<?php

namespace Devcbh\LaravelAiProvider\Drivers;

use Devcbh\LaravelAiProvider\Contracts\Driver;
use Illuminate\Support\Facades\Http;
use Exception;

class ClaudeDriver implements Driver
{
    public function __construct(protected array $config) {}

    public function chat(array $messages, array $options = []): string
    {
        $system = '';
        $chatMessages = [];

        foreach ($messages as $message) {
            if ($message->role === 'system') {
                $system = $message->content;
            } else {
                $chatMessages[] = $message->toArray();
            }
        }

        $payload = [
            'model' => $options['model'] ?? $this->config['model'] ?? 'claude-3-sonnet-20240229',
            'system' => $system,
            'messages' => $chatMessages,
            'max_tokens' => $this->config['max_tokens'] ?? 1024,
            'temperature' => $options['temperature'] ?? $this->config['temperature'] ?? 0.7,
        ];

        $headers = [
            'x-api-key' => $this->config['api_key'],
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ];

        if (($options['response_format'] ?? null) === 'json' && isset($options['schema'])) {
            $payload['output_format'] = [
                'type' => 'json_schema',
                'schema' => $options['schema'],
            ];
            $headers['anthropic-beta'] = 'structured-outputs-2025-11-13';
        }

        $response = Http::withHeaders($headers)
            ->post(($this->config['base_url'] ?? 'https://api.anthropic.com/v1') . '/messages', $payload);

        if ($response->failed()) {
            throw new Exception("Claude API Error: " . $response->body());
        }

        return $response->json('content.0.text');
    }
}
