<?php

namespace Api;

use Illuminate\Support\Facades\Facade;

/**
 * Api Facade
 *
 * @author Mohammed Mudasir
 */
class ApiFacade extends facade
{
    protected static function getFacadeAccessor() { return 'api-presentor'; }
}
