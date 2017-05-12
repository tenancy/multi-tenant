<?php

namespace Hyn\Tenancy\Abstracts;

use Hyn\Tenancy\Events\Hostnames\Identified;
use Hyn\Tenancy\Models\Website;
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

    public function __construct(Filesystem $filesystem, Repository $config)
    {
        $this->filesystem = $filesystem;
        $this->config = $config;
    }

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        if ($this->config->get("{$this->configBaseKey}.enabled")) {
            $events->listen(Identified::class, [$this, 'load']);
        }
    }

    /**
     * @param Identified $event
     * @return void
     */
    abstract public function load(Identified $event);

    /**
     * @param Website|null $website
     * @return bool
     */
    protected function exists(Website $website = null): bool
    {
        if (!$website) {
            return false;
        }

        return $this->filesystem->exists($this->path($website));
    }

    /**
     * @param Website $website
     * @return string
     */
    protected function path(Website $website)
    {
        return sprintf(
            "%s%s%s",
            $website->uuid,
            DIRECTORY_SEPARATOR,
            $this->path
        );
    }
}
