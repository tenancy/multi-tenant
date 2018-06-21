<?php

namespace Hyn\Tenancy\Tests\Providers;

use Hyn\Tenancy\Providers\Tenants\RouteProvider;
use Hyn\Tenancy\Tests\Test;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Request as FoundationRequest;

class RouteProviderTest extends Test
{
    protected function pathIdentified(string $path)
    {
        file_put_contents("$path/routes/tenants.php", <<<EOM
<?php

\Route::get('/', function () { return 'bar'; })->name('tenant');

EOM
        );
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
        $this->app->call([new RouteProvider($this->app), 'map']);

        $this->overrideGlobalRoute();

        $this->assertEquals(1, $this->app['router']->getRoutes()->count());
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

    protected function tearDown()
    {
        unlink(base_path('routes/tenants.php'));

        parent::tearDown();
    }
}
