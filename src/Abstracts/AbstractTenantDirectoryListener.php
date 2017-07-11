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

namespace Hyn\Tenancy\Abstracts;

use Hyn\Tenancy\Events\Hostnames\Identified;
use Hyn\Tenancy\Events\Hostnames\Switched;
use Hyn\Tenancy\Website\Directory;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Filesystem\Filesystem;

abstract class AbstractTenantDirectoryListener
{

    /**
     * @var string
     */
    protected $configBaseKey;

    /**
     * @var string
     */
    protected $path;
    /**
     * @var Filesystem
     */
    protected $filesystem;
    /**
     * @var Repository
     */
    protected $config;
    /**
     * @var Directory
     */
    protected $directory;

    /**
     * Event has to have a Website object to work.
     *
     * @var bool
     */
    protected $requiresWebsite = true;

    /**
     * Path has to exist in tenant directory.
     *
     * @var bool
     */
    protected $requiresPath = true;

    public function __construct(Filesystem $filesystem, Repository $config, Directory $directory)
    {
        $this->filesystem = $filesystem;
        $this->config = $config;
        $this->directory = $directory;
    }

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        if ($this->config->get("{$this->configBaseKey}.enabled")) {
            $events->listen([Identified::class, Switched::class], [$this, 'proxy']);
        }
    }

    /**
     * Proxies fired events to configure the handler.
     * @param HostnameEvent $event
     */
    public function proxy(HostnameEvent $event)
    {
        if ($event->hostname && $event->hostname->website) {
            $this->directory->setWebsite($event->hostname->website);
        } elseif ($this->requiresWebsite) {
            return;
        }

        if ($this->requiresPath && !$this->exists()) {
            return;
        }

        $result = $this->load($event);

        // Possible after processing.

        return $result;
    }

    /**
     * @param HostnameEvent $event
     * @return void
     */
    abstract public function load(HostnameEvent $event);

    /**
     * @return bool
     */
    protected function exists(): bool
    {
        if (!$this->directory->getWebsite()) {
            return false;
        }

        return $this->directory->exists($this->path);
    }

    /**
     * @return string
     */
    protected function path()
    {
        return $this->directory->path($this->path);
    }
}
