<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://laravel-tenancy.com
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Tests\Webserver\Vhost;

use Hyn\Tenancy\Generators\Webserver\Vhost\ApacheGenerator;
use Hyn\Tenancy\Listeners\Servant;
use Hyn\Tenancy\Tests\Test;
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

        $this->setUpHostnames();
        $this->setUpWebsites(true, true);

        $this->generator = $app->make(ApacheGenerator::class);
        $this->filesystem = app(Servant::class)->serviceFilesystem('apache2', config('webserver.apache2', []));
    }

    /**
     * @test
     */
    public function generates_vhost_configuration()
    {
        $path = $this->generator->targetPath($this->website);

        $this->assertTrue($this->filesystem->exists($path));
    }
}
