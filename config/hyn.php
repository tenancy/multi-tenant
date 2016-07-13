<?php

return [
    'packages' => [
        'multi-tenant'         => [
            'description'      => 'Multi tenancy for Laravel 5',
            'service-provider' => 'Hyn\Tenancy\MultiTenantServiceProvider',
        ],
        'management-interface' => [
            'description'      => 'Interface for managing webserver and multi tenancy',
            'service-provider' => 'Hyn\ManagementInterface\ManagementInterfaceServiceProvider',
        ],
        'webserver'            => [
            'description'      => 'Integration into and generation of configs for webservices',
            'service-provider' => 'Hyn\Webserver\WebserverServiceProvider',
        ],
    ],
];
