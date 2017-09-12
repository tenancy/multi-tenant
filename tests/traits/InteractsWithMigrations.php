<?php

namespace Hyn\Tenancy\Tests\Traits;

use Illuminate\Database\Eloquent\Collection;

trait InteractsWithMigrations
{
    protected function migrateAndTest(string $command, callable $callback = null, callable $hook = null)
    {
        $code = $this->artisan("tenancy:$command", [
            '--realpath' => __DIR__ . '/../migrations',
            '-n' => 1
        ]);

        $this->assertEquals(0, $code, "tenancy:$command didn't work out");

        if ($hook) {
            $hook();
        }

        if ($callback) {
            $this->websites->query()->chunk(10, function (Collection $websites) use ($callback) {
                $websites->each($callback);
            });
        }
    }
}