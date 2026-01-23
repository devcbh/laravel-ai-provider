<?php

namespace Devcbh\LaravelAiProvider\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Devcbh\LaravelAiProvider\PendingRequest role(string $message)
 * @method static \Devcbh\LaravelAiProvider\PendingRequest lastContext(array $messages)
 * @method static \Devcbh\LaravelAiProvider\PendingRequest model(string $model)
 * @method static \Devcbh\LaravelAiProvider\PendingRequest temperature(float $temperature)
 * @method static string ask(string $prompt)
 * @method static \Devcbh\LaravelAiProvider\Contracts\Driver driver(string|null $driver = null)
 */
class Ai extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ai';
    }
}
