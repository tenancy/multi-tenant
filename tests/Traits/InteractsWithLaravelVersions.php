<?php

namespace Hyn\Tenancy\Tests\Traits;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Response;

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

        if ($response instanceof Response) {
            return $this->seeJson($data);
        }

        throw new \RuntimeException('Response object unknown');
    }

    /**
     * @param $compareTo
     * @param Application|null $app
     * @return bool
     */
    protected function isAppVersion($compareTo, Application $app = null): bool
    {
        if (!$app && $this->app) { $app = $this->app; }

        return version_compare(substr($app->version(), 0, 3), $compareTo, 'eq');
    }
}
