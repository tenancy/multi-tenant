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

namespace Hyn\Tenancy\Providers\Tenants;

use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Environment;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\ServiceProvider;

class QueueProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app['queue']->createPayloadUsing(function (string $connection, string $queue = null, array $payload = []) {
            if (isset($payload['website_id'])) {
                return [];
            }

            /** @var Environment $environment */
            $environment = $this->app->make(Environment::class);
            $tenant = $environment->tenant();

            return $tenant ? ['website_id' => $tenant->id] : [];
        });

        $this->app['events']->listen(JobProcessing::class, function ($event) {
            if (isset($event->job->payload()['website_id'])) {
                /** @var Environment $environment */
                $environment = $this->app->make(Environment::class);
                /** @var WebsiteRepository $repository */
                $repository = $this->app->make(WebsiteRepository::class);

                $tenant = $repository->findById($event->job->payload()['website_id']);

                if ($tenant) {
                    $environment->tenant($tenant);
                }
            }
        });
    }
}
