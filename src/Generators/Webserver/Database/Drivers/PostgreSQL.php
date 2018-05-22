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
use Illuminate\Database\Connection as IlluminateConnection;
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
        $user = function (IlluminateConnection $connection) use ($config) {
            if (config('tenancy.db.auto-create-tenant-database-user') && !$this->userExists($connection, $config['username'])) {
                return $connection->statement("CREATE USER \"{$config['username']}\" WITH PASSWORD '{$config['password']}'");
            }

            return true;
        };
        $create = function (IlluminateConnection $connection) use ($config) {
            return $connection->statement("CREATE DATABASE \"{$config['database']}\" WITH OWNER=\"{$config['username']}\"");
        };
        $grant = function (IlluminateConnection $connection) use ($config) {
            return $connection->statement("GRANT ALL PRIVILEGES ON DATABASE \"{$config['database']}\" TO \"{$config['username']}\"");
        };

        $connection = $connection->system($event->website);

        return $user($connection) && $create($connection) && $grant($connection);
    }

    protected function userExists($connection, string $username): bool
    {
        return $connection->table('pg_roles')
            ->where('rolname', $username)
            ->count() > 0;
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

        if (!$connection->system($event->website)->statement("ALTER DATABASE \"$uuid\" RENAME TO \"{$config['database']}\"")) {
            throw new GeneratorFailedException("Could not delete database {$config['database']}, the statement failed.");
        }

        return true;
    }

    /**
     * @param Deleted $event
     * @param array $config
     * @param Connection $connection
     * @return bool
     */
    public function deleted(Deleted $event, array $config, Connection $connection): bool
    {
        $existing = $connection->configuration();

        if (Arr::get($existing, 'uuid') === $event->website->uuid) {
            $connection->get()->disconnect();
        }

        $user = function (IlluminateConnection $connection) use ($config) {
            if (config('tenancy.db.auto-delete-tenant-database-user') && $this->userExists($connection, $config['username'])) {
                return $connection->statement("DROP USER \"{$config['username']}\"");
            }

            return true;
        };
        $delete = function (IlluminateConnection $connection) use ($config) {
            return $connection->statement("DROP DATABASE IF EXISTS \"{$config['database']}\"");
        };

        $connection = $connection->system($event->website);

        return $delete($connection) && $user($connection);
    }
}
