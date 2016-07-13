<?php

use Hyn\ManagementInterface\ManagementInterfaceServiceProvider;
use Hyn\Tenancy\TenancyServiceProvider;
use Hyn\Webserver\WebserverServiceProvider;

return [
    'packages' => [
        'multi-tenant'         => [
            'description'      => 'Multi tenancy for Laravel 5',
            'service-provider' => TenancyServiceProvider::class,
        ],
        'management-interface' => [
            'description'      => 'Interface for managing webserver and multi tenancy',
            'service-provider' => ManagementInterfaceServiceProvider::class,
        ],
        'webserver'            => [
            'description'      => 'Integration into and generation of configs for webservices',
            'service-provider' => WebserverServiceProvider::class,
        ],
    ],
];
