<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/hyn/multi-tenant
 *
 */

use Hyn\Tenancy\Database\Connection;

return [
    'website' => [
        /**
         * Each website has a short random hash that identifies this entity
         * to the application. By default this id is randomized and fully
         * auto-generated. In case you want to force your own logic fi
         * when you need to have a better overview of the complete
         * tenant folder structure, disable this and implement
         * your own id generation logic.
         *
         * @see https://hyn.readme.io/v3.0/docs/tenancy#section-websitedisable-random-id
         */
        'disable-random-id' => false,

        /**
         * The random Id generator is responsible for creating the hash as mentioned
         * above. You can override what generator to use by modifying this value
         * in the configuration.
         *
         * @see https://hyn.readme.io/v3.0/docs/tenancy#section-websiterandom-id-generator
         * @warn This won't work if disable-random-id is true.
         */
        'random-id-generator' => Hyn\Tenancy\Generators\Uuid\ShaGenerator::class,

        /**
         * Specify the disk you configured in the filesystems.php file where to store
         * the tenant specific files, including media, packages, routes and other
         * files for this particular website.
         *
         * @see
         * @info If not set, will revert to the default filesystem.
         */
        'disk' => null,

        /**
         * Automatically generate a tenant directory based on the random id of the
         * website. Uses the above disk to store files to override system-wide
         * files.
         *
         * @see
         * @info set to false to disable.
         */
        'auto-create-tenant-directory' => true,

        /**
         * Automatically rename the tenant directory when the random id of the
         * website changes. This should not be too common, but in case it happens
         * we automatically want to move files accordingly.
         *
         * @see
         * @info set to false to disable.
         */
        'auto-rename-tenant-directory' => true,

        /**
         * Automatically deletes the tenant specific directory and all files
         * contained within.
         *
         * @see
         * @info set to true to enable.
         */
        'auto-delete-tenant-directory' => false,

        /**
         * Time to cache websites in minutes. Set to false to disable.
         */
        'cache' => 10,
    ],
    'hostname' => [
        /**
         * If you want the multi tenant application to fall back to a default
         * hostname/website in case the requested hostname was not found
         * in the database, complete in detail the default hostname.
         *
         * @warn this must be a FQDN, these have no protocol or path!
         *
         * @see https://hyn.readme.io/v3.0/docs/tenancy#section-hostnamedefault
         */
        'default' => env('TENANCY_DEFAULT_HOSTNAME'),
        /**
         * The package is able to identify the requested hostname by itself,
         * disable to get full control (and responsibility) over hostname
         * identification. The hostname identification is needed to
         * set a specific website as currently active.
         *
         * @see src/Jobs/HostnameIdentification.php
         * @see https://hyn.readme.io/v3.0/docs/tenancy#section-hostnameauto-identification
         */
        'auto-identification' => env('TENANCY_AUTO_HOSTNAME_IDENTIFICATION', true),
        /**
         * Abort application execution in case no hostname was identified. This will throw a
         * 404 not found in case the tenant hostname was not resolved.
         *
         * @see https://hyn.readme.io/v3.0/docs/tenancy#section-hostnameabort-without-identified-hostname
         */
        'abort-without-identified-hostname' => true,

        /**
         * Time to cache hostnames in minutes. Set to false to disable.
         */
        'cache' => 10,
    ],
    'db' => [
        /**
         * The default connection to use; this overrules the Laravel database.default
         * configuration setting. In Laravel this is normally configured to 'mysql'.
         * You can set a environment variable to override the default database
         * connection to - for instance - the tenant connection 'tenant'.
         *
         * @see https://hyn.readme.io/v3.0/docs/tenancy#section-dbdefault
         */
        'default' => env('TENANCY_DEFAULT_CONNECTION'),
        /**
         * Used to give names to the system and tenant database connections. By
         * default we configure 'system' and 'tenant'. The tenant connection
         * is set up automatically by this package.
         *
         * @see src/Database/Connection.php
         * @see https://hyn.readme.io/v3.0/docs/tenancy#section-dbsystem-connection-name
         * @see https://hyn.readme.io/v3.0/docs/tenancy#section-dbtenant-connection-name
         * @see https://hyn.readme.io/v3.0/docs/tenancy#section-dbmigration-connection-name
         */
        'system-connection-name' => env('TENANCY_SYSTEM_CONNECTION_NAME', Connection::DEFAULT_SYSTEM_NAME),
        'tenant-connection-name' => env('TENANCY_TENANT_CONNECTION_NAME', Connection::DEFAULT_TENANT_NAME),
        'tenant-migration-name' => env('TENANCY_MIGRATION_CONNECTION_NAME', Connection::DEFAULT_MIGRATION_NAME),

        /**
         * The tenant division mode specifies to what database websites will be
         * connecting. The default setup is to use a new database per tenant.
         * In case you prefer to use the same database with a table prefix,
         * set the mode to 'prefix'.
         *
         * @see src/Database/Connection.php
         * @see https://hyn.readme.io/v3.0/docs/tenancy#section-dbtenant-division-mode
         */
        'tenant-division-mode' => env('TENANCY_DATABASE_DIVISION_MODE', 'database'),

        /**
         * The database password generator takes care of creating a valid hashed
         * string used for tenants to connect to the specific database. Do
         * note that this will only work in 'division modes' that set up
         * a connection to a separate database.
         */
        'password-generator' => Hyn\Tenancy\Generators\Database\DefaultPasswordGenerator::class,

        /**
         * The tenant migrations to be run during creation of a tenant. Specify a directory
         * to run the migrations from.
         *
         * @see https://hyn.readme.io/v3.0/docs/tenancy#section-dbtenant-migrations-path
         */
        'tenant-migrations-path' => false,

        /**
         * Automatically generate a tenant database based on the random id of the
         * website.
         *
         * @see
         * @info set to false to disable.
         */
        'auto-create-tenant-database' => true,

        /**
         * Automatically rename the tenant database when the random id of the
         * website changes. This should not be too common, but in case it happens
         * we automatically want to move databases accordingly.
         *
         * @see
         * @info set to false to disable.
         */
        'auto-rename-tenant-database' => true,

        /**
         * Automatically deletes the tenant specific database and all data
         * contained within.
         *
         * @see
         * @info set to true to enable.
         */
        'auto-delete-tenant-database' => false,
    ],
    'folders' => [
        'config' => [
            /**
             * Merge configuration files from the config directory
             * inside the tenant directory with the global configuration files.
             */
            'enabled' => true,

            /**
             * List of configuration files to ignore, preventing override of crucial
             * application configurations.
             */
            'blacklist' => ['database', 'tenancy', 'webserver'],
        ],
        'routes' => [
            /**
             * Allows adding and overriding URL routes inside the tenant directory.
             */
            'enabled' => true,

            /**
             * Prefix all tenant routes.
             */
            'prefix' => null,
        ],
        'trans' => [
            /**
             * Allows reading translation files from a trans directory inside
             * the tenant directory.
             */
            'enabled' => true,

            /**
             * Will override the global translations with the tenant translations.
             * This is done by overriding the laravel default translator with the new path.
             */
            'override-global' => true,

            /**
             * In case you disabled global override, specify a namespace here to load the
             * tenant translation files with.
             */
            'namespace' => 'tenant',
        ],
        'vendor' => [
            /**
             * Allows using a custom vendor (composer driven) folder inside
             * the tenant directory.
             */
            'enabled' => true,
        ],
    ]
];
