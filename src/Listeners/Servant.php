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

namespace Hyn\Tenancy\Listeners;

use Hyn\Tenancy\Abstracts\AbstractEvent;
use Hyn\Tenancy\Contracts\Generator\GeneratesConfiguration;
use Hyn\Tenancy\Contracts\Generator\SavesToPath;
use Hyn\Tenancy\Contracts\Webserver\ReloadsServices;
use Hyn\Tenancy\Events;
use Hyn\Tenancy\Traits\DispatchesEvents;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Class Servant
 * @package Hyn\Tenancy\Listeners
 *
 * Servant takes care of registering and configuring webserver services.
 */
class Servant
{
    use DispatchesEvents;

    /**
     * @var FilesystemManager
     */
    protected $filesystemManager;

    public function __construct(FilesystemManager $filesystemManager)
    {
        $this->filesystemManager = $filesystemManager;
    }

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(
            [Events\Websites\Created::class, Events\Hostnames\Attached::class],
            [$this, 'generate']
        );
        $events->listen(Events\Websites\Updated::class, [$this, 'touch']);
        $events->listen(Events\Websites\Deleted::class, [$this, 'delete']);
    }

    /**
     * @param AbstractEvent|Events\Hostnames\Attached|Events\Websites\Created $event
     */
    public function generate(AbstractEvent $event)
    {
        $this->each(function ($generator, $service, $config) use ($event) {
            $contents = $path = null;

            if ($generator instanceof GeneratesConfiguration) {
                $contents = $generator->generate($event->website);
            }

            if ($generator instanceof SavesToPath) {
                $path = $generator->targetPath($event->website);
            }

            if ($path && $contents && $this->writeFileToDisk($path, $contents, $service, $config)) {
                $this->emitEvent(
                    (new Events\Webservers\ConfigurationSaved($event->website, $service))
                        ->setConfiguration($contents)
                        ->setPath($path)
                );
            }

            if ($generator instanceof ReloadsServices) {
                $generator->reload();
            }
        });
    }

    /**
     * @param Events\Websites\Updated $event
     */
    public function touch(Events\Websites\Updated $event)
    {
        if ($event->website->isDirty('uuid')) {
            $this->each(function ($generator, $service, $config) use ($event) {
                $path = null;

                if ($generator instanceof SavesToPath) {
                    $original = $event->website->newInstance();
                    $original->setRawAttributes($event->website->getOriginal());
                    $path = $generator->targetPath($original);
                }

                if ($path) {
                    $filesystem = $this->serviceFilesystem($service, $config);
                    $filesystem->delete($path);
                }

                if ($generator instanceof ReloadsServices) {
                    $generator->reload();
                }
            });
        }

        if ($event->website->isDirty()) {
            $this->generate($event);
        }
    }

    /**
     * @param Events\Websites\Deleted $event
     */
    public function delete(Events\Websites\Deleted $event)
    {
        $this->each(function ($generator, $service, $config) use ($event) {
            $path = null;

            if ($generator instanceof SavesToPath) {
                $path = $generator->targetPath($event->website);
            }

            if ($path) {
                $filesystem = $this->serviceFilesystem($service, $config);
                $filesystem->delete($path);
            }

            if ($generator instanceof ReloadsServices) {
                $generator->reload();
            }
        });
    }

    /**
     * @param string $path
     * @param string $contents
     * @param string $service
     * @param array $config
     * @return bool
     */
    protected function writeFileToDisk(string $path, string $contents, string $service, array $config = []): bool
    {
        $filesystem = $this->serviceFilesystem($service, $config);

        if (!$filesystem->exists(dirname($path)) && dirname($path) != '.') {
            $filesystem->makeDirectory(dirname($path));
        }

        return $filesystem->put($path, $contents);
    }

    /**
     * @param $service
     * @param array $config
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function serviceFilesystem($service, array $config)
    {
        return $this->filesystemManager->disk(Arr::get($config, 'disk') ?? "tenancy-webserver-$service");
    }

    /**
     * @param $callable
     */
    public function each($callable)
    {
        $this->services()->each(function (array $config, string $service) use ($callable) {
            $generator = $this->generator($config);

            $callable($generator, $service, $config);
        });
    }

    /**
     * @param array $config
     * @return mixed
     */
    protected function generator(array $config)
    {
        return app(Arr::get($config, 'generator'));
    }

    /**
     * @return Collection
     */
    public function services(): Collection
    {
        return collect(config('webserver', []))
            ->filter(function ($service) {
                return
                    Arr::get($service, 'enabled', false) &&
                    Arr::get($service, 'generator', false);
            });
    }
}
