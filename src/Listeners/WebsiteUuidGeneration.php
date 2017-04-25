<?php

namespace Hyn\Tenancy\Listeners;

use Hyn\Tenancy\Contracts\Website\UuidGenerator;
use Hyn\Tenancy\Events\Websites\Creating;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;

class WebsiteUuidGeneration
{
    /**
     * @var Repository
     */
    private $config;
    /**
     * @var UuidGenerator
     */
    private $generator;

    /**
     * WebsiteUuidGeneration constructor.
     * @param Repository $config
     * @param UuidGenerator $generator
     */
    public function __construct(Repository $config, UuidGenerator $generator)
    {
        $this->config = $config;
        $this->generator = $generator;
    }

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Creating::class, [$this, 'addUuid']);
    }

    /**
     * @param Creating $event
     */
    public function addUuid(Creating $event)
    {
        if ($this->config->get('tenancy.website.disable-random-id') !== true) {
            $event->website->uuid = $this->generator->generate($event->website);
        }
    }
}
