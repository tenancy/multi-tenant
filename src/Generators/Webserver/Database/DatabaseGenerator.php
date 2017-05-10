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

namespace Hyn\Tenancy\Generators\Webserver\Database;

use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Events;
use Hyn\Tenancy\Exceptions\GeneratorFailedException;
use Hyn\Tenancy\Traits\DispatchesEvents;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Connection as IlluminateConnection;
use Illuminate\Support\Arr;

class DatabaseGenerator
{
    use DispatchesEvents;
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $mode;

    /**
     * DatabaseGenerator constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->mode = config('tenancy.db.tenant-division-mode');
    }

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Events\Websites\Created::class, [$this, 'create']);
    }

    /**
     * @param Events\Websites\Created $event
     * @throws GeneratorFailedException
     */
    public function create(Events\Websites\Created $event)
    {
        if ($this->mode !== Connection::DIVISION_MODE_SEPARATE_DATABASE) {
            return;
        }

        $config = $this->connection->generateConfigurationArray($event->website);

        $this->configureHost($config);

        $this->emitEvent(
            new Events\Database\Creating($config, $event->website)
        );

        $driver = Arr::get($config, 'driver', 'mysql');

        switch ($driver) {
            case 'mysql':
                $success = $this->createMysql($config);
                break;
            case 'pgsql':
                $success = $this->createPostgres($config);
                break;
            default:
                throw new GeneratorFailedException("Could not generate database for driver $driver");
        }

        if (!$success) {
            throw new GeneratorFailedException("Could not generate database {$config['database']}, one of the statements failed.");
        }

        $this->emitEvent(
            new Events\Database\Created($config, $event->website)
        );
    }

    /**
     * @param array $config
     * @return bool
     */
    protected function createMysql(array $config = [])
    {
        $create = function ($connection) use ($config) {
            return $connection->statement("CREATE DATABASE `{$config['database']}`");
        };
        $grant = function ($connection) use ($config) {
            return $connection->statement("GRANT ALL ON `{$config['database']}`.* TO `{$config['username']}`@'{$config['host']}' IDENTIFIED BY '{$config['password']}'");
        };

        return $this->connection->system()->transaction(function (IlluminateConnection $connection) use ($create, $grant) {
            return $create($connection) && $grant($connection);
        });
    }

    /**
     * Mutates specified host for remote connections.
     *
     * @param $config
     */
    protected function configureHost(&$config)
    {
        $host = Arr::get($config, 'host');

        if (! in_array($host, ['localhost', '127.0.0.1', '192.168.0.1'])) {
            $config['host'] = '%';
        }
    }

    /**
     * @param array $config
     * @return bool
     */
    protected function createPostgres(array $config = [])
    {
        $connection = $this->connection->system();

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
}
