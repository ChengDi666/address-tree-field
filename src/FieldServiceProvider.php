<?php

namespace Norgeit\AddressTreeField;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Norgeit\AddressTreeField\Domain\Cache\ArrayCache;
use Norgeit\AddressTreeField\Domain\Cache\Cache;
use Norgeit\AddressTreeField\Domain\Relation\Handlers\BelongsToHandler;
use Norgeit\AddressTreeField\Domain\Relation\Handlers\BelongsToManyHandler;
use Norgeit\AddressTreeField\Domain\Relation\Handlers\HasManyHandler;
use Norgeit\AddressTreeField\Domain\Relation\RelationHandlerFactory;
use Norgeit\AddressTreeField\Domain\Relation\RelationHandlerResolver;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::serving(function (ServingNova $event) {
            Nova::script('address-tree-field', __DIR__.'/../dist/js/field.js');
            Nova::style('address-tree-field', __DIR__.'/../dist/css/field.css');
        });

        $this->app->booted(function () {
            \Route::middleware(['nova'])
                ->prefix('nova-vendor/address-tree-field')
                ->group(__DIR__.'/../routes/api.php');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(RelationHandlerFactory::class, RelationHandlerResolver::class);

        $factory = $this->app->make(RelationHandlerFactory::class);

        $factory->register($this->app->make(BelongsToManyHandler::class));
        $factory->register($this->app->make(BelongsToHandler::class));
        $factory->register($this->app->make(HasManyHandler::class));


        $this->app->singleton(Cache::class, ArrayCache::class);
    }
}
