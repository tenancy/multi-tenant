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

namespace Hyn\Tenancy\Listeners\Filesystem;

use Hyn\Tenancy\Abstracts\WebsiteEvent;
use Hyn\Tenancy\Events\Websites\Identified;
use Hyn\Tenancy\Events\Websites\Switched;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Arr;

class ActivatesDisk
{
    /**
     * @var FilesystemManager
     */
    private $filesystem;

    public function __construct(Factory $filesystem)
    {
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
            $disk = config('tenancy.website.disk') ?? 'tenancy-default';

            $config = config('filesystems.disks.' . $disk);
            Arr::set($config, 'root', Arr::get($config, 'root') . '/' .$event->website->uuid);

            config(['filesystems.disks.tenant' => $config]);

            // Force flush the manager to resolve the disk anew when requested.
            $this->filesystem->set('tenant', null);
        }
    }
}
