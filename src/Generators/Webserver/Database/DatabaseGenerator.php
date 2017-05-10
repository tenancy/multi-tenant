<?php

namespace Hyn\Tenancy\Generators\Webserver\Database;

use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Events\Websites as Events;
use Hyn\Tenancy\Exceptions\GeneratorFailedException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Connection as IlluminateConnection;
use Illuminate\Support\Arr;

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
     * @throws GeneratorFailedException
     */
    public function create(Events\Created $event)
    {
        if ($this->mode !== Connection::DIVISION_MODE_SEPARATE_DATABASE) {
            return;
        }

        $config = $this->connection->generateConfigurationArray($event->website);

        $this->configureHost($config);

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
