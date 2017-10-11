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
        $create = function ($connection) use ($config) {
            return $connection->statement("CREATE DATABASE `{$config['database']}`");
        };
        $grant = function ($connection) use ($config) {
            return $connection->statement("GRANT ALL ON `{$config['database']}`.* TO `{$config['username']}`@'{$config['host']}' IDENTIFIED BY '{$config['password']}'");
        };

        return $connection->system()->transaction(function (IlluminateConnection $connection) use ($create, $grant) {
            return $create($connection) && $grant($connection);
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

//        if (!$connection->system()->statement("RENAME TABLE `$uuid`.`table` TO `{$config['database']}`.`table`")) {
//            throw new GeneratorFailedException("Could not rename database {$config['database']}, the statement failed.");
//        }

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
        if (!$connection->system()->statement("DROP DATABASE IF EXISTS `{$config['database']}`")) {
            throw new GeneratorFailedException("Could not delete database {$config['database']}, the statement failed.");
        }

        return true;
    }
}
