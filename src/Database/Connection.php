<?php

namespace Hyn\Tenancy\Database;

use Hyn\Tenancy\Contracts\Database\PasswordGenerator;
use Hyn\Tenancy\Contracts\ServiceMutation;
use Hyn\Tenancy\Contracts\Website\UuidGenerator;
use Hyn\Tenancy\Events\Database\ConfigurationLoading;
use Hyn\Tenancy\Exceptions\ConnectionException;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Traits\DispatchesEvents;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\DatabaseManager;

class Connection implements ServiceMutation
{
    use DispatchesEvents;

    const DEFAULT_SYSTEM_NAME = 'system';
    const DEFAULT_TENANT_NAME = 'tenant';

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
     * @var UuidGenerator
     */
    protected $uuidGenerator;

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
     * Connection constructor.
     * @param Config $config
     * @param UuidGenerator $uuidGenerator
     * @param PasswordGenerator $passwordGenerator
     * @param ConnectionResolverInterface $connection
     */
    public function __construct(
        Config $config,
        UuidGenerator $uuidGenerator,
        PasswordGenerator $passwordGenerator,
        ConnectionResolverInterface $connection
    ) {
        $this->config = $config;
        $this->uuidGenerator = $uuidGenerator;
        $this->passwordGenerator = $passwordGenerator;

        $this->enforceDefaultConnection();
        $this->connection = $connection;
    }

    protected function enforceDefaultConnection()
    {
        if ($default = $this->config->get('tenancy.db.default')) {
            $this->config->set('database.default', $default);
        }
    }

    /**
     * @return string
     */
    public function systemName() : string
    {
        return $this->config->get('tenancy.db.system-connection-name', static::DEFAULT_SYSTEM_NAME);
    }

    /**
     * @return string
     */
    public function tenantName() : string
    {
        return $this->config->get('tenancy.db.tenant-connection-name', static::DEFAULT_TENANT_NAME);
    }

    /**
     * Whenever a website is activated, trigger a service update.
     *
     * @param Hostname $hostname
     * @return bool
     */
    public function activate(Hostname $hostname) : bool
    {
        // TODO: Implement activate() method.
    }

    /**
     * Mutates the service based on a website being enabled.
     *
     * @param Hostname $hostname
     * @return bool
     */
    public function enable(Hostname $hostname) : bool
    {
        // TODO: Implement enable() method.
    }

    /**
     * Acts on this service whenever a website is disabled.
     *
     * @param Hostname $hostname
     * @return bool
     */
    public function disable(Hostname $hostname) : bool
    {
        // TODO: Implement disable() method.
    }

    /**
     * Reacts to this service when we switch the active tenant website.
     *
     * @param Hostname $from
     * @param Hostname $to
     * @return bool
     */
    public function switch(Hostname $from, Hostname $to) : bool
    {
        $this->config->set(
            sprintf('database.connections.%s', $this->tenantName()),
            $this->generateConfigurationArray($to->website)
        );

        $this->connection->purge(
            $this->tenantName()
        );
    }

    /**
     * @param Website $website
     * @return array
     * @throws ConnectionException
     */
    protected function generateConfigurationArray(Website $website) : array
    {
        $clone = config(sprintf(
            'database.connections.%s',
            $this->systemName()
        ));

        $mode = config('tenancy.db.tenant-division-mode');

        $this->emitEvent(new ConfigurationLoading($mode, $clone, $this));

        switch ($mode) {
            case static::DIVISION_MODE_SEPARATE_DATABASE:
                $clone['username'] = $clone['database'] = $this->uuidGenerator->generate($website);
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
