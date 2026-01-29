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
    protected bool $shouldScrubPii = false;
    protected array $fallbacks = [];
    protected array $tools = [];
    protected ?string $driverName = null;

    public function __construct(
        protected Driver $driver,
        protected ?PiiMasker $piiMasker = null
    ) {
        $this->shouldMaskPii = config('ai.pii_masking.enabled', true);
        $this->shouldScrubPii = config('ai.pii_masking.strict', false);
    }

    public function withPiiMasking(bool $enabled = true): self
    {
        $this->shouldMaskPii = $enabled;
        return $this;
    }

    public function scrubPii(bool $enabled = true): self
    {
        $this->shouldScrubPii = $enabled;
        if ($enabled) {
            $this->shouldMaskPii = true;
        }
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

    public function fallback(array $drivers): self
    {
        $this->fallbacks = $drivers;
        return $this;
    }

    public function withTools(array $tools): self
    {
        foreach ($tools as $tool) {
            if (is_callable($tool)) {
                $tool = $this->resolveToolFromCallable($tool);
            }

            $this->tools[$tool['function']['name']] = $tool;
        }

        $this->options['tools'] = array_values($this->tools);
        return $this;
    }

    protected function resolveToolFromCallable(callable $callable): array
    {
        if (is_array($callable)) {
            $reflection = new \ReflectionMethod($callable[0], $callable[1]);
        } else {
            $reflection = new \ReflectionFunction($callable);
        }

        $name = $reflection->getName();
        $description = $this->parseDescription($reflection->getDocComment() ?: '');

        $parameters = [
            'type' => 'object',
            'properties' => [],
            'required' => [],
        ];

        foreach ($reflection->getParameters() as $parameter) {
            $paramName = $parameter->getName();
            $type = $parameter->getType();
            $paramType = 'string';

            if ($type instanceof \ReflectionNamedType) {
                $paramType = match ($type->getName()) {
                    'int' => 'integer',
                    'float' => 'number',
                    'bool' => 'boolean',
                    'array' => 'array',
                    default => 'string',
                };
            }

            $parameters['properties'][$paramName] = [
                'type' => $paramType,
            ];

            if (!$parameter->isOptional()) {
                $parameters['required'][] = $paramName;
            }
        }

        return [
            'type' => 'function',
            'function' => [
                'name' => $name,
                'description' => $description,
                'parameters' => $parameters,
            ],
        ];
    }

    protected function parseDescription(string $docComment): string
    {
        $docComment = str_replace(['/**', '*/', '*'], '', $docComment);
        $lines = explode("\n", $docComment);
        $description = '';

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '@')) {
                continue;
            }
            $description .= $line . ' ';
        }

        return trim($description) ?: 'No description provided.';
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

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function addMessage(string $role, string $content): self
    {
        $this->messages[] = new Message($role, $content);
        return $this;
    }

    public function setDriverName(string $name): self
    {
        $this->driverName = $name;
        return $this;
    }

    public function getDriverName(): ?string
    {
        return $this->driverName ?? null;
    }

    public function ask(string $prompt): string
    {
        $this->messages[] = Message::user($prompt);

        return $this->executeChat();
    }

    public function stream(string $prompt): iterable
    {
        $this->messages[] = Message::user($prompt);

        return $this->executeStream();
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
        $this->applyPiiMasking();

        $drivers = array_merge([$this->driver], $this->getFallbackDrivers());
        $lastException = null;

        foreach ($drivers as $driver) {
            try {
                return $driver->chat($this->messages, $this->options);
            } catch (\Exception $e) {
                $lastException = $e;
                continue;
            }
        }

        throw $lastException ?: new \Exception("All drivers failed.");
    }

    protected function executeStream(): iterable
    {
        $this->applyPiiMasking();

        $drivers = array_merge([$this->driver], $this->getFallbackDrivers());
        $lastException = null;

        foreach ($drivers as $driver) {
            try {
                return $driver->stream($this->messages, $this->options);
            } catch (\Exception $e) {
                $lastException = $e;
                continue;
            }
        }

        throw $lastException ?: new \Exception("All drivers failed.");
    }

    protected function applyPiiMasking(): void
    {
        if ($this->shouldMaskPii && $this->piiMasker) {
            foreach ($this->messages as $message) {
                if ($this->shouldScrubPii) {
                    $message->content = $this->piiMasker->scrub($message->content);
                } else {
                    $message->content = $this->piiMasker->mask($message->content);
                }
            }
        }
    }

    protected function getFallbackDrivers(): array
    {
        $drivers = [];
        $manager = app(AiManager::class);

        foreach ($this->fallbacks as $fallback) {
            if ($fallback instanceof Driver) {
                $drivers[] = $fallback;
            } elseif (is_string($fallback)) {
                $drivers[] = $manager->driver($fallback)->getDriver();
            }
        }

        return $drivers;
    }
}
