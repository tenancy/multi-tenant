<?php

namespace Hyn\Tenancy\Tests\Website;

use Hyn\Tenancy\Tests\Test;
use Hyn\Tenancy\Website\Directory;
use Illuminate\Contracts\Foundation\Application;

class DirectoryTest extends Test
{
    /**
     * @var Directory
     */
    protected $directory;

    protected function duringSetUp(Application $app)
    {
        $this->directory = $app->make(Directory::class);
    }

    /**
     * @test
     * @covers \Hyn\Tenancy\Website\Directory::setWebsite
     * @covers \Hyn\Tenancy\Website\Directory::getWebsite
     */
    public function can_switch_website()
    {
        $this->setUpWebsites(true);

        $this->assertNull($this->directory->getWebsite());

        $this->directory->setWebsite($this->website);

        $this->assertEquals($this->website, $this->directory->getWebsite());
    }
}
