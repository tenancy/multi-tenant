<?php

namespace Hyn\Tenancy\Tests\Traits;

trait InteractsWithLaravelVersions
{
    public function assertJsonFragment($response, $data = [])
    {
        if ($this->isAppVersion('5.3')) {
            return $response->seeJson($data);
        }

        return $response->assertJsonFragment($data);
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
