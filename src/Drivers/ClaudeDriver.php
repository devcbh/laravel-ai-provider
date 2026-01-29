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
        $request = $this->prepareRequest($messages, $options);

        $response = Http::withHeaders($request['headers'])
            ->post($request['url'], $request['payload']);

        if ($response->failed()) {
            throw new Exception("Claude API Error: " . $response->body());
        }

        return $this->parseResponse($response);
    }

    public function prepareRequest(array $messages, array $options = []): array
    {
        [$payload, $headers] = $this->preparePayload($messages, $options);

        return [
            'method' => 'POST',
            'url' => ($this->config['base_url'] ?? 'https://api.anthropic.com/v1') . '/messages',
            'payload' => $payload,
            'headers' => $headers,
        ];
    }

    public function parseResponse(mixed $response): string
    {
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return $response->json('content.0.text') ?? '';
        }

        return $response['content'][0]['text'] ?? '';
    }

    public function stream(array $messages, array $options = []): iterable
    {
        [$payload, $headers] = $this->preparePayload($messages, $options);
        $payload['stream'] = true;

        $response = Http::withHeaders($headers)
            ->withOptions(['stream' => true])
            ->post(($this->config['base_url'] ?? 'https://api.anthropic.com/v1') . '/messages', $payload);

        if ($response->failed()) {
            throw new Exception("Claude API Error: " . $response->body());
        }

        $body = $response->toPsrResponse()->getBody();

        while (!$body->eof()) {
            $line = $this->readLine($body);
            if (str_starts_with($line, 'data: ')) {
                $data = substr($line, 6);
                $json = json_decode($data, true);
                if (isset($json['type']) && $json['type'] === 'content_block_delta') {
                    yield $json['delta']['text'] ?? '';
                }
            }
        }
    }

    protected function preparePayload(array $messages, array $options): array
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

        if (isset($options['tools'])) {
            $payload['tools'] = array_map(function ($tool) {
                return [
                    'name' => $tool['function']['name'],
                    'description' => $tool['function']['description'],
                    'input_schema' => $tool['function']['parameters'],
                ];
            }, $options['tools']);
        }

        return [$payload, $headers];
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
