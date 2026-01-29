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
        $request = $this->prepareRequest($messages, $options);

        $response = Http::post($request['url'], $request['payload']);

        if ($response->failed()) {
            throw new Exception("Gemini API Error: " . $response->body());
        }

        return $this->parseResponse($response);
    }

    public function prepareRequest(array $messages, array $options = []): array
    {
        $payload = $this->preparePayload($messages, $options);
        $model = $options['model'] ?? $this->config['model'] ?? 'gemini-1.5-flash';
        $apiKey = $this->config['api_key'];
        $baseUrl = $this->config['base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta';

        return [
            'method' => 'POST',
            'url' => "{$baseUrl}/models/{$model}:generateContent?key={$apiKey}",
            'payload' => $payload,
        ];
    }

    public function parseResponse(mixed $response): string
    {
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return $response->json('candidates.0.content.parts.0.text') ?? '';
        }

        return $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    public function stream(array $messages, array $options = []): iterable
    {
        $payload = $this->preparePayload($messages, $options);
        $model = $options['model'] ?? $this->config['model'] ?? 'gemini-1.5-flash';
        $apiKey = $this->config['api_key'];
        $baseUrl = $this->config['base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta';

        $response = Http::withOptions(['stream' => true])
            ->post("{$baseUrl}/models/{$model}:streamGenerateContent?key={$apiKey}", $payload);

        if ($response->failed()) {
            throw new Exception("Gemini API Error: " . $response->body());
        }

        $body = $response->toPsrResponse()->getBody();

        while (!$body->eof()) {
            $line = $this->readLine($body);
            if (empty($line)) continue;
            
            $json = json_decode($line, true);
            if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
                yield $json['candidates'][0]['content']['parts'][0]['text'];
            }
        }
    }

    protected function preparePayload(array $messages, array $options): array
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

        if (isset($options['tools'])) {
            $payload['tools'] = [
                ['function_declarations' => array_map(function ($tool) {
                    return [
                        'name' => $tool['function']['name'],
                        'description' => $tool['function']['description'],
                        'parameters' => $tool['function']['parameters'],
                    ];
                }, $options['tools'])]
            ];
        }

        return $payload;
    }

    protected function readLine($stream): string
    {
        $line = '';
        while (!$stream->eof()) {
            $char = $stream->read(1);
            if ($char === "\n") {
                break;
            }
            $line .= $char;
        }
        return trim($line);
    }
}
