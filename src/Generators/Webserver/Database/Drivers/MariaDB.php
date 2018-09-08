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
        $user = function ($connection) use ($config) {
            if (config('tenancy.db.auto-create-tenant-database-user', true)) {
                return $connection->statement("CREATE USER IF NOT EXISTS `{$config['username']}`@'{$config['host']}' IDENTIFIED BY '{$config['password']}'");
            }

            return true;
        };
        $create = function ($connection) use ($config) {
            return $connection->statement("CREATE DATABASE IF NOT EXISTS `{$config['database']}`
            DEFAULT CHARACTER SET `{$config['charset']}`
            DEFAULT COLLATE `{$config['collation']}`");
        };
        $grant = function ($connection) use ($config) {
            return $connection->statement("GRANT ALL ON `{$config['database']}`.* TO `{$config['username']}`@'{$config['host']}'");
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
}
