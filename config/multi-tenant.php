<?php

return [
    /*
     * Overrule the default tenant directory, where files for tenant websites are stored
     * @default app_storage('/multi-tenant') which resolves to /storage/multi-tenant
     */
    'tenant-directory' => null,

    'db' => [
        /*
         * The name for the system connection, this connection should be configured
         * you can configure this connection within the file config/database.php
         * by adding/editing under connections the key `hyn` or whatever
         * you specify beneath.
         */
        'system-connection-name' => 'hyn',
        /*
         * The name for the tenant connection, this connection will be generated dynamically
         * be aware that specifying this connection name in config/database.php is unneeded,
         * even more, the connection will be overwritten with the tenancy code.
         */
        'tenant-connection-name' => 'tenant',
        /*
         * Specify how you wish to separate the tenant databases from each other. By default
         * we divide them with separate databases. Alternately you can specify `prefix`,
         * which will create tenancy within the same database where tenants are having
         * their own tables with a unique prefix.
         */
        'tenant-division-mode' => 'database',
    ],

    /*
     * Whether to use the middleware.
     * If enabled the hostname will be globally set by the middleware, which ensures the correct
     * tenant data is loaded to be used. Disabling auto detection comes down to you setting
     * the current hostname or tenant by yourself.
     */
    'hostname-detection-middleware' => true,

    /*
     * The queue to run webserver tasks on
     * The specified queue name must have root privileges. If no value specified the default queue is
     * used.
     */
    'queue' => [
        'root' => null,
        'other' => null,
    ],

    /*
     * Specify the tenant specific functionality which should be globally disabled.
     *
     * If base is set to true, no tenant directories are created at all.
     */
    'disallow-for-tenant' => [
        'base'      => false,
        'config'    => false,
        'lang'      => false,
        'media'     => false,
        'providers' => false,
        'routes'    => false,
        'vendor'    => false,
        'views'     => false,
    ],
];
