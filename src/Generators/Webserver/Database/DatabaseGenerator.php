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

namespace Hyn\Tenancy\Generators\Webserver\Database;

use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Events;
use Hyn\Tenancy\Exceptions\GeneratorFailedException;
use Hyn\Tenancy\Traits\DispatchesEvents;
use Illuminate\Contracts\Events\Dispatcher;
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
     * @var DatabaseDriverFactory
     */
    protected $factory;

    /**
     * DatabaseGenerator constructor.
     * @param Connection $connection
     * @param DatabaseDriverFactory $factory
     */
    public function __construct(Connection $connection, DatabaseDriverFactory $factory)
    {
        $this->connection = $connection;
        $this->mode = config('tenancy.db.tenant-division-mode');
        $this->factory = $factory;
    }

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Events\Websites\Created::class, [$this, 'created']);
        $events->listen(Events\Websites\Updated::class, [$this, 'updated']);
        $events->listen(Events\Websites\Deleted::class, [$this, 'deleted']);
        $events->listen(Events\KeyUpdated::class, [$this, 'keyUpdated']);
    }

    /**
     * @param Events\Websites\Created $event
     * @throws GeneratorFailedException
     */
    public function created(Events\Websites\Created $event)
    {
        if (!config('tenancy.db.auto-create-tenant-database', true)) {
            return;
        }

        if (!in_array($this->mode, [
            Connection::DIVISION_MODE_SEPARATE_DATABASE,
            Connection::DIVISION_MODE_SEPARATE_SCHEMA,
        ])) {
            return;
        }

        $config = $this->connection->generateConfigurationArray($event->website);

        $this->configureHost($config);

        $this->emitEvent(
            new Events\Database\Creating($config, $event->website)
        );

        if (!$this->factory->create($config['driver'])->created($event, $config, $this->connection)) {
            throw new GeneratorFailedException("Could not generate database {$config['database']}, one of the statements failed.");
        }

        $this->emitEvent(
            new Events\Database\Created($config, $event->website)
        );
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
     * @param Events\Websites\Deleted $event
     * @throws GeneratorFailedException
     */
    public function deleted(Events\Websites\Deleted $event)
    {
        if (!config('tenancy.db.auto-delete-tenant-database', false)) {
            return;
        }

        if (!in_array($this->mode, [
            Connection::DIVISION_MODE_SEPARATE_DATABASE,
            Connection::DIVISION_MODE_SEPARATE_SCHEMA,
        ])) {
            return;
        }

        $config = $this->connection->generateConfigurationArray($event->website);

        $this->configureHost($config);

        $this->emitEvent(
            new Events\Database\Deleting($config, $event->website)
        );

        if (!$this->factory->create($config['driver'])->deleted($event, $config, $this->connection)) {
            throw new GeneratorFailedException("Could not delete database {$config['database']}, the statement failed.");
        }

        $this->emitEvent(
            new Events\Database\Deleted($config, $event->website)
        );
    }

    /**
     * @param Events\Websites\Updated $event
     * @throws GeneratorFailedException
     */
    public function updated(Events\Websites\Updated $event)
    {
        if (!config('tenancy.db.auto-rename-tenant-database', false)) {
            return;
        }

        if (!in_array($this->mode, [
            Connection::DIVISION_MODE_SEPARATE_DATABASE,
            Connection::DIVISION_MODE_SEPARATE_SCHEMA,
        ])) {
            return;
        }

        $uuid = Arr::get($event->dirty, 'uuid');

        if (!$uuid) {
            return;
        }

        $config = $this->connection->generateConfigurationArray($event->website);

        $this->configureHost($config);

        $this->emitEvent(
            new Events\Database\Renaming($config, $event->website)
        );

        if (!$this->factory->create($config['driver'])->updated($event, $config, $this->connection)) {
            throw new GeneratorFailedException("Could not rename database {$config['database']}, the statement failed.");
        }

        $this->emitEvent(
            new Events\Database\Renamed($config, $event->website)
        );
    }

    public function keyUpdated(Events\KeyUpdated $event)
    {
        if (!in_array($this->mode, [
            Connection::DIVISION_MODE_SEPARATE_DATABASE,
            Connection::DIVISION_MODE_SEPARATE_SCHEMA,
        ])) {
            return;
        }

        app(WebsiteRepository::class)->query()->get()->each(function ($website) {
            $config = $this->connection->generateConfigurationArray($website);

            $this->configureHost($config);

            if (!$this->factory->create($config['driver'])->updatePassword($website, $config, $this->connection)) {
                throw new GeneratorFailedException("Could not update user {$config['username']} password, one of the statements failed.");
            }
        });
    }
}
