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
use Hyn\Tenancy\Environment;
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

    /**
     * The shared root is not the isolation boundary: the tenant's uuid is.
     *
     * @test
     */
    public function the_disk_is_rooted_in_the_active_tenants_own_directory()
    {
        $this->activateTenant();

        $this->assertStringEndsWith(
            '/'.$this->website->uuid,
            config('filesystems.disks.tenant.root'),
            'The tenant disk is not rooted in the active tenant.'
        );
    }

    /**
     * @test
     */
    public function switching_tenant_repoints_the_disk()
    {
        $this->activateTenant();
        $other = $this->getReplicatedWebsite();

        $this->files->disk('tenant')->put('only-the-first.txt', 'x');

        app(Environment::class)->tenant($other);

        $this->assertStringEndsWith(
            '/'.$other->uuid,
            config('filesystems.disks.tenant.root'),
            'The disk kept pointing at the previous tenant after switching.'
        );

        $this->assertFalse(
            $this->files->disk('tenant')->exists('only-the-first.txt'),
            "Tenant {$other->uuid} can see a file written by {$this->website->uuid}."
        );
    }

    /**
     * The filesystem counterpart of releasing the connection: an idle worker
     * must not still be pointed at the last customer's directory.
     *
     * @test
     */
    public function releasing_the_tenant_leaves_no_disk_pointed_at_it()
    {
        $this->activateTenant();
        $uuid = $this->website->uuid;

        app(Environment::class)->forgetTenant();

        $this->assertNotEquals(
            $uuid,
            basename((string) config('filesystems.disks.tenant.root')),
            "The tenant disk is still rooted in {$uuid} after it was released."
        );
    }
}
