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
 *
 */

namespace Hyn\Tenancy\Listeners;

use Hyn\Tenancy\Contracts\Generator\GeneratesConfiguration;
use Hyn\Tenancy\Contracts\Generator\SavesToPath;
use Hyn\Tenancy\Traits\DispatchesEvents;
use Illuminate\Contracts\Events\Dispatcher;
use Hyn\Tenancy\Events;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Servant
{
    use DispatchesEvents;

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Events\Websites\Created::class, [$this, 'generate']);
        $events->listen(Events\Websites\Deleted::class, [$this, 'delete']);
    }

    /**
     * @param Events\Websites\Created $event
     */
    public function generate(Events\Websites\Created $event)
    {
        $this->each(function ($generator, $service, $config) use ($event) {
            $contents = $path = null;

            if ($generator instanceof GeneratesConfiguration) {
                $contents = $generator->generate($event->website);
            }

            if ($generator instanceof SavesToPath) {
                $path = $generator->targetPath($event->website);
            }

            if ($path && $contents && $this->writeFileToDisk($path, $contents, $config)) {
                $this->emitEvent(
                    (new Events\Webservers\ConfigurationSaved($event->website, $service))
                        ->setConfiguration($contents)
                        ->setPath($path)
                );
            }
        });
    }

    /**
     * @param string $path
     * @param string $contents
     * @param array $config
     * @return bool
     */
    protected function writeFileToDisk(string $path, string $contents, array $config = []): bool
    {
        $filesystem = $this->serviceFilesystem($config);

        $directory = $filesystem->exists(dirname($path));

        if (!$directory && dirname($path) != '.') {
            $directory = $filesystem->makeDirectory(dirname($path));
        }

        return $directory && $filesystem->put($path, $contents);
    }

    /**
     * @param array $config
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function serviceFilesystem(array $config)
    {
        /** @var \Illuminate\Filesystem\FilesystemManager $manager */
        $manager = app('filesystem');

        return $manager->disk(Arr::get($config, 'disk'));
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
                $filesystem = $this->serviceFilesystem($config);
                $filesystem->delete($path);
            }
        });
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
    protected function services(): Collection
    {
        return collect(config('webserver', []))
            ->filter(function ($service) {
                return Arr::get($service, 'enabled', false) &&
                    Arr::get($service, 'generator');
            });
    }
}
