<?php

namespace Devcbh\LaravelAiProvider\Drivers;

use Devcbh\LaravelAiProvider\Contracts\Driver;
use Illuminate\Support\Facades\Http;
use Exception;

class MistralDriver implements Driver
{
    public function __construct(protected array $config) {}

    public function chat(array $messages, array $options = []): string
    {
        $payload = [
            'model' => $options['model'] ?? $this->config['model'] ?? 'mistral-tiny',
            'messages' => array_map(fn($m) => $m->toArray(), $messages),
            'temperature' => $options['temperature'] ?? $this->config['temperature'] ?? 0.7,
        ];

        if (($options['response_format'] ?? null) === 'json') {
            if (isset($options['schema'])) {
                $payload['response_format'] = [
                    'type' => 'json_schema',
                    'json_schema' => [
                        'schema' => $options['schema'],
                        'name' => $options['schema_name'] ?? 'response_schema',
                        'strict' => true,
                    ],
                ];
            } else {
                $payload['response_format'] = ['type' => 'json_object'];
            }
        }

        $response = Http::withToken($this->config['api_key'])
            ->post(($this->config['base_url'] ?? 'https://api.mistral.ai/v1') . '/chat/completions', $payload);

        if ($response->failed()) {
            throw new Exception("Mistral API Error: " . $response->body());
        }

        return $response->json('choices.0.message.content');
    }
}
