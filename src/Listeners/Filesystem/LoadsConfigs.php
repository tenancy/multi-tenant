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

namespace Hyn\Tenancy\Listeners\Filesystem;

use Hyn\Tenancy\Abstracts\AbstractTenantDirectoryListener;
use Hyn\Tenancy\Abstracts\HostnameEvent;
use Illuminate\Support\Arr;

class LoadsConfigs extends AbstractTenantDirectoryListener
{
    protected $configBaseKey = 'tenancy.folders.config';

    /**
     * @var string
     */
    protected $path = 'config';

    /**
     * @param HostnameEvent $event
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function load(HostnameEvent $event)
    {
        $this->readConfigurationFiles($this->path);
    }

    /**
     * @param string $path
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function readConfigurationFiles(string $path)
    {
        foreach ($this->directory->files($path) as $file) {
            $key = basename($file, '.php');


            // Blacklisted; skip.
            if (\in_array($key, $this->config->get('tenancy.folders.config.blacklist', []), true)) {
                continue;
            }

            if ($this->directory->isLocal()) {
                $values = $this->directory->getRequire($file);
            } else {
                /** @noinspection PhpIncludeInspection */
                $values = include 'data:text/plain,' . $this->directory->get($file);
            }

            $values = Arr::dot($values, "{$key}.");

            $this->config->set($values);
        }
    }
}
