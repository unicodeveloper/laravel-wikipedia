<?php

namespace Busayo\Larapedia;

use Illuminate\Support\ServiceProvider;

class WikiRandServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the application services
     * @return void
     */
    public function register()
    {
        $this->app->bind('wikirand', function(){
            return new WikiRand(config('wikirand'));
        });
    }

    /**
     * Get the services provided by the provider
     * @return array
     */
    public function provides()
    {
        return ['wikirand'];
    }
}