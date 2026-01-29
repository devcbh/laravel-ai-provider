<?php

namespace Devcbh\LaravelAiProvider\Tests;

use Orchestra\Testbench\TestCase;
use Devcbh\LaravelAiProvider\AiServiceProvider;
use Devcbh\LaravelAiProvider\Facades\Ai;
use Illuminate\Support\Facades\Http;

class AsyncTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [AiServiceProvider::class];
    }

    /** @test */
    public function it_can_handle_async_requests()
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [['message' => ['content' => 'OpenAI Response']]]
            ], 200),
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'Gemini Response']]]]]
            ], 200),
        ]);

        $responses = Ai::async()->ask([
            'first' => 'Question 1',
            'second' => 'Question 2',
        ]);

        $this->assertCount(2, $responses);
        // By default, it uses the default driver (openai) if not specified otherwise in loop
        // but currently AsyncPendingRequest::ask uses $this->manager->driver() which uses default.
        $this->assertEquals('OpenAI Response', $responses['first']);
        $this->assertEquals('OpenAI Response', $responses['second']);
    }

    /** @test */
    public function it_can_handle_multiple_drivers_async()
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [['message' => ['content' => 'OpenAI Response']]]
            ], 200),
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'Gemini Response']]]]]
            ], 200),
        ]);

        $async = Ai::async();
        
        $async->add('openai', Ai::driver('openai'));
        $async->add('gemini', Ai::driver('gemini'));

        $responses = $async->execute();

        $this->assertEquals('OpenAI Response', $responses['openai']);
        $this->assertEquals('Gemini Response', $responses['gemini']);
    }
}
