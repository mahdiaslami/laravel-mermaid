<?php

namespace MahdiAslami\Database;

use Illuminate\Support\ServiceProvider;

class MermaidServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'mahdiaslami');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'mahdiaslami');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mermaid.php', 'laravel-mermaid');

        // Register the service the package provides.
        $this->app->singleton('mermaid', function ($app) {
            return new Mermaid;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['mermaid'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/mermaid.php' => config_path('mermaid.php'),
        ], 'laravel-mermaid.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/mahdiaslami'),
        ], 'laravel-mermaid.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/mahdiaslami'),
        ], 'laravel-mermaid.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/mahdiaslami'),
        ], 'laravel-mermaid.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
