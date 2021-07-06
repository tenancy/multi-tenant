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

use Illuminate\Support\Arr;
use Hyn\Tenancy\Contracts\Website;
use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Events\Websites\Created;
use Hyn\Tenancy\Events\Websites\Deleted;
use Hyn\Tenancy\Events\Websites\Updated;
use Hyn\Tenancy\Exceptions\GeneratorFailedException;
use Hyn\Tenancy\Contracts\Webserver\DatabaseGenerator;

class MariaDB implements DatabaseGenerator
{
    /**
     * @param Created $event
     * @param array $config
     * @param Connection $connection
     *
     * @return bool
     */
    public function created(Created $event, array $config, Connection $connection): bool
    {
        $createUser = config('tenancy.db.auto-create-tenant-database-user', true);

        $user = "CREATE USER IF NOT EXISTS `{$config['username']}`@'{$config['host']}' IDENTIFIED BY '{$config['password']}'";

        $create = "CREATE DATABASE IF NOT EXISTS `{$config['database']}`
            DEFAULT CHARACTER SET {$config['charset']}
            DEFAULT COLLATE {$config['collation']}";

        $privileges = config('tenancy.db.tenant-database-user-privileges', null) ?? 'ALL';

        $grant = "GRANT $privileges ON `{$config['database']}`.* TO `{$config['username']}`@'{$config['host']}'";

        if ($createUser) {
            return $connection->system($event->website)->statement($user)
                && $connection->system($event->website)->statement($create)
                && $connection->system($event->website)->statement($grant);
        }

        return $connection->system($event->website)->statement($create);
    }

    /**
     * @param Updated $event
     * @param array $config
     * @param Connection $connection
     *
     * @throws GeneratorFailedException
     *
     * @return bool
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
     *
     * @return bool
     */
    public function deleted(Deleted $event, array $config, Connection $connection): bool
    {
        $deleteUser = config('tenancy.db.auto-delete-tenant-database-user', false);

        $user = "DROP USER IF EXISTS `{$config['username']}`@'{$config['host']}'";

        $delete = "DROP DATABASE IF EXISTS `{$config['database']}`";

        if ($deleteUser) {
            return $connection->system($event->website)->statement($user)
                && $connection->system($event->website)->statement($delete);
        }

        return $connection->system($event->website)->statement($delete);
    }

    public function updatePassword(Website $website, array $config, Connection $connection): bool
    {
        $user = "ALTER USER `{$config['username']}`@'{$config['host']}' IDENTIFIED BY '{$config['password']}'";

        $flush = 'FLUSH PRIVILEGES';

        return $connection->system($website)->statement($user)
            && $connection->system($website)->statement($flush);
    }
}
