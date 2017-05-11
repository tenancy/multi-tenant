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

namespace Hyn\Tenancy\Generators\Webserver\Vhost;

use Hyn\Tenancy\Contracts\Webserver\ReloadsServices;
use Hyn\Tenancy\Contracts\Webserver\VhostGenerator;
use Hyn\Tenancy\Models\Website;

class ApacheGenerator implements VhostGenerator, ReloadsServices
{
    /**
     * @param Website $website
     * @return string
     */
    public function generate(Website $website): string
    {
        return view('tenancy.generator::webserver.apache.vhost', compact('website'));
    }

    /**
     * @param Website $website
     * @return string
     */
    public function targetPath(Website $website): string
    {
        return sprintf(
            "%s/apache/{$website->uuid}.conf",
            config('webserver.apache2.paths.tenant-files') ?? storage_path('tenancy/webserver')
        );
    }

    /**
     * @return bool
     */
    public function reload(): bool
    {
        $success = null;

        if ($this->testConfiguration()) {
            exec('webserver.apache2.paths.actions.reload', $_, $success);
        }

        return $success;
    }

    /**
     * @return bool
     */
    public function testConfiguration(): bool
    {
        exec('webserver.apache2.paths.actions.test-config', $_, $success);

        return $success;
    }
}
