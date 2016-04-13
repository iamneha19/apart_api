<?php

namespace Api;

use Illuminate\Support\ServiceProvider;

/**
 * Api Service Provider
 *
 * @author Mohammed Mudasir
 */
class ApiServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('api-presentor', function($app)
        {
            return $app->make('Api\Presentor');
        });
    }


    /**
     * Get the services provided.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'api-presentor'
        ];
    }
}
