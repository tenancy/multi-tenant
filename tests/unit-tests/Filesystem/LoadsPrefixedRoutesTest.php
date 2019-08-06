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
use Hyn\Tenancy\Tests\Traits\InteractsWithRoutes;
use Illuminate\Contracts\Foundation\Application;

class LoadsPrefixedRoutesTest extends Test
{
    use InteractsWithRoutes;

    protected function duringSetUp(Application $app)
    {
        config(['tenancy.folders.routes.prefix' => 'v1']);

        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);
    }

    /**
     * @test
     */
    public function read_prefixed_additional_routes()
    {
        $this->create_and_test_route('foo', 'v1/foo');
    }
}
