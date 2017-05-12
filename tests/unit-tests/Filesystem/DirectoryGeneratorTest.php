<?php

namespace Hyn\Tenancy\Tests\Filesystem;

use Hyn\Tenancy\Tests\Test;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Filesystem\Filesystem;

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
        $this->websites->create($this->website);

        $this->assertTrue($this->filesystem->exists($this->website->uuid));
    }
}
