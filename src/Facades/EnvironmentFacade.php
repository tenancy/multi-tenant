<?php

namespace Hyn\Tenancy\Facades;

use Illuminate\Support\Facades\Facade;
use Hyn\Tenancy\Environment;

class EnvironmentFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Environment::class;
    }
}
