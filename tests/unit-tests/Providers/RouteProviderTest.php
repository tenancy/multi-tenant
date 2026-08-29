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

namespace Hyn\Tenancy\Tests\Providers;

use Hyn\Tenancy\Providers\Tenants\RouteProvider;
use Hyn\Tenancy\Tests\Test;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Symfony\Component\HttpFoundation\Request as FoundationRequest;

class RouteProviderTest extends Test
{
    protected function prepareSkeleton(string $path)
    {
        file_put_contents(
            "$path/routes/tenants.php",
            <<<EOM
<?php

\Route::get('/', function () { return 'bar'; })->name('tenant');

EOM
        );
    }

    /**
     * The skeleton the suite runs on ships no routes of its own, so the global
     * routes a real application would have are declared here: one the tenant
     * route file takes over, and one that has to survive it.
     *
     * Testbench registers these on a booted callback queued before the
     * providers boot, which puts them ahead of the tenant routes — the same
     * order a real application loads them in.
     *
     * @param Router $router
     */
    protected function defineRoutes($router)
    {
        $router->get('/', function () {
            return 'foo';
        })->name('global');

        $router->get('other', function () {
            return 'foo';
        })->name('other');
    }

    protected function duringSetUp(Application $app)
    {
        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);
        $this->activateTenant();
    }

    /**
     * @test
     */
    public function overrides_global_route()
    {
        $this->overrideGlobalRoute();

        $this->assertEquals(2, $this->app['router']->getRoutes()->count());
    }

    /**
     * @test
     */
    public function replaces_global_route()
    {
        config(['tenancy.routes.replace-global' => true]);

        // Refresh routes with above configuration now set.
        $this->app->call([new RouteProvider($this->app), 'boot']);

        $this->overrideGlobalRoute();

        $this->assertEquals(1, $this->app['router']->getRoutes()->count());
    }

    /**
     * @test
     */
    public function resolves_route_from_helper()
    {
        $url = route('tenant');

        $this->assertEquals(url('/'), $url);
    }

    /**
     * Create a fake request to send to the router matching logic.
     */
    protected function overrideGlobalRoute()
    {
        $request = Request::createFromBase(FoundationRequest::create("http://{$this->hostname->fqdn}"));
        $this->assertEquals($this->hostname->fqdn, $request->getHost());

        /** @var Route $route */
        $route = $this->app['router']->getRoutes()->match($request);

        $this->assertEquals('tenant', $route->getName());
    }

    protected function tearDown() : void
    {
        unlink(base_path('routes/tenants.php'));

        parent::tearDown();
    }
}
