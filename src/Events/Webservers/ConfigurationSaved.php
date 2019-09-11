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

namespace Hyn\Tenancy\Events\Webservers;

use Hyn\Tenancy\Abstracts\WebserverEvent;

class ConfigurationSaved extends WebserverEvent
{
    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $configuration;

    /**
     * @param mixed $configuration
     * @return ConfigurationSaved
     */
    public function setConfiguration(string $configuration)
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @param mixed $path
     * @return ConfigurationSaved
     */
    public function setPath(string $path)
    {
        $this->path = $path;
        return $this;
    }
}
