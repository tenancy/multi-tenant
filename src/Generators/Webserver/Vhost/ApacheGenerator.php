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

namespace Hyn\Tenancy\Generators\Webserver\Vhost;

use Hyn\Tenancy\Contracts\Webserver\ReloadsServices;
use Hyn\Tenancy\Contracts\Webserver\VhostGenerator;
use Hyn\Tenancy\Contracts\Website;
use Hyn\Tenancy\Website\Directory;
use Symfony\Component\Process\Process;

class ApacheGenerator implements VhostGenerator, ReloadsServices
{
    /**
     * @var Directory
     */
    private $directory;

    public function __construct(Directory $directory)
    {
        $this->directory = $directory;
    }

    /**
     * @param Website $website
     * @return null|string
     */
    public function media(Website $website)
    {
        return $this->directory->setWebsite($website)->isLocal() && $this->directory->exists('media') ?
            $this->directory->path('media', true) :
            null;
    }

    /**
     * @param Website $website
     * @return string
     */
    public function generate(Website $website): string
    {
        return view(config('webserver.apache2.view'), [
            'website' => $website,
            'config' => config('webserver.apache2', []),
            'directory' => $this->directory->setWebsite($website),
            'media' => $this->media($website)
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
        if ($this->testConfiguration() && $reload = config('webserver.apache2.paths.actions.reload')) {
            return (Process::fromShellCommandline($reload))
                ->mustRun()
                ->isSuccessful();
        }

        return false;
    }

    /**
     * @return bool
     */
    public function testConfiguration(): bool
    {
        $test = config('webserver.apache2.paths.actions.test-config');

        if (is_bool($test)) {
            return $test;
        }

        return (Process::fromShellCommandline($test))
            ->mustRun()
            ->isSuccessful();
    }
}
