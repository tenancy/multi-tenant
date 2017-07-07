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
 * @see https://hyn.me
 * @see https://patreon.com/tenancy
 */

namespace Hyn\Tenancy\Tests;

use Illuminate\Contracts\Foundation\Application;

class WebsiteRepositoryTest extends Test
{

    /**
     * @test
     * @covers \Hyn\Tenancy\Repositories\WebsiteRepository::create
     * @covers \Hyn\Tenancy\Contracts\Repositories\WebsiteRepository::create
     */
    public function creates_website()
    {
        $this->websites->create($this->website);

        $this->assertTrue($this->website->exists);
    }

    protected function duringSetUp(Application $app)
    {
        $this->setUpWebsites();
        $this->setUpHostnames();
    }
}
