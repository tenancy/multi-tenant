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
 * @see https://hyn.me
 * @see https://patreon.com/tenancy
 */

namespace Hyn\Tenancy\Listeners\Filesystem;

use Hyn\Tenancy\Abstracts\AbstractTenantDirectoryListener;
use Hyn\Tenancy\Events\Hostnames\Identified;

class LoadsConfigs extends AbstractTenantDirectoryListener
{
    protected $configBaseKey = 'tenancy.folders.config';

    /**
     * @var string
     */
    protected $path = 'config';

    /**
     * @param Identified $event
     */
    public function load(Identified $event)
    {
        $this->readConfigurationFiles($this->path);
    }

    /**
     * @param string $path
     */
    protected function readConfigurationFiles(string $path)
    {
        foreach ($this->directory->files($path) as $file) {
            $key = basename($file, '.php');


            // Blacklisted; skip.
            if (in_array($key, $this->config->get('tenancy.folders.config.blacklist', []))) {
                continue;
            }

            if ($this->directory->isLocal()) {
                $values = $this->directory->getRequire($file);
            } else {
                $values = include 'data:text/plain,' . $this->directory->get($file);
            }

            $existing = $this->config->get($key, []);

            $this->config->set($key, array_merge_recursive(
                $existing,
                $values
            ));
        }
    }
}
