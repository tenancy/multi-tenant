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

namespace Hyn\Tenancy\Generators\Webserver\Database\Drivers;

use Hyn\Tenancy\Contracts\Webserver\DatabaseGenerator;
use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Events\Websites\Created;
use Hyn\Tenancy\Events\Websites\Deleted;
use Hyn\Tenancy\Events\Websites\Updated;
use Hyn\Tenancy\Exceptions\GeneratorFailedException;
use Illuminate\Support\Arr;

class PostgreSQL implements DatabaseGenerator
{
    /**
     * @param Created $event
     * @param array $config
     * @param Connection $connection
     * @return bool
     */
    public function created(Created $event, array $config, Connection $connection): bool
    {
        $connection = $connection->system();

        $user = function () use ($connection, $config) {
            return $connection->statement("CREATE USER \"{$config['username']}\" WITH PASSWORD '{$config['password']}'");
        };
        $create = function () use ($connection, $config) {
            return $connection->statement("CREATE DATABASE \"{$config['database']}\" WITH OWNER=\"{$config['username']}\"");
        };
        $grant = function () use ($connection, $config) {
            return $connection->statement("GRANT ALL PRIVILEGES ON DATABASE \"{$config['database']}\" TO \"{$config['username']}\"");
        };

        return $user() && $create() && $grant();
    }

    /**
     * @param Updated $event
     * @param array $config
     * @param Connection $connection
     * @return bool
     * @throws GeneratorFailedException
     */
    public function updated(Updated $event, array $config, Connection $connection): bool
    {
        $uuid = Arr::get($event->dirty, 'uuid');

        if (!$connection->system()->statement("ALTER DATABASE \"$uuid\" RENAME TO \"{$config['database']}\"")) {
            throw new GeneratorFailedException("Could not delete database {$config['database']}, the statement failed.");
        }

        return true;
    }

    /**
     * @param Deleted $event
     * @param array $config
     * @param Connection $connection
     * @return bool
     * @throws GeneratorFailedException
     */
    public function deleted(Deleted $event, array $config, Connection $connection): bool
    {
        $connection->get()->disconnect();

        $user = function () use ($connection, $config) {
            return $connection->system()->statement("DROP USER \"{$config['username']}\"");
        };
        $delete = function () use ($connection, $config) {
            return $connection->system()->statement("DROP DATABASE IF EXISTS \"{$config['database']}\"");
        };

        return $delete() && $user();
    }
}
