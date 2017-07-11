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

class ConfigurationLoaded extends AbstractEvent
{
    /**
     * @var array
     */
    public $configuration;

    /**
     * @var Connection
     */
    public $connection;

    /**
     * @param array $configuration
     * @param Connection $connection
     */
    public function __construct(array &$configuration, Connection &$connection)
    {
        $this->configuration = &$configuration;
        $this->connection = &$connection;
    }
}
