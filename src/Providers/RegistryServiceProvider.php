<?php

namespace Upsoftware\Registry\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Upsoftware\Registry\Classes\Registry;
use Upsoftware\Registry\Facades\Registry as RegistryFacade;

class RegistryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        include __DIR__.'/../Http/helpers.php';
    }
    public function register(): void
    {
        $this->registerFacades();
    }

    protected function registerFacades(): void
    {
        $loader = AliasLoader::getInstance();

        $loader->alias('registry', RegistryFacade::class);
        $this->app->singleton('registry', function () {
            return app()->make(Registry::class);
        });
    }
}
