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
use Illuminate\Support\Str;

class MariaDB implements DatabaseGenerator
{
    /**
     * @param Created $event
     * @param array $config
     * @param Connection $connection
     * @return bool
     */
    public function created(Created $event, array $config, Connection $connection): bool
    {
        $createUser = config('tenancy.db.auto-create-tenant-database-user', true);

        $user = function ($connection) use ($config, $createUser) {
            if ($createUser) {
                return $connection->statement("CREATE USER IF NOT EXISTS `{$config['username']}`@'{$config['host']}' IDENTIFIED BY '{$config['password']}'");
            }

            return true;
        };
        $create = function ($connection) use ($config) {
            return $connection->statement("CREATE DATABASE IF NOT EXISTS `{$config['database']}`
            DEFAULT CHARACTER SET {$config['charset']}
            DEFAULT COLLATE {$config['collation']}");
        };
        $grant = function ($connection) use ($config, $createUser) {
            if ($createUser) {
                $privileges = config('tenancy.db.tenant-database-user-privileges', null) ?? 'ALL';
                return $connection->statement("GRANT $privileges ON `{$config['database']}`.* TO `{$config['username']}`@'{$config['host']}'");
            }

            return true;
        };

        return $connection->system($event->website)->transaction(function (IlluminateConnection $connection) use ($user, $create, $grant) {
            return $user($connection) && $create($connection) && $grant($connection);
        });
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

        $this->created(new Created($event->website), $config, $connection);

//        if (!$connection->system($event->website)->statement("RENAME TABLE `$uuid`.`table` TO `{$config['database']}`.`table`")) {
//            throw new GeneratorFailedException("Could not rename database {$config['database']}, the statement failed.");
//        }

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
        $user = function ($connection) use ($config) {
            if (config('tenancy.db.auto-delete-tenant-database-user', false)) {
                return $connection->statement("DROP USER IF EXISTS `{$config['username']}`@'{$config['host']}'");
            }

            return true;
        };

        $delete = function ($connection) use ($config) {
            return $connection->statement("DROP DATABASE IF EXISTS `{$config['database']}`");
        };

        return $connection->system($event->website)->transaction(function (IlluminateConnection $connection) use ($user, $delete) {
            return $delete($connection) && $user($connection);
        });
    }

    public function updatePassword(Website $website, array $config, Connection $connection): bool
    {
        $user = function (IlluminateConnection $connection) use ($config) {
            if ($this->isMariaDb($connection)) {
                return $connection->statement("UPDATE mysql.user SET Password=PASSWORD('{$config['password']}') WHERE User='{$config['username']}' AND Host='{$config['host']}'");
            } else {
                return $connection->statement("ALTER USER `{$config['username']}`@'{$config['host']}' IDENTIFIED BY '{$config['password']}'");
            }
        };

        $flush = function (IlluminateConnection $connection) {
            return $connection->statement('FLUSH PRIVILEGES');
        };

        return $connection->system($website)->transaction(function (IlluminateConnection $connection) use ($user, $flush) {
            return $user($connection) && $flush($connection);
        });
    }

    protected function isMariaDb(IlluminateConnection $connection): bool
    {
        $platform = $connection->getDoctrineSchemaManager()->getDatabasePlatform();
        $reflect = new \ReflectionClass($platform);

        return Str::startsWith($reflect->getShortName(), 'MariaDb');
    }
}
