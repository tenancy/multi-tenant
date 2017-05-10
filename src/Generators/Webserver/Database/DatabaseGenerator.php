<?php

namespace Hyn\Tenancy\Generators\Webserver\Database;

use Illuminate\Database\Connection as IlluminateConnection;
use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Exceptions\GeneratorFailedException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Hyn\Tenancy\Events\Websites as Events;

class DatabaseGenerator
{
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
        $events->listen(Events\Created::class, [$this, 'create']);
    }

    /**
     * @param Events\Created $event
     */
    public function create(Events\Created $event)
    {
        if ($this->mode !== Connection::DIVISION_MODE_SEPARATE_DATABASE) {
            return;
        }

        $config = $this->connection->generateConfigurationArray($event->website);

        $this->connection->system()->transaction(function (IlluminateConnection $connection) use ($config) {
            $driver = Arr::get($config, 'driver', 'mysql');

            switch ($driver) {
                case 'mysql':
                    $success = $this->createMysql($connection, $config);
                    break;
                case 'pgsql':
                    $success = $this->createPostgres($connection, $config);
                    break;
                default:
                    throw new GeneratorFailedException("Could not generate database for driver $driver");
            }

            if (!$success) {
                throw new GeneratorFailedException("Could not generate database {$config['database']}, one of the statements failed.");
            }
        });
    }

    /**
     * @param $connection
     * @param array $config
     * @return bool
     */
    protected function createMysql($connection, array $config = [])
    {
        $create = function() use ($connection, $config) {
            return $connection->statement("CREATE DATABASE `{$config['database']}`");
        };
        $grant = function() use ($connection, $config) {
            return $connection->statement("GRANT ALL ON `{$config['database']}`.* TO `{$config['username']}`@'{$config['host']}' IDENTIFIED BY '{$config['password']}'");
        };

        return $create() && $grant();
    }

    /**
     * @param $connection
     * @param array $config
     * @return bool
     */
    protected function createPostgres($connection, array $config = [])
    {
        $user = function () use ($connection, $config) {
            return $connection->statement("CREATE USER \"{$config['username']}\" WITH PASSWORD '{$config['password']}'");
        };
        $create = function () use ($connection, $config) {
            return $connection->statement("CREATE DATABASE {$config['database']} WITH OWNER=\"{$config['username']}\"");
        };
        $grant = function () use ($connection, $config) {
            return $connection->statement("GRANT ALL PRIVILEGES ON DATABASE {$config['database']} TO \"{$config['username']}\"");
        };

        return $user() && $create() && $grant();
    }
}
