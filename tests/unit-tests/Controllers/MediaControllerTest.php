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

namespace Hyn\Tenancy\Tests\Controllers;

use Hyn\Tenancy\Controllers\MediaController;
use Hyn\Tenancy\Tests\Test;
use Hyn\Tenancy\Website\Directory;
use Illuminate\Contracts\Foundation\Application;

class MediaControllerTest extends Test
{
    /**
     * @var Directory
     */
    protected $directory;

    protected function duringSetUp(Application $app)
    {
        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);
        $this->directory = $app->make(Directory::class);

        $app['router']->get('/media/{path}', MediaController::class)->where('path', '.+');
    }

    /**
     * @test
     */
    public function request_file_via_controller()
    {
        $this->directory->setWebsite($this->website);
        $this->assertTrue($this->directory->put('media/test', 'foo'));

        $this->activateTenant();

        $response = $this->get('http://'. $this->hostname->fqdn . '/media/test');

        $response->assertSuccessful();

        $response->assertSeeText('foo');

        $this->directory->delete('media/test');
    }
}
