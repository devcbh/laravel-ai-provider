<?php

namespace Devcbh\LaravelAiProvider\Tests;

use Orchestra\Testbench\TestCase;
use Devcbh\LaravelAiProvider\Drivers\OpenAiDriver;
use Devcbh\LaravelAiProvider\Drivers\ClaudeDriver;
use Devcbh\LaravelAiProvider\Drivers\GeminiDriver;
use Devcbh\LaravelAiProvider\Drivers\MistralDriver;
use Devcbh\LaravelAiProvider\Drivers\OllamaDriver;
use Devcbh\LaravelAiProvider\DTOs\Message;
use Illuminate\Support\Facades\Http;

class DriversTest extends TestCase
{
    /** @test */
    public function openai_driver_chat()
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'OpenAI Response']]
                ]
            ], 200)
        ]);

        $driver = new OpenAiDriver(['api_key' => 'test-key']);
        $response = $driver->chat([Message::user('Hello')]);

        $this->assertEquals('OpenAI Response', $response);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.openai.com/v1/chat/completions' &&
                   $request->header('Authorization')[0] === 'Bearer test-key' &&
                   $request['messages'][0]['content'] === 'Hello';
        });
    }

    /** @test */
    public function claude_driver_chat()
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    ['text' => 'Claude Response']
                ]
            ], 200)
        ]);

        $driver = new ClaudeDriver(['api_key' => 'test-key']);
        $response = $driver->chat([Message::user('Hello')]);

        $this->assertEquals('Claude Response', $response);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.anthropic.com/v1/messages' &&
                   $request->header('x-api-key')[0] === 'test-key' &&
                   $request['messages'][0]['content'] === 'Hello';
        });
    }

    /** @test */
    public function gemini_driver_chat()
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'Gemini Response']]]]
                ]
            ], 200)
        ]);

        $driver = new GeminiDriver(['api_key' => 'test-key']);
        $response = $driver->chat([Message::user('Hello')]);

        $this->assertEquals('Gemini Response', $response);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'generativelanguage.googleapis.com') &&
                   str_contains($request->url(), 'key=test-key') &&
                   $request['contents'][0]['parts'][0]['text'] === 'Hello';
        });
    }

    /** @test */
    public function mistral_driver_chat()
    {
        Http::fake([
            'api.mistral.ai/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Mistral Response']]
                ]
            ], 200)
        ]);

        $driver = new MistralDriver(['api_key' => 'test-key']);
        $response = $driver->chat([Message::user('Hello')]);

        $this->assertEquals('Mistral Response', $response);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.mistral.ai/v1/chat/completions' &&
                   $request->header('Authorization')[0] === 'Bearer test-key' &&
                   $request['messages'][0]['content'] === 'Hello';
        });
    }

    /** @test */
    public function ollama_driver_chat()
    {
        Http::fake([
            'localhost:11434/*' => Http::response([
                'message' => ['content' => 'Ollama Response']
            ], 200)
        ]);

        $driver = new OllamaDriver([]);
        $response = $driver->chat([Message::user('Hello')]);

        $this->assertEquals('Ollama Response', $response);
        Http::assertSent(function ($request) {
            return $request->url() === 'http://localhost:11434/api/chat' &&
                   $request['messages'][0]['content'] === 'Hello' &&
                   $request['stream'] === false;
        });
    }
}
