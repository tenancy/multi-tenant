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

namespace Hyn\Tenancy\Tests\Filesystem;

use Hyn\Tenancy\Tests\Test;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Str;

class DirectoryGeneratorTest extends Test
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    protected function duringSetUp(Application $app)
    {
        $this->setUpWebsites();
        $this->setUpHostnames();

        $this->filesystem = app('tenant.disk');
    }

    /**
     * @test
     */
    public function directory_created()
    {
        $this->assertFalse($this->filesystem->exists($this->website->uuid));

        $this->websites->create($this->website);

        $this->assertTrue(
            $this->filesystem->exists($this->website->uuid),
            "Failed to generate directory for website {$this->website->uuid}."
        );
    }

    /**
     * @test
     * @depends directory_created
     */
    public function directory_modified()
    {
        $this->website->uuid = Str::random(16);
        $this->website = $this->websites->update($this->website);

        $this->assertTrue($this->filesystem->exists($this->website->uuid));
    }

    /**
     * @test
     * @depends directory_modified
     */
    public function directory_deleted()
    {
        $this->websites->delete($this->website);

        $this->assertFalse($this->filesystem->exists($this->website->uuid));
    }
}
