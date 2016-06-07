<?php

namespace Hyn\Framework\Commands;

use Illuminate\Bus\Queueable;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

abstract class AbstractCommand extends Command implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public function __construct()
    {
        parent::__construct();

        // set the queue if specified in the configuration file
        if (is_null($this->queue) && config('multi-tenant.queue.other')) {
            $this->onQueue(config('multi-tenant.queue.other'));
        }
    }
}
