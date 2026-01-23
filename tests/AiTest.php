<?php

namespace Devcbh\LaravelAiProvider\Tests;

use Orchestra\Testbench\TestCase;
use Devcbh\LaravelAiProvider\AiServiceProvider;
use Devcbh\LaravelAiProvider\Facades\Ai;
use Devcbh\LaravelAiProvider\AiManager;

class AiTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [AiServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Ai' => Ai::class,
        ];
    }

    /** @test */
    public function it_can_be_resolved_from_the_container()
    {
        $this->assertInstanceOf(AiManager::class, $this->app->make('ai'));
    }

    /** @test */
    public function it_can_use_facade()
    {
        $this->assertInstanceOf(AiManager::class, Ai::getFacadeRoot());
    }
}
