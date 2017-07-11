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

namespace Hyn\Tenancy\Events\Database;

use Hyn\Tenancy\Abstracts\AbstractEvent;
use Hyn\Tenancy\Database\Connection;

class ConfigurationLoading extends AbstractEvent
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
    public function __construct(string &$mode, array &$configuration, Connection &$connection)
    {
        $this->mode = &$mode;
        $this->configuration = &$configuration;
        $this->connection = &$connection;
    }
}
