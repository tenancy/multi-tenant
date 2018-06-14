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

use Hyn\Tenancy\Abstracts\WebsiteEvent;
use Hyn\Tenancy\Events\Websites\Identified;
use Hyn\Tenancy\Events\Websites\Switched;
use Hyn\Tenancy\Contracts\Website;
use Hyn\Tenancy\Website\Directory;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class ActivatesDisk
{
    /**
     * @var Directory
     */
    protected $directory;

    /**
     * @var FilesystemManager
     */
    private $filesystem;

    public function __construct(Directory $directory, Factory $filesystem)
    {
        $this->directory = $directory;
        $this->filesystem = $filesystem;
    }

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen([Identified::class, Switched::class], [$this, 'activate']);
    }

    /**
     * @param WebsiteEvent $event
     */
    public function activate(WebsiteEvent $event)
    {
        if ($event->website) {
            $this->filesystem->set('tenant', $this->resolve($event->website));
        }
    }

    /**
     * Resolve the given disk.
     *
     * @param Website $website
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function resolve(Website $website)
    {
        $config = config('filesystems.disks.' . (config('tenancy.website.disk') ?? 'tenancy-default'));
        Arr::set($config, 'root', Arr::get($config, 'root') . '/' .$website->uuid);

        $driverMethod = 'create'.ucfirst($config['driver']).'Driver';

        if (method_exists($this->filesystem, $driverMethod)) {
            return $this->filesystem->{$driverMethod}($config);
        } else {
            throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
        }
    }
}
