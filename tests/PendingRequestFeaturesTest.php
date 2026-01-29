<?php

namespace Devcbh\LaravelAiProvider\Tests;

use Orchestra\Testbench\TestCase;
use Devcbh\LaravelAiProvider\PendingRequest;
use Devcbh\LaravelAiProvider\Contracts\Driver;
use Devcbh\LaravelAiProvider\DTOs\Message;
use Mockery;

class PendingRequestFeaturesTest extends TestCase
{
    /** @test */
    public function it_can_set_model_and_temperature()
    {
        $driver = Mockery::mock(Driver::class);
        $request = new PendingRequest($driver);

        $request->model('gpt-4')->temperature(0.9);

        $this->assertEquals('gpt-4', $request->getOptions()['model']);
        $this->assertEquals(0.9, $request->getOptions()['temperature']);
    }

    /** @test */
    public function it_can_add_messages()
    {
        $driver = Mockery::mock(Driver::class);
        $request = new PendingRequest($driver);

        $request->role('You are a helpful assistant');
        $request->addMessage('user', 'Hello');

        $messages = $request->getMessages();
        $this->assertCount(2, $messages);
        $this->assertEquals('system', $messages[0]->role);
        $this->assertEquals('You are a helpful assistant', $messages[0]->content);
        $this->assertEquals('user', $messages[1]->role);
        $this->assertEquals('Hello', $messages[1]->content);
    }

    /** @test */
    public function it_can_set_json_schema()
    {
        $driver = Mockery::mock(Driver::class);
        $request = new PendingRequest($driver);

        $schema = ['type' => 'object', 'properties' => ['name' => ['type' => 'string']]];
        $request->schema($schema, 'user_schema');

        $options = $request->getOptions();
        $this->assertEquals($schema, $options['schema']);
        $this->assertEquals('user_schema', $options['schema_name']);
    }

    /** @test */
    public function it_can_add_tools_from_callables()
    {
        $driver = Mockery::mock(Driver::class);
        $request = new PendingRequest($driver);

        $request->withTools([
            'get_weather' => function (string $location, int $days = 1) {
                /** Get weather for a location */
                return "Sunny";
            }
        ]);

        $options = $request->getOptions();
        $this->assertCount(1, $options['tools']);
        $tool = $options['tools'][0];
        $this->assertEquals('function', $tool['type']);
        $this->assertEquals('get_weather', $tool['function']['name']);
        $this->assertEquals('No description provided.', $tool['function']['description']);
        $this->assertEquals('string', $tool['function']['parameters']['properties']['location']['type']);
        $this->assertEquals('integer', $tool['function']['parameters']['properties']['days']['type']);
        $this->assertContains('location', $tool['function']['parameters']['required']);
        $this->assertNotContains('days', $tool['function']['parameters']['required']);
    }

    /** @test */
    public function as_json_sets_response_format()
    {
        $driver = Mockery::mock(Driver::class);
        $request = new PendingRequest($driver);

        $driver->shouldReceive('chat')
            ->once()
            ->andReturn('{"name": "John"}');

        $result = $request->asJson('Give me a user');

        $this->assertEquals(['name' => 'John'], $result);
        $this->assertEquals('json', $request->getOptions()['response_format']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
