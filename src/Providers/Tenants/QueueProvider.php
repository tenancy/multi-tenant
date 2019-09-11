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

namespace Hyn\Tenancy\Providers\Tenants;

use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Environment;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Arr;
use Hyn\Tenancy\Queue\DispatcherMiddleware;
use Illuminate\Contracts\Bus\Dispatcher;

class QueueProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app['queue']->createPayloadUsing(function (string $connection, string $queue = null, array $payload = []) {
            /** @var Environment $environment */
            $environment = resolve(Environment::class);

            /** @var mixed|null $website_id */
            $website_id = Arr::get($payload, 'data.command')->website_id ?? optional($environment->tenant())->getKey();

            return ['website_id' => $website_id];
        });

        $this->app['events']->listen(JobProcessing::class, function ($event) {
            if ($key = Arr::get($event->job->payload(), 'website_id')) {
                /** @var Environment $environment */
                $environment = resolve(Environment::class);
                /** @var WebsiteRepository $repository */
                $repository = resolve(WebsiteRepository::class);

                $tenant = $repository->findById($key);

                $environment->tenant($tenant);
            }
        });
        
        $this->app->make(Dispatcher::class)->pipeThrough([DispatcherMiddleware::class]);
    }
}
