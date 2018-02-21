<?php

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
        $this->directory->setWebsite($this->website);

        $app['router']->get('/media/{path}', MediaController::class)->where('path', '.+')->name('tenant.media');
    }

    /**
     * @test
     */
    public function request_file_via_controller()
    {
        $this->directory->put('media/test', 'foo');

        $this->activateTenant();

        $response = $this->get('/media/test');

        $response->assertSuccessful();

        $response->assertSeeText('foo');
    }
}
