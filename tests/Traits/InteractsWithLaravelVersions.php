<?php

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
        if (!$app && $this->app) { $app = $this->app; }

        return version_compare(substr($app->version(), 0, 3), $compareTo, 'eq');
    }
}
