<?php

namespace Devcbh\LaravelAiProvider\Tests;

use Orchestra\Testbench\TestCase;
use Devcbh\LaravelAiProvider\AiManager;
use Devcbh\LaravelAiProvider\AiServiceProvider;
use Devcbh\LaravelAiProvider\Drivers\OpenAiDriver;
use Devcbh\LaravelAiProvider\Drivers\ClaudeDriver;
use Devcbh\LaravelAiProvider\Drivers\GeminiDriver;
use Devcbh\LaravelAiProvider\Drivers\MistralDriver;
use Devcbh\LaravelAiProvider\Drivers\OllamaDriver;
use Devcbh\LaravelAiProvider\PendingRequest;

class AiManagerTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [AiServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('ai.default', 'openai');
        $app['config']->set('ai.providers.openai', ['api_key' => 'sk-test', 'model' => 'gpt-3.5-turbo']);
        $app['config']->set('ai.providers.claude', ['api_key' => 'claude-test']);
        $app['config']->set('ai.providers.gemini', ['api_key' => 'gemini-test']);
        $app['config']->set('ai.providers.mistral', ['api_key' => 'mistral-test']);
        $app['config']->set('ai.providers.ollama', ['base_url' => 'http://localhost:11434']);
    }

    /** @test */
    public function it_can_create_openai_driver()
    {
        $manager = new AiManager($this->app);
        $driver = $manager->driver('openai');
        $this->assertInstanceOf(PendingRequest::class, $driver);
        $this->assertInstanceOf(OpenAiDriver::class, $driver->getDriver());
    }

    /** @test */
    public function it_can_create_claude_driver()
    {
        $manager = new AiManager($this->app);
        $driver = $manager->driver('claude');
        $this->assertInstanceOf(ClaudeDriver::class, $driver->getDriver());
    }

    /** @test */
    public function it_can_create_gemini_driver()
    {
        $manager = new AiManager($this->app);
        $driver = $manager->driver('gemini');
        $this->assertInstanceOf(GeminiDriver::class, $driver->getDriver());
    }

    /** @test */
    public function it_can_create_mistral_driver()
    {
        $manager = new AiManager($this->app);
        $driver = $manager->driver('mistral');
        $this->assertInstanceOf(MistralDriver::class, $driver->getDriver());
    }

    /** @test */
    public function it_can_create_ollama_driver()
    {
        $manager = new AiManager($this->app);
        $driver = $manager->driver('ollama');
        $this->assertInstanceOf(OllamaDriver::class, $driver->getDriver());
    }

    /** @test */
    public function it_can_use_default_driver()
    {
        $manager = new AiManager($this->app);
        $driver = $manager->driver();
        $this->assertInstanceOf(OpenAiDriver::class, $driver->getDriver());
    }
}
