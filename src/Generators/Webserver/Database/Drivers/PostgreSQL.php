<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://tenancy.dev
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Generators\Webserver\Database\Drivers;

use Hyn\Tenancy\Contracts\Webserver\DatabaseGenerator;
use Hyn\Tenancy\Contracts\Website;
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
     * @param Created    $event
     * @param array      $config
     * @param Connection $connection
     * @return bool
     */
    public function created(Created $event, array $config, Connection $connection): bool
    {
        $connection = $connection->system($event->website);

        $createUser = config('tenancy.db.auto-create-tenant-database-user');

        if ($createUser) {
            return
                $this->createUser($connection, $config) &&
                $this->createDatabase($connection, $config) &&
                $this->grantPrivileges($connection, $config);
        } else {
            return $this->createDatabase($connection, $config);
        }
    }

    protected function createUser(IlluminateConnection $connection, array $config)
    {
        if (!$this->userExists($connection, $config['username'])) {
            return $connection->statement("CREATE USER \"{$config['username']}\" WITH PASSWORD '{$config['password']}'");
        }

        return true;
    }

    protected function createDatabase(IlluminateConnection $connection, array $config)
    {
        return $connection->statement("CREATE DATABASE \"{$config['database']}\"");
    }

    protected function grantPrivileges(IlluminateConnection $connection, array $config)
    {
        $granted = $connection->statement("GRANT ALL PRIVILEGES ON DATABASE \"{$config['database']}\" TO \"{$config['username']}\"");

        // Every role may connect to a new database by default, leaving one
        // tenant able to reach another's.
        $connection->statement("REVOKE CONNECT ON DATABASE \"{$config['database']}\" FROM PUBLIC");

        return $this->grantSchemaPrivileges($connection, $config) && $granted;
    }

    /**
     * Grants the tenant the right to create objects in its own database.
     *
     * PostgreSQL 15 revoked the CREATE privilege the public schema used to
     * grant to everyone, which privileges on the database no longer cover. A
     * schema grant only applies inside its own database, hence the connection.
     *
     * It goes to PUBLIC rather than to the tenant role because a role level
     * grant is recorded as a dependency and makes DROP USER fail on deletion.
     * CONNECT was revoked from PUBLIC above, so nothing else reaches it.
     */
    protected function grantSchemaPrivileges(IlluminateConnection $connection, array $config)
    {
        $name = 'tenancy-provisioning';

        config([
            "database.connections.{$name}" => array_merge(
                $connection->getConfig(),
                ['database' => $config['database']]
            ),
        ]);

        try {
            return app('db')->connection($name)->statement('GRANT ALL ON SCHEMA public TO PUBLIC');
        } finally {
            app('db')->purge($name);
        }
    }

    protected function userExists($connection, string $username): bool
    {
        return $connection->table('pg_roles')
                ->where('rolname', $username)
                ->count() > 0;
    }

    /**
     * @param Updated    $event
     * @param array      $config
     * @param Connection $connection
     * @return bool
     * @throws GeneratorFailedException
     */
    public function updated(Updated $event, array $config, Connection $connection): bool
    {
        $uuid = Arr::get($event->dirty, 'uuid');

        if (!$connection->system($event->website)->statement("ALTER DATABASE \"$uuid\" RENAME TO \"{$config['database']}\"")) {
            throw new GeneratorFailedException("Could not rename database {$config['database']}, the statement failed.");
        }

        return true;
    }

    /**
     * @param Deleted    $event
     * @param array      $config
     * @param Connection $connection
     * @return bool
     */
    public function deleted(Deleted $event, array $config, Connection $connection): bool
    {
        $existing = $connection->configuration();

        if (Arr::get($existing, 'uuid') === $event->website->uuid) {
            $connection->get()->disconnect();
        }

        $connection = $connection->system($event->website);

        return
            $this->flushConnection($connection, $config) &&
            $this->dropPriviliges($connection, $config) &&
            $this->dropDatabase($connection, $config) &&
            $this->dropUser($connection, $config);
    }

    protected function flushConnection(IlluminateConnection $connection, array $config)
    {
        $connection
            ->table('pg_stat_activity')
            ->select($connection->raw('pg_terminate_backend(pid)'))
            ->where('datname', $config['database'])
            ->where('pid', '<>', $connection->raw('pg_backend_pid()'))
            ->get();

        return true;
    }

    protected function dropPriviliges(IlluminateConnection $connection, array $config)
    {
        if ($this->userExists($connection, $config['username'])) {
            return $connection->statement("DROP OWNED BY \"{$config['username']}\"");
        }

        return true;
    }

    protected function dropDatabase(IlluminateConnection $connection, array $config)
    {
        return $connection->statement("DROP DATABASE IF EXISTS \"{$config['database']}\"");
    }

    protected function dropUser(IlluminateConnection $connection, array $config)
    {
        if (config('tenancy.db.auto-delete-tenant-database-user') && $this->userExists(
            $connection,
            $config['username']
        )) {
            return $connection->statement("DROP USER \"{$config['username']}\"");
        }

        return true;
    }

    public function updatePassword(Website $website, array $config, Connection $connection): bool
    {
        return $connection->system($website)->statement("ALTER USER \"{$config['username']}\" WITH PASSWORD '{$config['password']}'");
    }
}
