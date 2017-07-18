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

namespace Hyn\Tenancy\Generators\Webserver\Vhost;

use Hyn\Tenancy\Contracts\Webserver\ReloadsServices;
use Hyn\Tenancy\Contracts\Webserver\VhostGenerator;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Website\Directory;

class ApacheGenerator implements VhostGenerator, ReloadsServices
{
    /**
     * @var Directory
     */
    private $directory;

    function __construct(Directory $directory)
    {
        $this->directory = $directory;
    }

    /**
     * @param Website $website
     * @return string
     */
    public function generate(Website $website): string
    {
        return view('tenancy.generators::webserver.apache.vhost', [
            'website' => $website,
            'config' => config('webserver.apache2', []),
            'directory' => $this->directory
        ]);
    }

    /**
     * @param Website $website
     * @return string
     */
    public function targetPath(Website $website): string
    {
        return "{$website->uuid}.conf";
    }

    /**
     * @return bool
     */
    public function reload(): bool
    {
        $success = null;

        if ($this->testConfiguration()) {
            exec(config('webserver.apache2.paths.actions.reload'), $_, $success);
        }

        return $success;
    }

    /**
     * @return bool
     */
    public function testConfiguration(): bool
    {
        exec(config('webserver.apache2.paths.actions.test-config'), $_, $success);

        return $success;
    }
}
