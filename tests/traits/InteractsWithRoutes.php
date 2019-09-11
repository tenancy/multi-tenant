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

namespace Hyn\Tenancy\Tests\Traits;

use Hyn\Tenancy\Website\Directory;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Symfony\Component\HttpFoundation\Request as FoundationRequest;

trait InteractsWithRoutes
{
    protected function create_and_test_route(string $path, string $uri = null)
    {
        /** @var Directory $directory */
        $directory = $this->app->make(Directory::class);
        $directory->setWebsite($this->website);

        if (!$uri) {
            $uri = $path;
        }

        // Write a testing config.
        $this->assertTrue($directory->put('routes.php', <<<EOM
<?php

\Route::get('$path', function () { return 'testing'; })->name('bar');
EOM
        ));

        $this->assertTrue($directory->exists('routes.php'));

        $this->activateTenant();

        /** @var Router $router */
        $router = $this->app->make('router');

        $request = Request::createFromBase(FoundationRequest::create($uri));

        $route = $router->getRoutes()->match($request);

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('bar', $route->getName());
    }
}
