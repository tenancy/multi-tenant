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
use Hyn\Tenancy\Website\Directory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\View\Factory;

class LoadsViewsTest extends Test
{
    /**
     * @var Directory
     */
    protected $directory;

    /**
     * @var Factory
     */
    protected $views;

    protected function duringSetUp(Application $app)
    {
        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);

        $this->directory = $app->make(Directory::class);
        $this->directory->setWebsite($this->website);

        /** @var Factory views */
        $this->views = $app['view'];
    }

    /**
     * @test
     */
    public function loads_additional_views()
    {
        $this->assertFalse($this->views->exists('foo'));

        // Directory should now exists, let's write the config folder.
        $this->assertTrue($this->directory->makeDirectory('views'));

        $this->assertTrue($this->directory->put('views/foo.blade.php', 'bar'));

        $this->assertTrue($this->directory->exists('views/'));

        $this->activateTenant();

        $this->assertTrue($this->views->exists('foo'));

        $this->assertEquals('bar', $this->views->make('foo')->render());
    }

    /**
     * @test
     */
    public function loads_additional_views_overriding_global()
    {
        $this->assertTrue($this->views->exists('welcome'));

        // Directory should now exists, let's write the config folder.
        $this->assertTrue($this->directory->makeDirectory('views'));

        $this->assertTrue($this->directory->put('views/welcome.blade.php', 'bar'));

        $this->assertTrue($this->directory->exists('views/'));

        $this->activateTenant();

        $this->assertEquals('bar', $this->views->make('welcome')->render());
    }

    /**
     * @test
     */
    public function loads_additional_views_not_overriding_global()
    {
        $this->assertTrue($this->views->exists('welcome'));

        config(['tenancy.folders.views.override-global' => false]);

        // Directory should now exists, let's write the config folder.
        $this->assertTrue($this->directory->makeDirectory('views'));

        $this->assertTrue($this->directory->put('views/welcome.blade.php', 'bar'));

        $this->assertTrue($this->directory->exists('views/'));

        $this->activateTenant();

        $this->assertNotEquals('bar', $this->views->make('welcome')->render());
    }

    /**
     * @test
     */
    public function loads_additional_views_with_namespace()
    {
        $this->assertTrue($this->views->exists('welcome'));

        config(['tenancy.folders.views.namespace' => 'tenant']);

        // Directory should now exists, let's write the config folder.
        $this->assertTrue($this->directory->makeDirectory('views'));

        $this->assertTrue($this->directory->put('views/welcome.blade.php', 'bar'));

        $this->assertTrue($this->directory->exists('views/'));

        $this->activateTenant();

        $this->assertNotEquals('bar', $this->views->make('welcome')->render());
        $this->assertEquals('bar', $this->views->make('tenant::welcome')->render());
    }
}
