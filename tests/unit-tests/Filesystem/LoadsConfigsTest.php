<?php

namespace Hyn\Tenancy\Tests\Filesystem;

use Hyn\Tenancy\Tests\Test;
use Illuminate\Contracts\Foundation\Application;

class LoadsConfigsTest extends Test
{

    protected function duringSetUp(Application $app)
    {
        $this->setUpWebsites();
        $this->setUpHostnames();
    }
    /**
     * @test
     */
    public function reads_additional_config()
    {
        $this->websites->create($this->website);

    }
}
