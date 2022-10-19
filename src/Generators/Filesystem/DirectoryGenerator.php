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

namespace Hyn\Tenancy\Generators\Filesystem;

use Hyn\Tenancy\Events\Filesystem\DirectoryCreated;
use Hyn\Tenancy\Events\Filesystem\DirectoryDeleted;
use Hyn\Tenancy\Events\Filesystem\DirectoryRenamed;
use Hyn\Tenancy\Traits\DispatchesEvents;
use Illuminate\Contracts\Events\Dispatcher;
use Hyn\Tenancy\Events\Websites as Events;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Arr;

class DirectoryGenerator
{
    use DispatchesEvents;

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Events\Created::class, [$this, 'created']);
        $events->listen(Events\Updated::class, [$this, 'updated']);
        $events->listen(Events\Deleted::class, [$this, 'deleted']);
    }

    /**
     * @return Filesystem
     */
    protected function filesystem(): Filesystem
    {
        return app('tenancy.disk');
    }

    /**
     * Mutates the service based on a website being enabled.
     *
     * @param Events\Created $event
     * @return bool
     */
    public function created(Events\Created $event): bool
    {
        if (config('tenancy.website.auto-create-tenant-directory')) {
            $stat = $this->filesystem()->makeDirectory($event->website->uuid);

            if ($stat) {
                $this->emitEvent(
                    new DirectoryCreated($event->website, $this->filesystem())
                );
            }

            return $stat;
        }

        return true;
    }

    /**
     * @param Events\Updated $event
     * @return bool
     */
    public function updated(Events\Updated $event): bool
    {
        $rename = config('tenancy.website.auto-rename-tenant-directory');
        if ($rename && ($uuid = Arr::get($event->dirty, 'uuid')) && $this->filesystem()->exists($uuid)) {
            $stat = $this->filesystem()->move(
                $uuid,
                $event->website->uuid
            );

            if ($stat) {
                $this->emitEvent(
                    (new DirectoryRenamed($event->website, $this->filesystem()))
                        ->setOld($uuid)
                );
            }

            return $stat;
        }

        return true;
    }

    /**
     * Acts on this service whenever a website is disabled.
     *
     * @param Events\Deleted $event
     * @return bool
     */
    public function deleted(Events\Deleted $event): bool
    {
        if (config('tenancy.website.auto-delete-tenant-directory')) {
            $stat = $this->filesystem()->deleteDirectory($event->website->uuid);

            if ($stat) {
                $this->emitEvent(
                    new DirectoryDeleted($event->website, $this->filesystem())
                );
            }

            return $stat;
        }

        return true;
    }
}
