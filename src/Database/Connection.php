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

namespace Hyn\Tenancy\Database;

use Hyn\Tenancy\Contracts\Database\PasswordGenerator;
use Hyn\Tenancy\Events\Database\ConfigurationLoading;
use Hyn\Tenancy\Exceptions\ConnectionException;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Traits\DispatchesEvents;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\DatabaseManager;
use Hyn\Tenancy\Events;
use Illuminate\Support\Str;

class Connection
{
    use DispatchesEvents;

    const DEFAULT_SYSTEM_NAME = 'system';
    const DEFAULT_TENANT_NAME = 'tenant';
    const DEFAULT_MIGRATION_NAME = 'tenant-migration';

    const DIVISION_MODE_SEPARATE_DATABASE = 'database';
    const DIVISION_MODE_SEPARATE_PREFIX = 'prefix';

    /**
     * Allows manually setting the configuration during event callbacks.
     */
    const DIVISION_MODE_BYPASS = 'bypass';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var PasswordGenerator
     */
    protected $passwordGenerator;
    /**
     * @var Dispatcher
     */
    protected $events;
    /**
     * @var ConnectionResolverInterface
     */
    protected $connection;
    /**
     * @var DatabaseManager
     */
    private $db;
    /**
     * @var Kernel
     */
    protected $artisan;

    /**
     * Connection constructor.
     * @param Config $config
     * @param PasswordGenerator $passwordGenerator
     * @param DatabaseManager $db
     * @param Kernel $artisan
     */
    public function __construct(
        Config $config,
        PasswordGenerator $passwordGenerator,
        DatabaseManager $db,
        Kernel $artisan
    ) {
        $this->config = $config;
        $this->passwordGenerator = $passwordGenerator;
        $this->db = $db;
        $this->artisan = $artisan;

        $this->enforceDefaultConnection();
    }

    protected function enforceDefaultConnection()
    {
        if ($default = $this->config->get('tenancy.db.default')) {
            $this->config->set('database.default', $default);
        }
    }

    /**
     * Gets the currently active tenant connection.
     *
     * @return \Illuminate\Database\Connection
     */
    public function get()
    {
        return $this->db->connection($this->tenantName());
    }

    /**
     * @param Hostname|Website $to
     * @param null $connection
     * @return bool
     */
    public function set($to, $connection = null): bool
    {
        $connection = $connection ?? $this->tenantName();

        $website = null;

        if ($to instanceof Hostname) {
            $website = $to->website;
        }

        if ($to instanceof Website) {
            $website = $to;
        }

        if ($website) {
            // Sets current connection settings.
            $this->config->set(
                sprintf('database.connections.%s', $connection),
                $this->generateConfigurationArray($website)
            );
        }

        // Purges the old connection.
        $this->db->purge(
            $connection
        );

        if ($website) {
            $this->db->reconnect(
                $connection
            );
        }

        return true;
    }

    /**
     * Gets the system connection.
     *
     * @return \Illuminate\Database\Connection
     */
    public function system()
    {
        return $this->db->connection($this->systemName());
    }

    /**
     * @return string
     */
    public function systemName(): string
    {
        return $this->config->get('tenancy.db.system-connection-name', static::DEFAULT_SYSTEM_NAME);
    }

    /**
     * @return string
     */
    public function tenantName(): string
    {
        return $this->config->get('tenancy.db.tenant-connection-name', static::DEFAULT_TENANT_NAME);
    }

    /**
     * @return string
     */
    public function migrationName(): string
    {
        return $this->config->get('tenancy.db.migration-connection-name', static::DEFAULT_MIGRATION_NAME);
    }

    /**
     * Purges the current tenant connection.
     * @param null $connection
     */
    public function purge($connection = null)
    {
        $connection = $connection ?? $this->tenantName();

        $this->db->purge(
            $connection
        );

        $this->config->set(
            sprintf('database.connections.%s', $connection),
            []
        );
    }

    /**
     * @param Hostname|Website $for
     * @param string|null $path
     * @return bool
     */
    public function migrate($for, string $path = null)
    {
        $this->set($for, $this->migrationName());

        if ($path) {
            $path = realpath($path);
        }

        $options = [
            '--database' => $this->migrationName(),
            '-n' => 1
        ];

        if ($path) {
            $options['--realpath'] = $path;
        }

        $code = $this->artisan->call('migrate', $options);

        $this->purge($this->migrationName());

        return $code === 0;
    }

    /**
     * @param Website $website
     * @return array
     * @throws ConnectionException
     */
    public function generateConfigurationArray(Website $website): array
    {
        $clone = config(sprintf(
            'database.connections.%s',
            $this->systemName()
        ));

        $mode = config('tenancy.db.tenant-division-mode');

        $this->emitEvent(new ConfigurationLoading($mode, $clone, $this));

        switch ($mode) {
            case static::DIVISION_MODE_SEPARATE_DATABASE:
                $clone['username'] = $clone['database'] = $website->uuid;
                $clone['password'] = $this->passwordGenerator->generate($website);
                break;
            case static::DIVISION_MODE_SEPARATE_PREFIX:
                $clone['prefix'] = sprintf('%d_', $website->id);
                break;
            case static::DIVISION_MODE_BYPASS:
                break;
            default:
                throw new ConnectionException("Division mode '$mode' unknown.");
        }

        return $clone;
    }
}
