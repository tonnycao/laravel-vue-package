<?php

namespace FDT\DataLoader;

use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\ServiceProvider;

class DataLoaderServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->registerViews();
        $this->registerConfig();
        $this->registerFactories();
        $this->registerAssets();
        $this->registerMigrations();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(DataLoaderAuthServiceProvider::class);
        $this->commands([
            \FDT\DataLoader\Commands\ApprovedJob::class,
            \FDT\DataLoader\Commands\DataSource::class,
        ]);
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/dataloader');
        $sourcePath = __DIR__.'/../src/resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'dataloader-views');

        $this->loadViewsFrom(__DIR__.'/resources/views', 'dataloader');
    }

    /**
     * Register assets.
     *
     * @return void
     */
    public function registerAssets()
    {
        $assetsPath = public_path('js');
        $sourcePath = __DIR__.'/../src/resources/public/js';

        $this->publishes([
            $sourcePath => $assetsPath,
        ], 'dataloader-assets');
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../src/Database/factories');
        }
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__ . '/Config/dataloader.php' => config_path('dataloader.php'),
        ]);
        $this->mergeConfigFrom(
            __DIR__ . '/Config/dataloader.php','dataloader'
        );
    }

    /**
     * Register Migrations.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        $migrationsPath = database_path('migrations');
        $sourcePath = __DIR__.'/../src/Database/migrations';

        $this->publishes([
            $sourcePath => $migrationsPath,
        ], 'dataloader-migrations');

        $this->loadMigrationsFrom(__DIR__ . '/../src/Database/migrations');
    }
}
