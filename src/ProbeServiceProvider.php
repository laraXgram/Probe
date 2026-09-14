<?php

namespace LaraGram\Probe;

use LaraGram\Contracts\Support\DeferrableProvider;
use LaraGram\Foundation\Application as LaraGramApplication;
use LaraGram\Support\ServiceProvider;
use LaraGram\Probe\Console\ProbeCommand;

class ProbeServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $source = realpath($raw = __DIR__.'/../config/probe.php') ?: $raw;

        if ($this->app instanceof LaraGramApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => $this->app->configPath('probe.php')]);
        }

        $this->mergeConfigFrom($source, 'probe');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.probe', function () {
            return new ProbeCommand;
        });

        $this->commands(['command.probe']);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['command.probe'];
    }
}
