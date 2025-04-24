<?php

namespace NamHuuNam\MovieContentGenerator\Providers;

use Illuminate\Support\ServiceProvider;
use NamHuuNam\MovieContentGenerator\Console\Commands\GenerateMovieContentCommand;

class MovieContentGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../../config/MovieContentGenerator.php', 'MovieContentGenerator'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config file
        $this->publishes([
            __DIR__ . '/../../../config/MovieContentGenerator.php' => config_path('MovieContentGenerator.php'),
        ], 'config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../../../database/migrations/' => database_path('migrations'),
        ], 'migrations');

        // Register command
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateMovieContentCommand::class,
            ]);
        }
    }
}