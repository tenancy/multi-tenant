<?php

namespace Hyn\Tenancy\Abstracts;

use Hyn\Tenancy\Events\Hostnames\Identified;
use Hyn\Tenancy\Models\Website;
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
            $events->listen(Identified::class, [$this, 'proxy']);
        }
    }

    /**
     * @param Identified $event
     */
    public function proxy(Identified $event)
    {
        if ($event->hostname->website) {
            $this->directory->setWebsite($event->hostname->website);
        } elseif ($this->requiresWebsite) {
            return;
        }

        if ($this->requiresPath || !$this->exists()) {
            return;
        }

        $result = $this->load($event);

        // Possible after processing.

        return $result;
    }

    /**
     * @param Identified $event
     * @return void
     */
    abstract public function load(Identified $event);

    /**
     * @return bool
     */
    protected function exists(): bool
    {
        if (!$this->website) {
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
