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
 * @see https://hyn.me
 * @see https://patreon.com/tenancy
 */

namespace Hyn\Tenancy\Tests\Traits;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestResponse;

trait InteractsWithLaravelVersions
{
    public function assertJsonFragment($data = [], $response = null)
    {
        if (! $response && isset($this->response)) {
            $response = $this->response;
        }

        if ($response instanceof TestResponse) {
            return $response->assertJsonFragment($data);
        }

        if ($response instanceof self) {
            return $this->seeJson($data);
        }

        throw new \RuntimeException('Response object unknown: ' . get_class($response));
    }

    /**
     * @param $compareTo
     * @param Application|null $app
     * @return bool
     */
    protected function isAppVersion($compareTo, Application $app = null): bool
    {
        if (!$app && $this->app) {
            $app = $this->app;
        }

        return version_compare(substr($app->version(), 0, 3), $compareTo, 'eq');
    }
}
