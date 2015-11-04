<?php

namespace Unicodeveloper\Larapedia;

use Illuminate\Support\Facades\Facade;

class WikiRandFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'wikirand';
    }
}