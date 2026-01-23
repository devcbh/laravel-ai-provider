<?php

namespace Devcbh\LaravelAiProvider;

use Devcbh\LaravelAiProvider\Contracts\Driver;
use Devcbh\LaravelAiProvider\DTOs\Message;

class PendingRequest
{
    protected array $messages = [];
    protected array $options = [];

    public function __construct(protected Driver $driver) {}

    public function role(string $message): self
    {
        $this->messages[] = Message::system($message);
        return $this;
    }

    public function lastContext(array $messages): self
    {
        $this->messages = array_merge($this->messages, $messages);
        return $this;
    }

    public function model(string $model): self
    {
        $this->options['model'] = $model;
        return $this;
    }

    public function temperature(float $temperature): self
    {
        $this->options['temperature'] = $temperature;
        return $this;
    }

    public function ask(string $prompt): string
    {
        $this->messages[] = Message::user($prompt);
        return $this->driver->chat($this->messages, $this->options);
    }
}
