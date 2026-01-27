<?php

namespace Devcbh\LaravelAiProvider\Tests;

use Orchestra\Testbench\TestCase;
use Devcbh\LaravelAiProvider\AiServiceProvider;
use Devcbh\LaravelAiProvider\Contracts\Driver;
use Devcbh\LaravelAiProvider\PendingRequest;
use Devcbh\LaravelAiProvider\PiiMasker\DefaultPiiMasker;
use Mockery;

class PendingRequestPiiTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [AiServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:3095867304958673049586730495867304958673049=');
        $app['config']->set('app.cipher', 'AES-256-CBC');
    }

    /** @test */
    public function it_masks_pii_when_enabled()
    {
        $driver = Mockery::mock(Driver::class);
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        
        $request = new PendingRequest($driver, $masker);
        $request->withPiiMasking(true);

        $driver->shouldReceive('chat')
            ->once()
            ->withArgs(function ($messages, $options) {
                return str_contains($messages[0]->content, "[MASKED_EMAIL_");
            })
            ->andReturn("OK");

        $response = $request->ask("Contact me at john@example.com");
        $this->assertEquals("OK", $response);
    }

    /** @test */
    public function it_does_not_mask_pii_when_disabled()
    {
        $driver = Mockery::mock(Driver::class);
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        
        $request = new PendingRequest($driver, $masker);
        $request->withPiiMasking(false);

        $driver->shouldReceive('chat')
            ->once()
            ->withArgs(function ($messages, $options) {
                return $messages[0]->content === "Contact me at john@example.com";
            })
            ->andReturn("OK");

        $response = $request->ask("Contact me at john@example.com");
        $this->assertEquals("OK", $response);
    }

    /** @test */
    public function it_masks_all_previous_messages()
    {
        $driver = Mockery::mock(Driver::class);
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        
        $request = new PendingRequest($driver, $masker);
        $request->withPiiMasking(true);
        $request->role("I am an assistant for user@example.com");

        $driver->shouldReceive('chat')
            ->once()
            ->withArgs(function ($messages, $options) {
                return str_contains($messages[0]->content, "I am an assistant for [MASKED_EMAIL_")
                    && str_contains($messages[1]->content, "My phone is [MASKED_PHONE_");
            })
            ->andReturn("OK");

        $response = $request->ask("My phone is 123-456-7890");
        $this->assertEquals("OK", $response);
    }

    /** @test */
    public function it_automatically_unmasks_response()
    {
        $driver = Mockery::mock(Driver::class);
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        
        $request = new PendingRequest($driver, $masker);
        $request->withPiiMasking(true);

        $driver->shouldReceive('chat')
            ->once()
            ->andReturnUsing(function ($messages) {
                // Return a response that contains the masked placeholder
                return "The email was " . $messages[0]->content;
            });

        $response = $request->ask("john@example.com");
        
        $this->assertEquals("The email was john@example.com", $response);
    }

    /** @test */
    public function it_scrubs_pii_when_enabled()
    {
        $driver = Mockery::mock(Driver::class);
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        
        $request = new PendingRequest($driver, $masker);
        $request->scrubPii(true);

        $driver->shouldReceive('chat')
            ->once()
            ->withArgs(function ($messages, $options) {
                return str_contains($messages[0]->content, "[REDACTED EMAIL]");
            })
            ->andReturn("OK");

        $response = $request->ask("Contact me at john@example.com");
        $this->assertEquals("OK", $response);
    }

    /** @test */
    public function it_does_not_unmask_when_scrubbed()
    {
        $driver = Mockery::mock(Driver::class);
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        
        $request = new PendingRequest($driver, $masker);
        $request->scrubPii(true);

        $driver->shouldReceive('chat')
            ->once()
            ->andReturn("The email was [REDACTED EMAIL]");

        $response = $request->ask("john@example.com");
        
        $this->assertEquals("The email was [REDACTED EMAIL]", $response);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
