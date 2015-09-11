<?php

return [
    /*
     * Overrule the default tenant directory, where files for tenant websites are stored
     * @default app_storage('/multi-tenant') which resolves to /resources/multi-tenant
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
    ]
];
