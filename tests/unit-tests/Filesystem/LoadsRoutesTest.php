<?php

namespace Hyn\Tenancy\Tests\Filesystem;

use Hyn\Tenancy\Tests\Test;
use Hyn\Tenancy\Website\Directory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Symfony\Component\HttpFoundation\Request as FoundationRequest;

class LoadsRoutesTest extends Test
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
    }

    /**
     * @test
     */
    public function reads_additional_routes()
    {
        // Write a testing config.
        $this->assertTrue($this->directory->put('routes.php', <<<EOM
<?php

\$router->get('foo', function () { return 'testing'; })->name('bar');
EOM
));

        $this->assertTrue($this->directory->exists('routes.php'));

        $this->activateTenant('local');

        /** @var Router $router */
        $router = $this->app->make('router');

        $request = Request::createFromBase(FoundationRequest::create('foo'));

        $route = $router->getRoutes()->match($request);

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('bar', $route->getName());
    }
}
