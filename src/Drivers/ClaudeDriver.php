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

        $response = Http::withHeaders([
            'x-api-key' => $this->config['api_key'],
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post(($this->config['base_url'] ?? 'https://api.anthropic.com/v1') . '/messages', [
            'model' => $options['model'] ?? $this->config['model'] ?? 'claude-3-sonnet-20240229',
            'system' => $system,
            'messages' => $chatMessages,
            'max_tokens' => $this->config['max_tokens'] ?? 1024,
            'temperature' => $options['temperature'] ?? $this->config['temperature'] ?? 0.7,
        ]);

        if ($response->failed()) {
            throw new Exception("Claude API Error: " . $response->body());
        }

        return $response->json('content.0.text');
    }
}
