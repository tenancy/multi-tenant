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

namespace Hyn\Tenancy\Events\Database;

use Hyn\Tenancy\Abstracts\AbstractEvent;
use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Contracts\Website;

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
     * @var Website
     */
    public $website;

    /**
     * @param string     $mode
     * @param array      $configuration
     * @param Connection $connection
     * @param Website    $website
     */
    public function __construct(string &$mode, array &$configuration, Connection &$connection, Website $website)
    {
        $this->mode = &$mode;
        $this->configuration = &$configuration;
        $this->connection = &$connection;
        $this->website = $website;
    }
}
