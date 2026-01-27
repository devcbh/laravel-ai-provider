<?php

namespace Devcbh\LaravelAiProvider;

use Illuminate\Support\ServiceProvider;
use Devcbh\LaravelAiProvider\Contracts\PiiMasker;
use Devcbh\LaravelAiProvider\PiiMasker\DefaultPiiMasker;

class AiServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ai.php', 'ai');

        $this->app->singleton(PiiMasker::class, function ($app) {
            return new DefaultPiiMasker($app['config']->get('ai.pii_masking', []));
        });

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
