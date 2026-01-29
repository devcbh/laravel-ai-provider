<?php

namespace Devcbh\LaravelAiProvider\Drivers;

use Devcbh\LaravelAiProvider\Contracts\Driver;
use Illuminate\Support\Facades\Http;
use Exception;
use Generator;

class OpenAiDriver implements Driver
{
    public function __construct(protected array $config) {}

    public function chat(array $messages, array $options = []): string
    {
        $request = $this->prepareRequest($messages, $options);

        $response = Http::withToken($request['token'])
            ->post($request['url'], $request['payload']);

        if ($response->failed()) {
            throw new Exception("OpenAI API Error: " . $response->body());
        }

        return $this->parseResponse($response);
    }

    public function prepareRequest(array $messages, array $options = []): array
    {
        return [
            'method' => 'POST',
            'url' => ($this->config['base_url'] ?? 'https://api.openai.com/v1') . '/chat/completions',
            'payload' => $this->preparePayload($messages, $options),
            'token' => $this->config['api_key'],
        ];
    }

    public function parseResponse(mixed $response): string
    {
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return $response->json('choices.0.message.content') ?? '';
        }

        return $response['choices'][0]['message']['content'] ?? '';
    }

    public function stream(array $messages, array $options = []): iterable
    {
        $payload = $this->preparePayload($messages, $options);
        $payload['stream'] = true;

        $response = Http::withToken($this->config['api_key'])
            ->withOptions(['stream' => true])
            ->post(($this->config['base_url'] ?? 'https://api.openai.com/v1') . '/chat/completions', $payload);

        if ($response->failed()) {
            throw new Exception("OpenAI API Error: " . $response->body());
        }

        $body = $response->toPsrResponse()->getBody();

        while (!$body->eof()) {
            $line = $this->readLine($body);
            if (str_starts_with($line, 'data: ')) {
                $data = substr($line, 6);
                if ($data === '[DONE]') {
                    break;
                }
                $json = json_decode($data, true);
                $content = $json['choices'][0]['delta']['content'] ?? null;
                if ($content !== null) {
                    yield $content;
                }
            }
        }
    }

    protected function preparePayload(array $messages, array $options): array
    {
        $payload = [
            'model' => $options['model'] ?? $this->config['model'] ?? 'gpt-3.5-turbo',
            'messages' => array_map(fn($m) => $m->toArray(), $messages),
            'temperature' => $options['temperature'] ?? $this->config['temperature'] ?? 0.7,
        ];

        if (($options['response_format'] ?? null) === 'json') {
            if (isset($options['schema'])) {
                $payload['response_format'] = [
                    'type' => 'json_schema',
                    'json_schema' => [
                        'name' => $options['schema_name'] ?? 'response_schema',
                        'strict' => true,
                        'schema' => $options['schema'],
                    ],
                ];
            } else {
                $payload['response_format'] = ['type' => 'json_object'];
            }
        }

        if (isset($options['tools'])) {
            $payload['tools'] = $options['tools'];
            $payload['tool_choice'] = $options['tool_choice'] ?? 'auto';
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
