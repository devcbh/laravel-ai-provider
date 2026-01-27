<?php

namespace Devcbh\LaravelAiProvider;

use Illuminate\Support\Manager;
use Devcbh\LaravelAiProvider\Drivers\OpenAiDriver;
use Devcbh\LaravelAiProvider\Drivers\GeminiDriver;
use Devcbh\LaravelAiProvider\Drivers\ClaudeDriver;
use Devcbh\LaravelAiProvider\Drivers\MistralDriver;
use Devcbh\LaravelAiProvider\Drivers\OllamaDriver;
use Devcbh\LaravelAiProvider\Contracts\PiiMasker;
use InvalidArgumentException;

class AiManager extends Manager
{
    public function getDefaultDriver()
    {
        return $this->config->get('ai.default');
    }

    public function createOpenaiDriver()
    {
        return new OpenAiDriver($this->config->get('ai.providers.openai'));
    }

    public function createGeminiDriver()
    {
        return new GeminiDriver($this->config->get('ai.providers.gemini'));
    }

    public function createClaudeDriver()
    {
        return new ClaudeDriver($this->config->get('ai.providers.claude'));
    }

    public function createAnthropicDriver()
    {
        return $this->createClaudeDriver();
    }

    public function createMistralDriver()
    {
        return new MistralDriver($this->config->get('ai.providers.mistral'));
    }

    public function createOllamaDriver()
    {
        return new OllamaDriver($this->config->get('ai.providers.ollama'));
    }

    /**
     * Get a driver instance.
     *
     * @param  string|null  $driver
     * @return \Devcbh\LaravelAiProvider\PendingRequest
     */
    public function driver($driver = null)
    {
        $driver = $driver ?: $this->getDefaultDriver();

        $piiMasker = $this->container->bound(PiiMasker::class) 
            ? $this->container->make(PiiMasker::class) 
            : null;

        $instance = parent::driver($driver);

        if ($instance instanceof PendingRequest) {
            return $instance;
        }

        return new PendingRequest($instance, $piiMasker);
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->driver()->$method(...$parameters);
    }
}
