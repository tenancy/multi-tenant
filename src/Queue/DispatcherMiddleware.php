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

namespace Hyn\Tenancy\Queue;

use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;

class DispatcherMiddleware
{
    public function handle($command, $next)
    {
        $key = $command->website_id ?? null;

        if ($key) {
            $environment = resolve(Environment::class);

            $repository = resolve(WebsiteRepository::class);

            $tenant = $repository->findById($key);

            $environment->tenant($tenant);
        }

        return $next($command);
    }
}
