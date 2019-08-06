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

namespace Hyn\Tenancy\Tests\Extend;

use Hyn\Tenancy\Queue\TenantAwareJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class JobExtend implements ShouldQueue
{
    use TenantAwareJob, InteractsWithQueue, Dispatchable;
    /**
     * Get the raw body of the job.
     *
     * @return string
     */
    public function handle()
    {
        // ..
    }
}
