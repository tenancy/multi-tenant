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
        $this->each(function ($generator, $service) use ($event) {
            $contents = $path = null;

            if ($generator instanceof GeneratesConfiguration) {
                $contents = $generator->generate($event->website);
            }

            if ($generator instanceof SavesToPath) {
                $path = $generator->targetPath($event->website);
            }

            if ($path && $contents) {
                $this->directory($path);

                file_put_contents($path, $contents);

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
     * @return bool
     */
    protected function directory(string $path): bool
    {
        $dir = dirname($path);

        if (!is_dir($dir)) {
            return mkdir($dir, 0777, true);
        }

        return is_dir($dir);
    }

    /**
     * @param Events\Websites\Deleted $event
     */
    public function delete(Events\Websites\Deleted $event)
    {
        $this->each(function ($generator, $service) use ($event) {
            $path = null;

            if ($generator instanceof SavesToPath) {
                $path = $generator->targetPath($event->website);
            }

            if ($path) {
                unlink($path);
            }
        });
    }

    /**
     * @param $callable
     */
    protected function each($callable)
    {
        $this->services()->each(function (array $config, string $service) use ($callable) {
            $generator = $this->generator($config);

            $callable($generator, $service);
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
