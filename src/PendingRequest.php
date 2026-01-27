<?php

namespace Devcbh\LaravelAiProvider;

use Devcbh\LaravelAiProvider\Contracts\Driver;
use Devcbh\LaravelAiProvider\Contracts\Template;
use Devcbh\LaravelAiProvider\Contracts\PiiMasker;
use Devcbh\LaravelAiProvider\DTOs\Message;

class PendingRequest
{
    protected array $messages = [];
    protected array $options = [];
    protected bool $shouldMaskPii = false;

    public function __construct(
        protected Driver $driver,
        protected ?PiiMasker $piiMasker = null
    ) {
        $this->shouldMaskPii = config('ai.pii_masking.enabled', false);
    }

    public function withPiiMasking(bool $enabled = true): self
    {
        $this->shouldMaskPii = $enabled;
        return $this;
    }

    public function role(string $message): self
    {
        $this->messages[] = Message::system($message);
        return $this;
    }

    public function template(Template $template, array $data = []): self
    {
        $this->role($template->systemPrompt());
        $this->messages[] = Message::user($template->userPrompt($data));

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

    public function schema(array $schema, string $name = 'response_schema'): self
    {
        $this->options['schema'] = $schema;
        $this->options['schema_name'] = $name;
        return $this;
    }

    public function driver(Driver|PendingRequest $driver): self
    {
        if ($driver instanceof PendingRequest) {
            $this->driver = $driver->getDriver();
        } else {
            $this->driver = $driver;
        }

        return $this;
    }

    public function getDriver(): Driver
    {
        return $this->driver;
    }

    public function ask(string $prompt): string
    {
        $this->messages[] = Message::user($prompt);

        return $this->executeChat();
    }

    public function asJson(string $prompt): array
    {
        $this->options['response_format'] = 'json';
        $this->messages[] = Message::user($prompt);

        $response = $this->executeChat();

        return json_decode($response, true) ?? [];
    }

    protected function executeChat(): string
    {
        if ($this->shouldMaskPii && $this->piiMasker) {
            foreach ($this->messages as $message) {
                $message->content = $this->piiMasker->mask($message->content);
            }
        }

        $response = $this->driver->chat($this->messages, $this->options);

        if ($this->shouldMaskPii && $this->piiMasker) {
            $response = $this->piiMasker->unmask($response);
        }

        return $response;
    }
}
