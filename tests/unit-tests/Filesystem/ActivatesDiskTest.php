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

namespace Hyn\Tenancy\Tests\Filesystem;

use Hyn\Tenancy\Tests\Test;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\FilesystemManager;
use InvalidArgumentException;

class ActivatesDiskTest extends Test
{
    /**
     * @var FilesystemManager
     */
    protected $files;

    protected function duringSetUp(Application $app)
    {
        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);
        $this->files = $app->make('filesystem');
    }

    /**
     * @test
     */
    public function sets_the_disk_during_switch()
    {
        $this->activateTenant();
        try {
            /** @var \Illuminate\Contracts\Filesystem\Filesystem $disk */
            $disk = $this->files->disk('tenant');
        } catch (InvalidArgumentException $e) {
            $this->fail("Disk 'tenant' not configured");
        }

        $this->assertTrue($disk->put('foo', 'bar'));
        $this->assertTrue($disk->exists('foo'));
    }
}
