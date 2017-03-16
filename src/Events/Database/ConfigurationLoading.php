<?php

namespace Hyn\Tenancy\Events\Database;

use Hyn\Tenancy\Database\Connection;

class ConfigurationLoading
{
    /**
     * @var string
     */
    public $mode;

    /**
     * @var array
     */
    public $configuration;

    /**
     * @var Connection
     */
    public $connection;

    /**
     * @param string $mode
     * @param array $configuration
     * @param Connection $connection
     */
    public function __construct(string $mode, array $configuration, Connection $connection)
    {
        $this->mode = $mode;
        $this->configuration = $configuration;
        $this->connection = $connection;
    }
}
