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

namespace Hyn\Tenancy\Tests;

use Carbon\Carbon;
use Hyn\Tenancy\Contracts\Hostname;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Events\Hostnames\Identified;
use Hyn\Tenancy\Events\Hostnames\Switched;
use Hyn\Tenancy\Middleware\HostnameActions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class EnvironmentTest extends Test
{
    /**
     * @var Environment
     */
    protected $environment;

    /**
     * @test
     */
    public function sets_hostname()
    {
        $this->expectsEvents(Switched::class);

        $this->environment->hostname($this->hostname);

        $identified = $this->app->make(Hostname::class);

        $this->assertEquals($this->hostname->fqdn, $identified->fqdn);
    }

    /**
     * @test
     */
    public function identifies_hostname()
    {
        $this->expectsEvents(Identified::class);

        $identified = $this->app->make(Hostname::class);

        $this->assertNull($identified);

        $this->hostname->save();
        
        config(['tenancy.hostname.default' => $this->hostname->fqdn]);

        $identified = $this->app->make(Hostname::class);

        $this->assertEquals($this->hostname->fqdn, $identified->fqdn);

        $this->assertEquals($identified->fqdn, $this->environment->hostname()->fqdn);
    }

    /**
     * @test
     */
    public function we_can_set_current_hostname_to_null_on_hostname_action_middleware()
    {
        $middleware = new HostnameActions(null, app()->make(Redirector::class));
        
        $this->assertNotNull($middleware);
    }


    /**
     * @test
     */
    public function middleware_fired_under_maintenance()
    {
        $this->hostname->save();
        
        config(['tenancy.hostname.default' => $this->hostname->fqdn]);
        
        $identified = $this->app->make(Hostname::class);
        
        $this->assertNotNull($identified);

        $now = Carbon::now();
        $identified->under_maintenance_since = $now;
        $identified->save();

        $request = new Request();
        $middleware = new HostnameActions($identified, app()->make(Redirector::class));

        try {
            $a = $middleware->handle($request, function () {
                return "ok";
            });
            
            $this->fail('Middleware didn\'t fire maintenance exception');
        } catch (MaintenanceModeException $e) {
            $this->assertEquals($e->wentDownAt->timestamp, $now->timestamp);
        }

        $identified->under_maintenance_since = null;
        $identified->save();

        $res = $middleware->handle($request, function () {
            return "ok";
        });

        $this->assertEquals("ok", $res);
    }


    protected function duringSetUp(Application $app)
    {
        $this->setUpHostnames();
        $this->environment = $app->make(Environment::class);
    }
}
