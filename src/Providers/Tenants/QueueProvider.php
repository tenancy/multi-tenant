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
use Hyn\Tenancy\Queue\DispatcherMiddleware;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class QueueProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->booted(function () {
            $this->app->extend('queue', function (QueueManager $queue) {
                $queue->createPayloadUsing(function (string $connection, ?string $queue = null, array $payload = []) {
                    /** @var Environment $environment */
                    $environment = resolve(Environment::class);

                    /** @var mixed|null $website_id */
                    $website_id = Arr::get($payload, 'data.command')->website_id ?? optional($environment->tenant())->getKey();

                    return ['website_id' => $website_id];
                });

                return $queue;
            });
        });

        $this->bindTenantToJobLifecycle();

        $this->app->make(Dispatcher::class)->pipeThrough([DispatcherMiddleware::class]);
    }

    /**
     * Tenants active before each job, restored once it ends.
     *
     * A worker hands one job after another to the same process, so a tenant
     * left active is inherited by whatever runs next. A stack rather than a
     * single value, since a synchronous job can dispatch another.
     *
     * @var array
     */
    protected $tenantStack = [];

    protected function bindTenantToJobLifecycle(): void
    {
        $events = $this->app['events'];

        $events->listen(JobProcessing::class, function (JobProcessing $event) {
            if ($this->runsInline($event->connectionName)) {
                return;
            }

            $environment = resolve(Environment::class);

            $this->tenantStack[] = $environment->tenant();

            $key = Arr::get($event->job->payload(), 'website_id');

            if (! $key) {
                // A job that declares no tenant is a system job. It must not
                // inherit the previous one.
                $this->releaseTenant($environment);

                return;
            }

            $tenant = resolve(WebsiteRepository::class)->findById($key);

            if (! $tenant) {
                // Deleted while the job sat in the queue. Resolving nothing
                // must not mean keeping whatever was active.
                $this->releaseTenant($environment);

                return;
            }

            $environment->tenant($tenant);
        });

        // An idle worker should hold no customer's connection.
        foreach ([JobProcessed::class, JobFailed::class, JobExceptionOccurred::class] as $event) {
            $events->listen($event, function ($event) {
                if ($this->runsInline($event->connectionName)) {
                    return;
                }

                $this->restoreTenant();
            });
        }
    }

    /**
     * Whether the job runs inline rather than on a worker.
     *
     * A synchronous dispatch dies with the request that asked for it, so no
     * next job inherits anything. It also has deliberate semantics of its own:
     * DispatcherMiddleware switches the ambient tenant and the suite asserts it
     * stays switched, which restoring here would undo.
     */
    protected function runsInline(?string $connection): bool
    {
        return $connection === 'sync';
    }

    /**
     * Put back whatever tenant was active before the job ran.
     */
    protected function restoreTenant(): void
    {
        if (! config('tenancy.queue.reset-tenant-between-jobs', true)) {
            array_pop($this->tenantStack);

            return;
        }

        $previous = array_pop($this->tenantStack);

        $environment = resolve(Environment::class);

        if ($previous) {
            $environment->tenant($previous);

            return;
        }

        $environment->forgetTenant();
    }

    /**
     * Release the active tenant, unless an application has opted out.
     *
     * Jobs that relied on an ambient tenant start failing with a connection
     * error rather than reaching the wrong database, so the old behaviour stays
     * available while an installation adapts.
     */
    protected function releaseTenant(Environment $environment): void
    {
        if (! config('tenancy.queue.reset-tenant-between-jobs', true)) {
            return;
        }

        $environment->forgetTenant();
    }
}
