<?php

namespace Devcbh\LaravelAiProvider\Tests;

use Orchestra\Testbench\TestCase;
use Devcbh\LaravelAiProvider\AiServiceProvider;
use Devcbh\LaravelAiProvider\Contracts\Driver;
use Devcbh\LaravelAiProvider\PendingRequest;
use Devcbh\LaravelAiProvider\DTOs\Message;
use Mockery;

class FailoverTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [AiServiceProvider::class];
    }

    /** @test */
    public function it_fails_over_to_next_driver_on_error()
    {
        $firstDriver = Mockery::mock(Driver::class);
        $firstDriver->shouldReceive('chat')
            ->once()
            ->andThrow(new \Exception('First driver failed'));

        $secondDriver = Mockery::mock(Driver::class);
        $secondDriver->shouldReceive('chat')
            ->once()
            ->with(Mockery::type('array'), Mockery::type('array'))
            ->andReturn('Success from second driver');

        $pendingRequest = new PendingRequest($firstDriver);
        $pendingRequest->fallback([$secondDriver]);

        $response = $pendingRequest->ask('Hello');

        $this->assertEquals('Success from second driver', $response);
    }

    /** @test */
    public function it_throws_last_exception_if_all_drivers_fail()
    {
        $firstDriver = Mockery::mock(Driver::class);
        $firstDriver->shouldReceive('chat')
            ->once()
            ->andThrow(new \Exception('First driver failed'));

        $secondDriver = Mockery::mock(Driver::class);
        $secondDriver->shouldReceive('chat')
            ->once()
            ->andThrow(new \Exception('Second driver failed'));

        $pendingRequest = new PendingRequest($firstDriver);
        $pendingRequest->fallback([$secondDriver]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Second driver failed');

        $pendingRequest->ask('Hello');
    }

    /** @test */
    public function it_can_register_tools()
    {
        $driver = Mockery::mock(Driver::class);
        $pendingRequest = new PendingRequest($driver);

        $tools = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_weather',
                    'description' => 'Get the current weather in a given location',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'location' => [
                                'type' => 'string',
                                'description' => 'The city and state, e.g. San Francisco, CA',
                            ],
                        ],
                        'required' => ['location'],
                    ],
                ],
            ]
        ];

        $pendingRequest->withTools($tools);

        $reflection = new \ReflectionClass($pendingRequest);
        $optionsProperty = $reflection->getProperty('options');
        $optionsProperty->setAccessible(true);
        $options = $optionsProperty->getValue($pendingRequest);

        $this->assertArrayHasKey('tools', $options);
        $this->assertEquals('get_weather', $options['tools'][0]['function']['name']);
    }

    /** @test */
    public function it_can_register_tools_via_callable()
    {
        $driver = Mockery::mock(Driver::class);
        $pendingRequest = new PendingRequest($driver);

        $orderService = new class {
            /**
             * Get details of an order by its ID.
             */
            public function getDetails(string $orderId, int $limit = 10)
            {
                return "Order details for $orderId";
            }
        };

        $pendingRequest->withTools([[$orderService, 'getDetails']]);

        $reflection = new \ReflectionClass($pendingRequest);
        $optionsProperty = $reflection->getProperty('options');
        $optionsProperty->setAccessible(true);
        $options = $optionsProperty->getValue($pendingRequest);

        $this->assertArrayHasKey('tools', $options);
        $tool = $options['tools'][0];
        $this->assertEquals('getDetails', $tool['function']['name']);
        $this->assertEquals('Get details of an order by its ID.', $tool['function']['description']);
        $this->assertEquals('string', $tool['function']['parameters']['properties']['orderId']['type']);
        $this->assertEquals('integer', $tool['function']['parameters']['properties']['limit']['type']);
        $this->assertContains('orderId', $tool['function']['parameters']['required']);
        $this->assertNotContains('limit', $tool['function']['parameters']['required']);
    }
}
