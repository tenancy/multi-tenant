<?php

namespace Hyn\Tenancy\Facades;

use Hyn\Tenancy\Environment;
use Illuminate\Support\Facades\Facade;

class TenancyFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Environment::class;
    }
}
