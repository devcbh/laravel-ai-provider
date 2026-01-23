<?php

namespace Devcbh\LaravelAiProvider;

use Illuminate\Support\ServiceProvider;

class AiServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ai.php', 'ai');

        $this->app->singleton('ai', function ($app) {
            return new AiManager($app);
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ai.php' => config_path('ai.php'),
            ], 'ai-config');
        }
    }
}
