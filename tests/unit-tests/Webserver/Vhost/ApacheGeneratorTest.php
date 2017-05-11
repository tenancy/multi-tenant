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
 *
 */

namespace Hyn\Tenancy\Tests\Webserver\Vhost;

use Hyn\Tenancy\Generators\Webserver\Vhost\ApacheGenerator;
use Hyn\Tenancy\Tests\Test;
use Illuminate\Contracts\Foundation\Application;

class ApacheGeneratorTest extends Test
{
    /**
     * @var ApacheGenerator
     */
    protected $generator;

    protected function duringSetUp(Application $app)
    {
        $this->setUpWebsites();
        $this->setUpHostnames();
        $app['config']->set('webserver.apache2.enabled', true);

        $this->generator = $app->make(ApacheGenerator::class);
    }

    /**
     * @test
     */
    public function generates_vhost_configuration()
    {
        $this->websites->create($this->website);

        $path = $this->generator->targetPath($this->website);

        $this->assertFileExists($path);
    }
}
