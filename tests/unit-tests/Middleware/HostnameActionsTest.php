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

namespace Hyn\Tenancy\Tests\Middleware;

use Exception;
use Hyn\Tenancy\Contracts\CurrentHostname;
use Hyn\Tenancy\Contracts\Hostname;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Middleware\HostnameActions;
use Hyn\Tenancy\Tests\Test;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HostnameActionsTest extends Test
{
    const RESPONSE = 'ok';

    /**
     * @test
     */
    public function under_maintenance()
    {
        $this->hostname->under_maintenance_since = Carbon::now();
        $this->hostname->save();

        try {
            $this->middleware($this->hostname);

            $this->fail('Middleware didn\'t fire maintenance exception');
        } catch (MaintenanceModeException $e) {
            $this->assertEquals($e->wentDownAt->timestamp, $this->hostname->under_maintenance_since->timestamp);
        }

        $this->hostname->under_maintenance_since = null;
        $this->hostname->save();

        // Rebind the updated model.
        $this->app->bind(CurrentHostname::class, function () {
            return $this->hostname;
        });

        $this->assertEquals(static::RESPONSE, $this->middleware($this->hostname));
    }

    /**
     * @test
     */
    public function middleware_allows_empty_hostname()
    {
        $middleware = new HostnameActions(app()->make(Redirector::class));

        $this->assertNotNull($middleware);
    }

    /**
     * @test
     */
    public function auto_identification_false()
    {
        config(['tenancy.hostname.auto-identification' => false]);
        config(['tenancy.hostname.abort-without-identified-hostname' => true]);

        try {
            $middleware = new HostnameActions(app()->make(Redirector::class));

            $request = new Request();

            $middleware->handle($request, function () {
                return static::RESPONSE;
            });
        } catch (Exception $e) {
            $this->assertInstanceOf(NotFoundHttpException::class, $e);
        }
    }

    protected function middleware(Hostname $set = null)
    {
        app(Environment::class)->hostname($set);

        $identified = $this->app->make(CurrentHostname::class);

        if ($set) {
            $this->assertNotNull($identified);
        } else {
            $this->assertNull($identified);
        }

        $request = new Request();
        $middleware = new HostnameActions(app()->make(Redirector::class));

        return $middleware->handle($request, function () {
            return static::RESPONSE;
        });
    }

    protected function duringSetUp(Application $app)
    {
        $this->setUpHostnames();
    }
}
