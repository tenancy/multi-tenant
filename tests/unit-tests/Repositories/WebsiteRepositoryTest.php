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

namespace Hyn\Tenancy\Tests;

use Illuminate\Contracts\Foundation\Application;

class WebsiteRepositoryTest extends Test
{
    /**
     * @test
     */
    public function creates_website()
    {
        $this->websites->create($this->website);

        $this->assertTrue($this->website->exists);
    }

    /**
     * @test
     */
    public function updates_website()
    {
        $this->setUpWebsites(true);

        $saved = $this->websites->update($this->website);

        $this->assertEquals($this->website->id, $saved->id);
    }

    /**
     * @test
     * @depends creates_website
     */
    public function deletes_website()
    {
        $this->setUpWebsites(true);

        $this->websites->delete($this->website);

        $this->assertTrue($this->website->exists);
        $this->assertNotNull($this->website->deleted_at);

        $this->websites->delete($this->website, true);

        $this->assertFalse($this->website->exists);
    }

    /**
     * @test
     */
    public function setting_custom_uuid()
    {
        $this->website->uuid = 'foo';

        $website = $this->websites->create($this->website);

        $this->assertEquals('foo', $website->uuid);
    }

    protected function duringSetUp(Application $app)
    {
        $this->setUpWebsites();
        $this->setUpHostnames();
    }
}
