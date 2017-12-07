<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://laravel-tenancy.com
 * @see https://github.com/hyn/multi-tenant
 */

use Hyn\Tenancy\Database\Connection;

return [
    'models' => [
        /**
         * Specify different models to be used for the global, system database
         * connection. These are also used in their relationships. Models
         * used have to implement their respective contracts and
         * either extend the SystemModel or use the trait
         * UsesSystemConnection.
         */

        // Must implement \Hyn\Tenancy\Contracts\Customer
        'customer' => \Hyn\Tenancy\Models\Customer::class,

        // Must implement \Hyn\Tenancy\Contracts\Hostname
        'hostname' => \Hyn\Tenancy\Models\Hostname::class,

        // Must implement \Hyn\Tenancy\Contracts\Website
        'website' => \Hyn\Tenancy\Models\Website::class
    ],
    'website' => [
        /**
         * Each website has a short random hash that identifies this entity
         * to the application. By default this id is randomized and fully
         * auto-generated. In case you want to force your own logic for
         * when you need to have a better overview of the complete
         * tenant folder structure, disable this and implement
         * your own id generation logic.
         */
        'disable-random-id' => false,

        /**
         * The random Id generator is responsible for creating the hash as mentioned
         * above. You can override what generator to use by modifying this value
         * in the configuration.
         *
         * @warn This won't work if disable-random-id is true.
         */
        'random-id-generator' => Hyn\Tenancy\Generators\Uuid\ShaGenerator::class,

        /**
         * Enable this flag in case you're using a driver that does not support
         * database username or database name with a length of more than 32 characters.
         *
         * This should be enabled for MySQL, but not for MariaDB and PostgreSQL.
         */
        'uuid-limit-length-to-32' => env('LIMIT_UUID_LENGTH_32', false),

        /**
         * Specify the disk you configured in the filesystems.php file where to store
         * the tenant specific files, including media, packages, routes and other
         * files for this particular website.
         *
         * @info If not set, will revert to the default filesystem.
         */
        'disk' => null,

        /**
         * Automatically generate a tenant directory based on the random id of the
         * website. Uses the above disk to store files to override system-wide
         * files.
         *
         * @info set to false to disable.
         */
        'auto-create-tenant-directory' => true,

        /**
         * Automatically rename the tenant directory when the random id of the
         * website changes. This should not be too common, but in case it happens
         * we automatically want to move files accordingly.
         *
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
         */
        'default' => env('TENANCY_DEFAULT_HOSTNAME'),
        /**
         * The package is able to identify the requested hostname by itself,
         * disable to get full control (and responsibility) over hostname
         * identification. The hostname identification is needed to
         * set a specific website as currently active.
         *
         * @see src/Jobs/HostnameIdentification.php
         */
        'auto-identification' => env('TENANCY_AUTO_HOSTNAME_IDENTIFICATION', true),

        /**
         * In case you want to have the tenancy environment set up early,
         * enable this flag. This will run the tenant identification
         * inside a middleware. This will eager load tenancy.
         *
         * A good use case is when you have set "tenant" as the default
         * database connection.
         */
        'early-identification' => env('TENANCY_EARLY_IDENTIFICATION', false),

        /**
         * Abort application execution in case no hostname was identified. This will throw a
         * 404 not found in case the tenant hostname was not resolved.
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
         */
        'default' => env('TENANCY_DEFAULT_CONNECTION'),
        /**
         * Used to give names to the system and tenant database connections. By
         * default we configure 'system' and 'tenant'. The tenant connection
         * is set up automatically by this package.
         *
         * @see src/Database/Connection.php
         * @var system-connection-name The database connection name to use for the global/system database.
         * @var tenant-connection-name The database connection name to use for the tenant database.
         */
        'system-connection-name' => env('TENANCY_SYSTEM_CONNECTION_NAME', Connection::DEFAULT_SYSTEM_NAME),
        'tenant-connection-name' => env('TENANCY_TENANT_CONNECTION_NAME', Connection::DEFAULT_TENANT_NAME),

        /**
         * The tenant division mode specifies to what database websites will be
         * connecting. The default setup is to use a new database per tenant.
         * In case you prefer to use the same database with a table prefix,
         * set the mode to 'prefix'.
         *
         * @see src/Database/Connection.php
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
         * to run the migrations from. If specified these migrations will be executed
         * whenever a new tenant is created.
         *
         * @info set to false to disable auto migrating.
         *
         * @warn this has to be an absolute path, feel free to use helper methods like
         * base_path() or database_path() to set this up.
         */
        'tenant-migrations-path' => database_path('migrations/tenant'),

        /**
         * Seeds the newly created tenant database based on this Seeder.
         *
         * @info requires tenant-migrations-path to be in use.
         *
         * @warn specify a valid fully qualified class name.
         * @example App\Seeders\AdminSeeder::class
         */
        'tenant-seed-class' => false,

        /**
         * Automatically generate a tenant database based on the random id of the
         * website.
         *
         * @info set to false to disable.
         */
        'auto-create-tenant-database' => true,

        /**
         * Automatically rename the tenant database when the random id of the
         * website changes. This should not be too common, but in case it happens
         * we automatically want to move databases accordingly.
         *
         * @info set to false to disable.
         */
        'auto-rename-tenant-database' => true,

        /**
         * Automatically deletes the tenant specific database and all data
         * contained within.
         *
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
        'media' => [
            /**
             * Mounts the assets directory with (static) files for public use.
             */
            'enabled' => true,
        ]
    ]
];
