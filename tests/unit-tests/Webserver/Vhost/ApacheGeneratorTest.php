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

namespace Hyn\Tenancy\Tests\Webserver\Vhost;

use Hyn\Tenancy\Generators\Webserver\Vhost\ApacheGenerator;
use Hyn\Tenancy\Listeners\Servant;
use Hyn\Tenancy\Tests\Test;
use Hyn\Tenancy\Website\Directory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Foundation\Application;

class ApacheGeneratorTest extends Test
{
    /**
     * @var ApacheGenerator
     */
    protected $generator;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    protected function duringSetUp(Application $app)
    {
        // Marks all tests in this class as skipped.
        if ($this->buildWebserver != 'apache') {
            $this->markTestSkipped('Testing a different driver: ' . $this->buildWebserver);
        }

        $app['config']->set('webserver.apache2.enabled', true);

        $this->generator = $app->make(ApacheGenerator::class);
        $this->filesystem = $app->make(Servant::class)->serviceFilesystem('apache2', config('webserver.apache2', []));
    }

    /**
     * @test
     */
    public function generates_vhost_configuration()
    {
        $this->setUpHostnames();
        $this->setUpWebsites(true, true);

        $path = $this->generator->targetPath($this->website);

        $this->assertTrue($this->filesystem->exists($path));
    }

    /**
     * @test
     */
    public function generates_vhost_media_alias()
    {
        $this->setUpHostnames();
        $this->setUpWebsites(true);

        $directory = app(Directory::class)->setWebsite($this->website);
        $directory->makeDirectory('media');

        $this->hostnames->attach($this->hostname, $this->website);

        $path = $this->generator->targetPath($this->website);

        $config = $this->filesystem->get($path);

        $this->assertStringContainsString('alias', $config);
    }
}
