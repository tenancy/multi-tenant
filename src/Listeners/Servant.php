<?php

namespace Hyn\Tenancy\Listeners;

use Hyn\Tenancy\Contracts\Generator\GeneratesConfiguration;
use Hyn\Tenancy\Contracts\Generator\SavesToPath;
use Illuminate\Contracts\Events\Dispatcher;
use Hyn\Tenancy\Events;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Servant
{
    /**
     * @var Collection
     */
    protected $services;

    /**
     * Servant constructor.
     */
    public function __construct()
    {
        $this->services = static::services();
    }

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
        $this->each(function ($generator) use ($event) {
            $contents = $path = null;

            if ($generator instanceof GeneratesConfiguration) {
                $contents = $generator->generate($event->website);
            }

            if ($generator instanceof SavesToPath) {
                $path = $generator->targetPath($event->website);
            }

            if ($path && $contents) {
                file_put_contents($path, $contents);
            }
        });
    }

    /**
     * @param Events\Websites\Deleted $event
     */
    public function delete(Events\Websites\Deleted $event)
    {
        $this->each(function ($generator) use ($event) {
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
        $this->services->each(function(array $config, string $service) use ($callable) {
            $generator = $this->generator($config);

            $callable($generator);
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
    protected static function services(): Collection
    {
        return collect(config('webserver', []))
            ->filter(function ($service) {
                return Arr::get($service, 'enabled', false) &&
                    Arr::get($service, 'generator');
            });
    }
}
