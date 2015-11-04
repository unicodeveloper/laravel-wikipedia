<?php

namespace Unicodeveloper\Larapedia;

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
     *  Publishes all the assets this package needs to function and load all routes
     * @return [type] [description]
     */
    public function boot()
    {
        $config = realpath(__DIR__.'/../resources/config/wikirand.php');

        $this->publishes([
            $config => config_path('wikirand.php')
        ]);
    }

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