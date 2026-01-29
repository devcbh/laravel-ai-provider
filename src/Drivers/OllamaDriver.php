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
        $request = $this->prepareRequest($messages, $options);

        $response = Http::post($request['url'], $request['payload']);

        if ($response->failed()) {
            throw new Exception("Ollama API Error: " . $response->body());
        }

        return $this->parseResponse($response);
    }

    public function prepareRequest(array $messages, array $options = []): array
    {
        $payload = $this->preparePayload($messages, $options);
        $payload['stream'] = false;

        return [
            'method' => 'POST',
            'url' => ($this->config['base_url'] ?? 'http://localhost:11434') . '/api/chat',
            'payload' => $payload,
        ];
    }

    public function parseResponse(mixed $response): string
    {
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return $response->json('message.content') ?? '';
        }

        return $response['message']['content'] ?? '';
    }

    public function stream(array $messages, array $options = []): iterable
    {
        $payload = $this->preparePayload($messages, $options);
        $payload['stream'] = true;

        $response = Http::withOptions(['stream' => true])
            ->post(($this->config['base_url'] ?? 'http://localhost:11434') . '/api/chat', $payload);

        if ($response->failed()) {
            throw new Exception("Ollama API Error: " . $response->body());
        }

        $body = $response->toPsrResponse()->getBody();

        while (!$body->eof()) {
            $line = $this->readLine($body);
            if (empty($line)) continue;
            
            $json = json_decode($line, true);
            if (isset($json['message']['content'])) {
                yield $json['message']['content'];
            }
            if (isset($json['done']) && $json['done']) {
                break;
            }
        }
    }

    protected function preparePayload(array $messages, array $options): array
    {
        $payload = [
            'model' => $options['model'] ?? $this->config['model'] ?? 'llama3',
            'messages' => array_map(fn($m) => $m->toArray(), $messages),
            'options' => [
                'temperature' => $options['temperature'] ?? $this->config['temperature'] ?? 0.7,
            ],
        ];

        if (($options['response_format'] ?? null) === 'json') {
            $payload['format'] = $options['schema'] ?? 'json';
        }

        if (isset($options['tools'])) {
            $payload['tools'] = array_map(function ($tool) {
                return [
                    'type' => 'function',
                    'function' => [
                        'name' => $tool['function']['name'],
                        'description' => $tool['function']['description'],
                        'parameters' => $tool['function']['parameters'],
                    ],
                ];
            }, $options['tools']);
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
